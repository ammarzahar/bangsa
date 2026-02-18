import type { FastifyPluginAsync } from "fastify";
import { MembershipStatus, RequestStatus, SubscriptionStatus } from "@prisma/client";
import { z } from "zod";
import { prisma } from "../../lib/prisma.js";
import { findAdminMembership } from "../groups/helpers.js";

const slugParamsSchema = z.object({ slug: z.string().regex(/^[a-z0-9-]{3,50}$/) });

export const dashboardRoutes: FastifyPluginAsync = async (fastify) => {
  fastify.get("/platform/overview", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    if (!request.user.isPlatformOwner) {
      return reply.code(403).send({ message: "Platform owner access required" });
    }

    const now = new Date();

    const [groupCount, memberCount, activeSubscriptions, recentGroups] = await Promise.all([
      prisma.group.count(),
      prisma.groupMembership.count({
        where: {
          status: MembershipStatus.APPROVED
        }
      }),
      prisma.subscription.findMany({
        where: {
          status: {
            in: [SubscriptionStatus.ACTIVE, SubscriptionStatus.TRIALING]
          },
          OR: [
            { currentPeriodEnd: null },
            { currentPeriodEnd: { gte: now } },
            { gracePeriodEndsAt: { gte: now } }
          ]
        },
        include: {
          plan: true,
          group: {
            select: {
              id: true,
              slug: true,
              name: true
            }
          }
        }
      }),
      prisma.group.findMany({
        take: 10,
        orderBy: { createdAt: "desc" },
        include: {
          owner: {
            select: {
              id: true,
              email: true,
              fullName: true
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
      })
    ]);

    const mrrCents = activeSubscriptions.reduce((sum, sub) => {
      if (!sub.groupId) {
        return sum;
      }
      return sum + sub.plan.priceCents;
    }, 0);

    return reply.send({
      overview: {
        totalGroups: groupCount,
        totalMembers: memberCount,
        activeSubscriptions: activeSubscriptions.length,
        mrrCents,
        currency: "USD"
      },
      recentGroups: recentGroups.map((group) => ({
        id: group.id,
        slug: group.slug,
        name: group.name,
        owner: group.owner,
        approvedMemberCount: group._count.memberships,
        createdAt: group.createdAt
      }))
    });
  });

  fastify.get("/group/:slug/overview", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    const parsed = slugParamsSchema.safeParse(request.params);
    if (!parsed.success) {
      return reply.code(400).send({ message: "Invalid group slug" });
    }

    const group = await prisma.group.findUnique({
      where: {
        slug: parsed.data.slug
      },
      include: {
        subscription: {
          include: {
            plan: true
          }
        }
      }
    });

    if (!group) {
      return reply.code(404).send({ message: "Group not found" });
    }

    if (!request.user.isPlatformOwner) {
      const membership = await findAdminMembership(group.id, request.user.userId);
      if (!membership) {
        return reply.code(403).send({ message: "Only group admins can view this dashboard" });
      }
    }

    const [approvedMembers, pendingMembers, pendingRequests, featuredProfiles] = await Promise.all([
      prisma.groupMembership.count({
        where: {
          groupId: group.id,
          status: MembershipStatus.APPROVED
        }
      }),
      prisma.groupMembership.count({
        where: {
          groupId: group.id,
          status: MembershipStatus.PENDING
        }
      }),
      prisma.membershipRequest.count({
        where: {
          groupId: group.id,
          status: RequestStatus.PENDING
        }
      }),
      prisma.memberProfile.count({
        where: {
          groupId: group.id,
          isFeatured: true
        }
      })
    ]);

    return reply.send({
      group: {
        id: group.id,
        slug: group.slug,
        name: group.name,
        status: group.status,
        visibility: group.visibility
      },
      analytics: {
        approvedMembers,
        pendingMembers,
        pendingRequests,
        featuredProfiles
      },
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
    });
  });
};