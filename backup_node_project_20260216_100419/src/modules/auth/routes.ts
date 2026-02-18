import type { FastifyPluginAsync } from "fastify";
import { Prisma } from "@prisma/client";
import { z } from "zod";
import { prisma } from "../../lib/prisma.js";
import { hashPassword, verifyPassword } from "../../lib/password.js";

const registerSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8).max(72),
  fullName: z.string().min(1).max(120).optional()
});

const loginSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8).max(72)
});

function toUserDto(user: {
  id: string;
  email: string;
  fullName: string | null;
  isPlatformOwner: boolean;
}) {
  return {
    id: user.id,
    email: user.email,
    fullName: user.fullName,
    isPlatformOwner: user.isPlatformOwner
  };
}

export const authRoutes: FastifyPluginAsync = async (fastify) => {
  fastify.post("/register", async (request, reply) => {
    const parsed = registerSchema.safeParse(request.body);
    if (!parsed.success) {
      return reply.code(400).send({ message: "Invalid request body", errors: parsed.error.flatten() });
    }

    const { email, password, fullName } = parsed.data;

    try {
      const passwordHash = await hashPassword(password);
      const user = await prisma.user.create({
        data: {
          email,
          passwordHash,
          fullName
        },
        select: {
          id: true,
          email: true,
          fullName: true,
          isPlatformOwner: true
        }
      });

      const token = fastify.jwt.sign({
        userId: user.id,
        email: user.email,
        isPlatformOwner: user.isPlatformOwner
      });

      return reply.code(201).send({
        token,
        user: toUserDto(user)
      });
    } catch (error) {
      if (error instanceof Prisma.PrismaClientKnownRequestError && error.code === "P2002") {
        return reply.code(409).send({ message: "Email is already in use" });
      }
      throw error;
    }
  });

  fastify.post("/login", async (request, reply) => {
    const parsed = loginSchema.safeParse(request.body);
    if (!parsed.success) {
      return reply.code(400).send({ message: "Invalid request body", errors: parsed.error.flatten() });
    }

    const { email, password } = parsed.data;

    const user = await prisma.user.findUnique({
      where: { email }
    });

    if (!user) {
      return reply.code(401).send({ message: "Invalid credentials" });
    }

    const valid = await verifyPassword(password, user.passwordHash);
    if (!valid) {
      return reply.code(401).send({ message: "Invalid credentials" });
    }

    const token = fastify.jwt.sign({
      userId: user.id,
      email: user.email,
      isPlatformOwner: user.isPlatformOwner
    });

    return reply.send({
      token,
      user: toUserDto(user)
    });
  });

  fastify.get("/me", { preHandler: [fastify.authenticate] }, async (request, reply) => {
    const user = await prisma.user.findUnique({
      where: { id: request.user.userId },
      select: {
        id: true,
        email: true,
        fullName: true,
        isPlatformOwner: true,
        createdAt: true
      }
    });

    if (!user) {
      return reply.code(404).send({ message: "User not found" });
    }

    return reply.send({ user });
  });
};