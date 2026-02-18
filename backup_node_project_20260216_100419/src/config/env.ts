import dotenv from "dotenv";

dotenv.config();

function requireEnv(name: string): string {
  const value = process.env[name];
  if (!value) {
    throw new Error(`Missing required environment variable: ${name}`);
  }
  return value;
}

function parsePort(value: string): number {
  const parsed = Number(value);
  if (!Number.isInteger(parsed) || parsed <= 0) {
    throw new Error(`Invalid PORT value: ${value}`);
  }
  return parsed;
}

export const env = {
  PORT: parsePort(process.env.PORT ?? "4000"),
  DATABASE_URL: requireEnv("DATABASE_URL"),
  JWT_SECRET: requireEnv("JWT_SECRET"),
  CORS_ORIGIN: process.env.CORS_ORIGIN ?? "http://localhost:3000"
} as const;