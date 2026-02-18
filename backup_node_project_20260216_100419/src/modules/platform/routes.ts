import type { FastifyPluginAsync } from "fastify";
import { GroupStatus, MembershipStatus } from "@prisma/client";
import { z } from "zod";
import { prisma } from "../../lib/prisma.js";

const slugParamsSchema = z.object({ slug: z.string().regex(/^[a-z0-9-]{3,50}$/) });
const reasonSchema = z.object({ reason: z.string().max(500).optional() });

export const platformRoutes: FastifyPluginAsync = async (fastify) => {
  fastify.get("/groups", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    if (!request.user.isPlatformOwner) {
      return reply.code(403).send({ message: "Platform owner access required" });
    }

    const groups = await prisma.group.findMany({
      orderBy: {
        createdAt: "desc"
      },
      include: {
        owner: {
          select: {
            id: true,
            email: true,
            fullName: true
          }
        },
        subscription: {
          include: {
            plan: true
          }
        },
        _count: {
          select: {
            memberships: {
              where: {
                status: MembershipStatus.APPROVED
              }
            }
          }
        }
      }
    });

    return reply.send({
      groups: groups.map((group) => ({
        id: group.id,
        slug: group.slug,
        name: group.name,
        status: group.status,
        visibility: group.visibility,
        owner: group.owner,
        approvedMemberCount: group._count.memberships,
        subscription: group.subscription
          ? {
              id: group.subscription.id,
              status: group.subscription.status,
              currentPeriodEnd: group.subscription.currentPeriodEnd,
              gracePeriodEndsAt: group.subscription.gracePeriodEndsAt,
              plan: {
                code: group.subscription.plan.code,
                name: group.subscription.plan.name,
                priceCents: group.subscription.plan.priceCents,
                currency: group.subscription.plan.currency
              }
            }
          : null
      }))
    });
  });

  fastify.post("/groups/:slug/suspend", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    if (!request.user.isPlatformOwner) {
      return reply.code(403).send({ message: "Platform owner access required" });
    }

    const params = slugParamsSchema.safeParse(request.params);
    if (!params.success) {
      return reply.code(400).send({ message: "Invalid group slug" });
    }

    const body = reasonSchema.safeParse(request.body ?? {});
    if (!body.success) {
      return reply.code(400).send({ message: "Invalid request body", errors: body.error.flatten() });
    }

    const group = await prisma.group.findUnique({ where: { slug: params.data.slug } });
    if (!group) {
      return reply.code(404).send({ message: "Group not found" });
    }

    const updated = await prisma.group.update({
      where: { id: group.id },
      data: { status: GroupStatus.SUSPENDED }
    });

    await prisma.auditLog.create({
      data: {
        targetGroupId: group.id,
        actorUserId: request.user.userId,
        eventType: "GROUP_SUSPENDED",
        metadata: {
          reason: body.data.reason ?? null
        }
      }
    });

    return reply.send({
      message: "Group suspended",
      group: updated
    });
  });

  fastify.post("/groups/:slug/activate", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    if (!request.user.isPlatformOwner) {
      return reply.code(403).send({ message: "Platform owner access required" });
    }

    const params = slugParamsSchema.safeParse(request.params);
    if (!params.success) {
      return reply.code(400).send({ message: "Invalid group slug" });
    }

    const group = await prisma.group.findUnique({ where: { slug: params.data.slug } });
    if (!group) {
      return reply.code(404).send({ message: "Group not found" });
    }

    const updated = await prisma.group.update({
      where: { id: group.id },
      data: { status: GroupStatus.ACTIVE }
    });

    await prisma.auditLog.create({
      data: {
        targetGroupId: group.id,
        actorUserId: request.user.userId,
        eventType: "GROUP_ACTIVATED"
      }
    });

    return reply.send({
      message: "Group activated",
      group: updated
    });
  });
};