import type { Prisma, PrismaClient } from "@prisma/client";

const USERNAME_BASE_REGEX = /[^a-z0-9]/g;

export type DbLike = PrismaClient | Prisma.TransactionClient;

export function normalizeUsername(input: string): string {
  const normalized = input
    .toLowerCase()
    .trim()
    .replace(/\s+/g, "-")
    .replace(USERNAME_BASE_REGEX, "");

  return normalized || "member";
}

export async function generateUniqueUsername(
  db: DbLike,
  groupId: string,
  seed: string
): Promise<string> {
  const base = normalizeUsername(seed);
  let candidate = base;
  let suffix = 1;

  while (true) {
    const exists = await db.memberProfile.findUnique({
      where: {
        groupId_username: {
          groupId,
          username: candidate
        }
      }
    });

    if (!exists) {
      return candidate;
    }

    candidate = `${base}${suffix}`;
    suffix += 1;
  }
}