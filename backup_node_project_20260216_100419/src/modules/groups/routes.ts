import type { FastifyPluginAsync } from "fastify";
import {
  GroupRole,
  GroupStatus,
  GroupVisibility,
  MembershipStatus,
  SubscriptionStatus
} from "@prisma/client";
import { z } from "zod";
import { prisma } from "../../lib/prisma.js";
import { generateUniqueUsername } from "../../lib/profile.js";
import { isSubscriptionActiveWindow } from "../../lib/rules.js";
import { findAdminMembership, findApprovedMembership } from "./helpers.js";

const slugRegex = /^[a-z0-9-]{3,50}$/;

const createGroupSchema = z.object({
  slug: z.string().regex(slugRegex),
  name: z.string().min(2).max(120),
  description: z.string().max(2000).optional(),
  visibility: z.nativeEnum(GroupVisibility).default(GroupVisibility.PUBLIC),
  logoUrl: z.string().url().optional(),
  coverImageUrl: z.string().url().optional()
});

const updateGroupSettingsSchema = z
  .object({
    name: z.string().min(2).max(120).optional(),
    description: z.string().max(2000).optional(),
    visibility: z.nativeEnum(GroupVisibility).optional(),
    logoUrl: z.string().url().optional(),
    coverImageUrl: z.string().url().optional()
  })
  .refine((payload) => Object.keys(payload).length > 0, {
    message: "At least one field is required"
  });

export const groupsRoutes: FastifyPluginAsync = async (fastify) => {
  fastify.post("/", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    const parsed = createGroupSchema.safeParse(request.body);
    if (!parsed.success) {
      return reply.code(400).send({ message: "Invalid request body", errors: parsed.error.flatten() });
    }

    const data = parsed.data;

    const existingGroup = await prisma.group.findUnique({ where: { slug: data.slug } });
    if (existingGroup) {
      return reply.code(409).send({ message: "Group slug is already taken" });
    }

    const now = new Date();
    const availableSubscription = await prisma.subscription.findFirst({
      where: {
        ownerUserId: request.user.userId,
        groupId: null,
        status: {
          in: [SubscriptionStatus.ACTIVE, SubscriptionStatus.TRIALING]
        }
      },
      orderBy: {
        createdAt: "asc"
      }
    });

    if (
      !availableSubscription ||
      !isSubscriptionActiveWindow(
        availableSubscription.status,
        availableSubscription.currentPeriodEnd,
        availableSubscription.gracePeriodEndsAt,
        now
      )
    ) {
      return reply.code(402).send({
        message: "An active subscription is required before creating a group"
      });
    }

    const created = await prisma.$transaction(async (tx) => {
      const owner = await tx.user.findUnique({ where: { id: request.user.userId } });
      if (!owner) {
        throw new Error("Authenticated user no longer exists");
      }

      const group = await tx.group.create({
        data: {
          slug: data.slug,
          name: data.name,
          description: data.description,
          visibility: data.visibility,
          logoUrl: data.logoUrl,
          coverImageUrl: data.coverImageUrl,
          status: GroupStatus.ACTIVE,
          ownerId: owner.id
        }
      });

      await tx.subscription.update({
        where: { id: availableSubscription.id },
        data: { groupId: group.id }
      });

      await tx.groupMembership.create({
        data: {
          groupId: group.id,
          userId: owner.id,
          role: GroupRole.OWNER,
          status: MembershipStatus.APPROVED,
          approvedByUserId: owner.id,
          approvedAt: now
        }
      });

      const username = await generateUniqueUsername(
        tx,
        group.id,
        owner.fullName ?? owner.email.split("@")[0]
      );

      await tx.memberProfile.create({
        data: {
          groupId: group.id,
          userId: owner.id,
          username,
          fullName: owner.fullName ?? owner.email
        }
      });

      await tx.auditLog.create({
        data: {
          targetGroupId: group.id,
          actorUserId: owner.id,
          eventType: "GROUP_CREATED",
          metadata: {
            slug: group.slug,
            name: group.name
          }
        }
      });

      return group;
    });

    return reply.code(201).send({
      message: "Group created successfully",
      group: created
    });
  });

  fastify.get("/:slug", async (request, reply) => {
    const paramsSchema = z.object({ slug: z.string().regex(slugRegex) });
    const parsedParams = paramsSchema.safeParse(request.params);
    if (!parsedParams.success) {
      return reply.code(400).send({ message: "Invalid group slug" });
    }

    const { slug } = parsedParams.data;

    const group = await prisma.group.findUnique({
      where: { slug },
      include: {
        _count: {
          select: {
            memberships: {
              where: { status: MembershipStatus.APPROVED }
            }
          }
        }
      }
    });

    if (!group) {
      return reply.code(404).send({ message: "Group not found" });
    }

    let requesterId: string | undefined;
    let requesterIsPlatformOwner = false;

    if (request.headers.authorization) {
      try {
        await request.jwtVerify();
        requesterId = request.user.userId;
        requesterIsPlatformOwner = request.user.isPlatformOwner;
      } catch {
        requesterId = undefined;
      }
    }

    if (group.status === GroupStatus.SUSPENDED && !requesterIsPlatformOwner) {
      return reply.code(423).send({ message: "Group is suspended" });
    }

    if (group.visibility === GroupVisibility.PRIVATE) {
      if (!requesterId) {
        return reply.code(403).send({ message: "Private group access requires membership" });
      }
      const membership = await findApprovedMembership(group.id, requesterId);
      if (!membership && !requesterIsPlatformOwner) {
        return reply.code(403).send({ message: "Private group access requires membership" });
      }
    }

    return reply.send({
      group: {
        id: group.id,
        slug: group.slug,
        name: group.name,
        description: group.description,
        logoUrl: group.logoUrl,
        coverImageUrl: group.coverImageUrl,
        visibility: group.visibility,
        status: group.status,
        memberCount: group._count.memberships
      }
    });
  });

  fastify.patch("/:slug/settings", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    const paramsSchema = z.object({ slug: z.string().regex(slugRegex) });
    const parsedParams = paramsSchema.safeParse(request.params);
    if (!parsedParams.success) {
      return reply.code(400).send({ message: "Invalid group slug" });
    }

    const parsedBody = updateGroupSettingsSchema.safeParse(request.body);
    if (!parsedBody.success) {
      return reply.code(400).send({ message: "Invalid request body", errors: parsedBody.error.flatten() });
    }

    const group = await prisma.group.findUnique({ where: { slug: parsedParams.data.slug } });
    if (!group) {
      return reply.code(404).send({ message: "Group not found" });
    }

    if (!request.user.isPlatformOwner) {
      const adminMembership = await findAdminMembership(group.id, request.user.userId);
      if (!adminMembership) {
        return reply.code(403).send({ message: "Only group admins can update settings" });
      }
    }

    const updated = await prisma.group.update({
      where: { id: group.id },
      data: parsedBody.data
    });

    await prisma.auditLog.create({
      data: {
        targetGroupId: group.id,
        actorUserId: request.user.userId,
        eventType: "GROUP_SETTINGS_UPDATED",
        metadata: {
          fields: Object.keys(parsedBody.data)
        }
      }
    });

    return reply.send({
      message: "Group settings updated",
      group: updated
    });
  });
};