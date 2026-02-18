import Fastify from "fastify";
import cors from "@fastify/cors";
import jwt from "@fastify/jwt";
import sensible from "@fastify/sensible";
import { env } from "./config/env.js";
import { authRoutes } from "./modules/auth/routes.js";
import { billingRoutes } from "./modules/billing/routes.js";
import { groupsRoutes } from "./modules/groups/routes.js";
import { membersRoutes } from "./modules/members/routes.js";
import { dashboardRoutes } from "./modules/dashboard/routes.js";
import { platformRoutes } from "./modules/platform/routes.js";

export function buildApp() {
  const app = Fastify({ logger: true });

  app.register(sensible);
  app.register(cors, {
    origin: env.CORS_ORIGIN.split(",").map((origin) => origin.trim())
  });
  app.register(jwt, {
    secret: env.JWT_SECRET,
    sign: {
      expiresIn: "7d"
    }
  });

  app.decorate("authenticate", async function authenticate(request, reply) {
    try {
      await request.jwtVerify();
    } catch {
      return reply.code(401).send({ message: "Unauthorized" });
    }
  });

  app.get("/health", async () => ({ ok: true, service: "bangsa-api" }));

  app.register(authRoutes, { prefix: "/api/v1/auth" });
  app.register(billingRoutes, { prefix: "/api/v1/billing" });
  app.register(groupsRoutes, { prefix: "/api/v1/groups" });
  app.register(membersRoutes, { prefix: "/api/v1/groups" });
  app.register(dashboardRoutes, { prefix: "/api/v1/dashboard" });
  app.register(platformRoutes, { prefix: "/api/v1/platform" });

  app.setErrorHandler((error, _request, reply) => {
    app.log.error(error);

    if (reply.sent) {
      return;
    }

    reply.code(500).send({
      message: "Internal server error"
    });
  });

  return app;
}
