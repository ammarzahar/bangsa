import type { FastifyPluginAsync } from "fastify";
import {
  PaymentProvider,
  SubscriptionStatus
} from "@prisma/client";
import { z } from "zod";
import { prisma } from "../../lib/prisma.js";

const createSubscriptionSchema = z.object({
  planCode: z.string().min(1),
  provider: z.nativeEnum(PaymentProvider)
});

function addDays(date: Date, days: number): Date {
  const copy = new Date(date);
  copy.setDate(copy.getDate() + days);
  return copy;
}

export const billingRoutes: FastifyPluginAsync = async (fastify) => {
  fastify.get("/plans", async (_request, reply) => {
    const plans = await prisma.plan.findMany({
      where: { isActive: true },
      orderBy: { priceCents: "asc" }
    });

    return reply.send({ plans });
  });

  fastify.post("/subscriptions", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    const parsed = createSubscriptionSchema.safeParse(request.body);
    if (!parsed.success) {
      return reply.code(400).send({ message: "Invalid request body", errors: parsed.error.flatten() });
    }

    const { planCode, provider } = parsed.data;
    const plan = await prisma.plan.findUnique({ where: { code: planCode } });

    if (!plan || !plan.isActive) {
      return reply.code(404).send({ message: "Plan not found" });
    }

    const now = new Date();
    const subscription = await prisma.subscription.create({
      data: {
        ownerUserId: request.user.userId,
        planId: plan.id,
        provider,
        status: SubscriptionStatus.ACTIVE,
        currentPeriodStart: now,
        currentPeriodEnd: addDays(now, 30),
        gracePeriodEndsAt: addDays(now, 37)
      },
      include: {
        plan: true
      }
    });

    return reply.code(201).send({
      message: "Subscription created and ready for group creation",
      subscription
    });
  });

  fastify.get("/subscriptions/me", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    const subscriptions = await prisma.subscription.findMany({
      where: {
        ownerUserId: request.user.userId
      },
      include: {
        plan: true,
        group: {
          select: {
            id: true,
            slug: true,
            name: true,
            status: true
          }
        }
      },
      orderBy: {
        createdAt: "desc"
      }
    });

    return reply.send({ subscriptions });
  });
};