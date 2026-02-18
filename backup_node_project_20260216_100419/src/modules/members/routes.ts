import type { FastifyPluginAsync } from "fastify";
import {
  GroupStatus,
  GroupVisibility,
  MembershipStatus,
  Prisma,
  RequestStatus
} from "@prisma/client";
import { z } from "zod";
import { prisma } from "../../lib/prisma.js";
import { generateUniqueUsername, normalizeUsername } from "../../lib/profile.js";
import { findAdminMembership, findApprovedMembership } from "../groups/helpers.js";

const slugRegex = /^[a-z0-9-]{3,50}$/;
const usernameRegex = /^[a-z0-9-]{3,40}$/;

const paramsSlugSchema = z.object({ slug: z.string().regex(slugRegex) });
const joinSchema = z.object({ note: z.string().max(500).optional() });
const paramsRequestSchema = z.object({
  slug: z.string().regex(slugRegex),
  requestId: z.string().uuid()
});
const memberParamsSchema = z.object({
  slug: z.string().regex(slugRegex),
  username: z.string().regex(usernameRegex)
});

const directoryQuerySchema = z.object({
  search: z
    .string()
    .optional()
    .transform((value) => value?.trim() || undefined),
  city: z
    .string()
    .optional()
    .transform((value) => value?.trim() || undefined),
  country: z
    .string()
    .optional()
    .transform((value) => value?.trim() || undefined),
  featured: z
    .enum(["true", "false"])
    .optional()
    .transform((value) => (value === undefined ? undefined : value === "true")),
  page: z.coerce.number().int().min(1).default(1),
  pageSize: z.coerce.number().int().min(1).max(100).default(20)
});

const updateProfileSchema = z
  .object({
    username: z.string().regex(usernameRegex).optional(),
    photoUrl: z.string().url().optional(),
    fullName: z.string().min(1).max(120).optional(),
    shortBio: z.string().max(500).optional(),
    currentRole: z.string().max(120).optional(),
    business: z.string().max(120).optional(),
    city: z.string().max(80).optional(),
    country: z.string().max(80).optional(),
    linkedinUrl: z.string().url().optional(),
    instagramUrl: z.string().url().optional(),
    facebookUrl: z.string().url().optional(),
    websiteUrl: z.string().url().optional(),
    isFeatured: z.boolean().optional()
  })
  .refine((payload) => Object.keys(payload).length > 0, {
    message: "At least one field is required"
  });

async function resolveOptionalRequester(request: {
  headers: { authorization?: string };
  jwtVerify: () => Promise<void>;
  user?: { userId: string; isPlatformOwner: boolean };
}): Promise<{ userId?: string; isPlatformOwner: boolean }> {
  if (!request.headers.authorization) {
    return { userId: undefined, isPlatformOwner: false };
  }

  try {
    await request.jwtVerify();
    return {
      userId: request.user?.userId,
      isPlatformOwner: Boolean(request.user?.isPlatformOwner)
    };
  } catch {
    return { userId: undefined, isPlatformOwner: false };
  }
}

async function validateGroupAccess(
  group: { id: string; visibility: GroupVisibility; status: GroupStatus },
  userId: string | undefined,
  isPlatformOwner: boolean
): Promise<{ ok: true } | { ok: false; status: number; message: string }> {
  if (group.status === GroupStatus.SUSPENDED && !isPlatformOwner) {
    return { ok: false, status: 423, message: "Group is suspended" };
  }

  if (group.visibility === GroupVisibility.PRIVATE && !isPlatformOwner) {
    if (!userId) {
      return { ok: false, status: 403, message: "Private group access requires membership" };
    }

    const membership = await findApprovedMembership(group.id, userId);
    if (!membership) {
      return { ok: false, status: 403, message: "Private group access requires membership" };
    }
  }

  return { ok: true };
}

