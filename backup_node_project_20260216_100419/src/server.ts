import { env } from "./config/env.js";
import { prisma } from "./lib/prisma.js";
import { buildApp } from "./app.js";

async function start() {
  const app = buildApp();

  const shutdown = async (signal: string) => {
    app.log.info({ signal }, "Shutting down Bangsa API");
    await app.close();
    await prisma.$disconnect();
    process.exit(0);
  };

  process.on("SIGINT", () => {
    void shutdown("SIGINT");
  });

  process.on("SIGTERM", () => {
    void shutdown("SIGTERM");
  });

  try {
    await app.listen({
      port: env.PORT,
      host: "0.0.0.0"
    });
  } catch (error) {
    app.log.error(error);
    await prisma.$disconnect();
    process.exit(1);
  }
}

void start();