export const membersRoutes: FastifyPluginAsync = async (fastify) => {
  fastify.post("/:slug/join", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    const params = paramsSlugSchema.safeParse(request.params);
    if (!params.success) {
      return reply.code(400).send({ message: "Invalid group slug" });
    }

    const body = joinSchema.safeParse(request.body ?? {});
    if (!body.success) {
      return reply.code(400).send({ message: "Invalid request body", errors: body.error.flatten() });
    }

    const group = await prisma.group.findUnique({ where: { slug: params.data.slug } });
    if (!group) {
      return reply.code(404).send({ message: "Group not found" });
    }

    if (group.status !== GroupStatus.ACTIVE) {
      return reply.code(409).send({ message: "Group is not accepting new members" });
    }

    const existing = await prisma.groupMembership.findUnique({
      where: {
        groupId_userId: {
          groupId: group.id,
          userId: request.user.userId
        }
      }
    });

    if (existing?.status === MembershipStatus.APPROVED) {
      return reply.send({ message: "You are already an approved member" });
    }

    await prisma.$transaction(async (tx) => {
      if (existing) {
        await tx.groupMembership.update({
          where: { id: existing.id },
          data: {
            status: MembershipStatus.PENDING,
            approvedAt: null,
            approvedByUserId: null
          }
        });
      } else {
        await tx.groupMembership.create({
          data: {
            groupId: group.id,
            userId: request.user.userId,
            status: MembershipStatus.PENDING
          }
        });
      }

      await tx.membershipRequest.upsert({
        where: {
          groupId_userId: {
            groupId: group.id,
            userId: request.user.userId
          }
        },
        update: {
          status: RequestStatus.PENDING,
          note: body.data.note,
          reviewedByUserId: null,
          reviewedAt: null
        },
        create: {
          groupId: group.id,
          userId: request.user.userId,
          note: body.data.note,
          status: RequestStatus.PENDING
        }
      });

      await tx.auditLog.create({
        data: {
          targetGroupId: group.id,
          actorUserId: request.user.userId,
          eventType: "MEMBERSHIP_REQUEST_SUBMITTED"
        }
      });
    });

    return reply.code(202).send({
      message: "Membership request submitted and awaiting admin approval"
    });
  });

  fastify.get(
    "/:slug/membership-requests",
    { preHandler: [fastify.authenticate] },
    async (request, reply) => {
      const params = paramsSlugSchema.safeParse(request.params);
      if (!params.success) {
        return reply.code(400).send({ message: "Invalid group slug" });
      }

      const group = await prisma.group.findUnique({ where: { slug: params.data.slug } });
      if (!group) {
        return reply.code(404).send({ message: "Group not found" });
      }

      if (!request.user.isPlatformOwner) {
        const membership = await findAdminMembership(group.id, request.user.userId);
        if (!membership) {
          return reply.code(403).send({ message: "Only group admins can view requests" });
        }
      }

      const requests = await prisma.membershipRequest.findMany({
        where: {
          groupId: group.id,
          status: RequestStatus.PENDING
        },
        include: {
          user: {
            select: {
              id: true,
              email: true,
              fullName: true,
              createdAt: true
            }
          }
        },
        orderBy: {
          createdAt: "asc"
        }
      });

      return reply.send({ requests });
    }
  );

  fastify.post(
    "/:slug/membership-requests/:requestId/approve",
    { preHandler: [fastify.authenticate] },
    async (request, reply) => {
      const params = paramsRequestSchema.safeParse(request.params);
      if (!params.success) {
        return reply.code(400).send({ message: "Invalid path parameters" });
      }

      const group = await prisma.group.findUnique({ where: { slug: params.data.slug } });
      if (!group) {
        return reply.code(404).send({ message: "Group not found" });
      }

      if (!request.user.isPlatformOwner) {
        const membership = await findAdminMembership(group.id, request.user.userId);
        if (!membership) {
          return reply.code(403).send({ message: "Only group admins can approve members" });
        }
      }

      const requestRow = await prisma.membershipRequest.findUnique({
        where: { id: params.data.requestId },
        include: { user: true }
      });

      if (!requestRow || requestRow.groupId !== group.id) {
        return reply.code(404).send({ message: "Membership request not found" });
      }

      if (requestRow.status !== RequestStatus.PENDING) {
        return reply.code(409).send({ message: "Membership request is already processed" });
      }

      const now = new Date();
      const result = await prisma.$transaction(async (tx) => {
        await tx.membershipRequest.update({
          where: { id: requestRow.id },
          data: {
            status: RequestStatus.APPROVED,
            reviewedByUserId: request.user.userId,
            reviewedAt: now
          }
        });

        const membership = await tx.groupMembership.upsert({
          where: {
            groupId_userId: {
              groupId: group.id,
              userId: requestRow.userId
            }
          },
          update: {
            status: MembershipStatus.APPROVED,
            approvedByUserId: request.user.userId,
            approvedAt: now
          },
          create: {
            groupId: group.id,
            userId: requestRow.userId,
            status: MembershipStatus.APPROVED,
            approvedByUserId: request.user.userId,
            approvedAt: now
          }
        });

        const existingProfile = await tx.memberProfile.findUnique({
          where: {
            groupId_userId: {
              groupId: group.id,
              userId: requestRow.userId
            }
          }
        });

        if (!existingProfile) {
          const username = await generateUniqueUsername(
            tx,
            group.id,
            requestRow.user.fullName ?? requestRow.user.email.split("@")[0]
          );

          await tx.memberProfile.create({
            data: {
              groupId: group.id,
              userId: requestRow.userId,
              username,
              fullName: requestRow.user.fullName ?? requestRow.user.email
            }
          });
        }

        await tx.auditLog.create({
          data: {
            targetGroupId: group.id,
            actorUserId: request.user.userId,
            eventType: "MEMBERSHIP_REQUEST_APPROVED",
            metadata: {
              requestId: requestRow.id,
              approvedUserId: requestRow.userId
            }
          }
        });

        return membership;
      });

      return reply.send({
        message: "Membership approved",
        membership: result
      });
    }
  );

  fastify.post(
    "/:slug/membership-requests/:requestId/reject",
    { preHandler: [fastify.authenticate] },
    async (request, reply) => {
      const params = paramsRequestSchema.safeParse(request.params);
      if (!params.success) {
        return reply.code(400).send({ message: "Invalid path parameters" });
      }

      const group = await prisma.group.findUnique({ where: { slug: params.data.slug } });
      if (!group) {
        return reply.code(404).send({ message: "Group not found" });
      }

      if (!request.user.isPlatformOwner) {
        const membership = await findAdminMembership(group.id, request.user.userId);
        if (!membership) {
          return reply.code(403).send({ message: "Only group admins can reject members" });
        }
      }

      const requestRow = await prisma.membershipRequest.findUnique({
        where: { id: params.data.requestId }
      });

      if (!requestRow || requestRow.groupId !== group.id) {
        return reply.code(404).send({ message: "Membership request not found" });
      }

      if (requestRow.status !== RequestStatus.PENDING) {
        return reply.code(409).send({ message: "Membership request is already processed" });
      }

      await prisma.$transaction(async (tx) => {
        await tx.membershipRequest.update({
          where: { id: requestRow.id },
          data: {
            status: RequestStatus.REJECTED,
            reviewedByUserId: request.user.userId,
            reviewedAt: new Date()
          }
        });

        await tx.groupMembership.upsert({
          where: {
            groupId_userId: {
              groupId: group.id,
              userId: requestRow.userId
            }
          },
          update: {
            status: MembershipStatus.REJECTED,
            approvedAt: null,
            approvedByUserId: null
          },
          create: {
            groupId: group.id,
            userId: requestRow.userId,
            status: MembershipStatus.REJECTED
          }
        });

        await tx.auditLog.create({
          data: {
            targetGroupId: group.id,
            actorUserId: request.user.userId,
            eventType: "MEMBERSHIP_REQUEST_REJECTED",
            metadata: {
              requestId: requestRow.id,
              rejectedUserId: requestRow.userId
            }
          }
        });
      });

      return reply.send({ message: "Membership rejected" });
    }
  );

  fastify.get("/:slug/members", async (request, reply) => {
    const params = paramsSlugSchema.safeParse(request.params);
    if (!params.success) {
      return reply.code(400).send({ message: "Invalid group slug" });
    }

    const query = directoryQuerySchema.safeParse(request.query ?? {});
    if (!query.success) {
      return reply.code(400).send({ message: "Invalid query params", errors: query.error.flatten() });
    }

    const group = await prisma.group.findUnique({ where: { slug: params.data.slug } });
    if (!group) {
      return reply.code(404).send({ message: "Group not found" });
    }

    const requester = await resolveOptionalRequester(request);
    const access = await validateGroupAccess(group, requester.userId, requester.isPlatformOwner);
    if (!access.ok) {
      return reply.code(access.status).send({ message: access.message });
    }

    const where: Prisma.MemberProfileWhereInput = {
      groupId: group.id,
      user: {
        memberships: {
          some: {
            groupId: group.id,
            status: MembershipStatus.APPROVED
          }
        }
      }
    };

    if (query.data.featured !== undefined) {
      where.isFeatured = query.data.featured;
    }

    if (query.data.city) {
      where.city = { contains: query.data.city, mode: "insensitive" };
    }

    if (query.data.country) {
      where.country = { contains: query.data.country, mode: "insensitive" };
    }

    if (query.data.search) {
      where.OR = [
        { fullName: { contains: query.data.search, mode: "insensitive" } },
        { currentRole: { contains: query.data.search, mode: "insensitive" } },
        { business: { contains: query.data.search, mode: "insensitive" } },
        { city: { contains: query.data.search, mode: "insensitive" } },
        { country: { contains: query.data.search, mode: "insensitive" } }
      ];
    }

    const skip = (query.data.page - 1) * query.data.pageSize;

    const [members, total] = await Promise.all([
      prisma.memberProfile.findMany({
        where,
        orderBy: [
          { isFeatured: "desc" },
          { fullName: "asc" }
        ],
        skip,
        take: query.data.pageSize,
        select: {
          username: true,
          photoUrl: true,
          fullName: true,
          shortBio: true,
          currentRole: true,
          business: true,
          city: true,
          country: true,
          linkedinUrl: true,
          instagramUrl: true,
          facebookUrl: true,
          websiteUrl: true,
          isFeatured: true
        }
      }),
      prisma.memberProfile.count({ where })
    ]);

    return reply.send({
      pagination: {
        page: query.data.page,
        pageSize: query.data.pageSize,
        total,
        totalPages: Math.ceil(total / query.data.pageSize)
      },
      members
    });
  });

  fastify.get("/:slug/member/:username", async (request, reply) => {
    const params = memberParamsSchema.safeParse(request.params);
    if (!params.success) {
      return reply.code(400).send({ message: "Invalid path parameters" });
    }

    const group = await prisma.group.findUnique({ where: { slug: params.data.slug } });
    if (!group) {
      return reply.code(404).send({ message: "Group not found" });
    }

    const requester = await resolveOptionalRequester(request);
    const access = await validateGroupAccess(group, requester.userId, requester.isPlatformOwner);
    if (!access.ok) {
      return reply.code(access.status).send({ message: access.message });
    }

    const profile = await prisma.memberProfile.findFirst({
      where: {
        groupId: group.id,
        username: params.data.username,
        user: {
          memberships: {
            some: {
              groupId: group.id,
              status: MembershipStatus.APPROVED
            }
          }
        }
      },
      select: {
        username: true,
        photoUrl: true,
        fullName: true,
        shortBio: true,
        currentRole: true,
        business: true,
        city: true,
        country: true,
        linkedinUrl: true,
        instagramUrl: true,
        facebookUrl: true,
        websiteUrl: true,
        isFeatured: true,
        updatedAt: true
      }
    });

    if (!profile) {
      return reply.code(404).send({ message: "Member profile not found" });
    }

    return reply.send({ profile });
  });

  fastify.patch(
    "/:slug/member/:username",
    { preHandler: [fastify.authenticate] },
    async (request, reply) => {
      const params = memberParamsSchema.safeParse(request.params);
      if (!params.success) {
        return reply.code(400).send({ message: "Invalid path parameters" });
      }

      const body = updateProfileSchema.safeParse(request.body);
      if (!body.success) {
        return reply.code(400).send({ message: "Invalid request body", errors: body.error.flatten() });
      }

      const group = await prisma.group.findUnique({ where: { slug: params.data.slug } });
      if (!group) {
        return reply.code(404).send({ message: "Group not found" });
      }

      const target = await prisma.memberProfile.findUnique({
        where: {
          groupId_username: {
            groupId: group.id,
            username: params.data.username
          }
        }
      });

      if (!target) {
        return reply.code(404).send({ message: "Member profile not found" });
      }

      const adminMembership = request.user.isPlatformOwner
        ? true
        : Boolean(await findAdminMembership(group.id, request.user.userId));

      const isSelf = target.userId === request.user.userId;

      if (!adminMembership && !isSelf) {
        return reply.code(403).send({ message: "You can only edit your own profile" });
      }

      if (!adminMembership && body.data.isFeatured !== undefined) {
        return reply.code(403).send({ message: "Only group admins can feature profiles" });
      }

      const updateData: Prisma.MemberProfileUpdateInput = {
        ...body.data
      };

      if (body.data.username) {
        updateData.username = normalizeUsername(body.data.username);
      }

      try {
        const updated = await prisma.memberProfile.update({
          where: { id: target.id },
          data: updateData
        });

        await prisma.auditLog.create({
          data: {
            targetGroupId: group.id,
            actorUserId: request.user.userId,
            eventType: "MEMBER_PROFILE_UPDATED",
            metadata: {
              profileUserId: target.userId,
              fields: Object.keys(body.data)
            }
          }
        });

        return reply.send({
          message: "Profile updated",
          profile: updated
        });
      } catch (error) {
        if (error instanceof Prisma.PrismaClientKnownRequestError && error.code === "P2002") {
          return reply.code(409).send({ message: "Username is already in use in this group" });
        }

        throw error;
      }
    }
  );
};