import {
  GroupRole,
  GroupStatus,
  GroupVisibility,
  MembershipStatus,
  PaymentProvider,
  PrismaClient,
  SubscriptionStatus
} from "@prisma/client";
import bcrypt from "bcryptjs";

const prisma = new PrismaClient();

function addDays(date: Date, days: number): Date {
  const copy = new Date(date);
  copy.setDate(copy.getDate() + days);
  return copy;
}

async function seedGroups(ownerId: string, planId: string): Promise<void> {
  const now = new Date();
  const groups = [
    {
      slug: "prasassti",
      name: "Prasassti",
      description: "Private network for professionals and founders.",
      visibility: GroupVisibility.PUBLIC
    },
    {
      slug: "usahawan",
      name: "Usahawan",
      description: "Networking community for entrepreneurs and business owners.",
      visibility: GroupVisibility.PUBLIC
    }
  ];

  for (const entry of groups) {
    const group = await prisma.group.upsert({
      where: { slug: entry.slug },
      update: {
        name: entry.name,
        description: entry.description,
        visibility: entry.visibility,
        status: GroupStatus.ACTIVE,
        ownerId
      },
      create: {
        slug: entry.slug,
        name: entry.name,
        description: entry.description,
        visibility: entry.visibility,
        status: GroupStatus.ACTIVE,
        ownerId
      }
    });

    const subscription = await prisma.subscription.findFirst({
      where: { groupId: group.id }
    });

    if (!subscription) {
      await prisma.subscription.create({
        data: {
          ownerUserId: ownerId,
          groupId: group.id,
          planId,
          provider: PaymentProvider.MANUAL,
          status: SubscriptionStatus.ACTIVE,
          currentPeriodStart: now,
          currentPeriodEnd: addDays(now, 30),
          gracePeriodEndsAt: addDays(now, 37)
        }
      });
    }

    await prisma.groupMembership.upsert({
      where: {
        groupId_userId: {
          groupId: group.id,
          userId: ownerId
        }
      },
      update: {
        role: GroupRole.OWNER,
        status: MembershipStatus.APPROVED,
        approvedByUserId: ownerId,
        approvedAt: now
      },
      create: {
        groupId: group.id,
        userId: ownerId,
        role: GroupRole.OWNER,
        status: MembershipStatus.APPROVED,
        approvedByUserId: ownerId,
        approvedAt: now
      }
    });

    await prisma.memberProfile.upsert({
      where: {
        groupId_userId: {
          groupId: group.id,
          userId: ownerId
        }
      },
      update: {
        username: "platform-owner",
        fullName: "Platform Owner",
        shortBio: "Owner and operator of Bangsa platform.",
        currentRole: "Platform Administrator",
        business: "Bangsa",
        city: "Kuala Lumpur",
        country: "Malaysia",
        websiteUrl: "https://bangsa.org"
      },
      create: {
        groupId: group.id,
        userId: ownerId,
        username: "platform-owner",
        fullName: "Platform Owner",
        shortBio: "Owner and operator of Bangsa platform.",
        currentRole: "Platform Administrator",
        business: "Bangsa",
        city: "Kuala Lumpur",
        country: "Malaysia",
        websiteUrl: "https://bangsa.org"
      }
    });
  }
}

async function main(): Promise<void> {
  const passwordHash = await bcrypt.hash("Bangsa123!", 12);

  const starterPlan = await prisma.plan.upsert({
    where: { code: "bangsa-starter-monthly" },
    update: {
      name: "Bangsa Starter",
      description: "Monthly subscription to create and run one group.",
      priceCents: 9900,
      currency: "USD",
      isActive: true
    },
    create: {
      code: "bangsa-starter-monthly",
      name: "Bangsa Starter",
      description: "Monthly subscription to create and run one group.",
      priceCents: 9900,
      currency: "USD",
      isActive: true
    }
  });

  const platformOwner = await prisma.user.upsert({
    where: { email: "owner@bangsa.org" },
    update: {
      fullName: "Platform Owner",
      passwordHash,
      isPlatformOwner: true
    },
    create: {
      email: "owner@bangsa.org",
      fullName: "Platform Owner",
      passwordHash,
      isPlatformOwner: true
    }
  });

  await seedGroups(platformOwner.id, starterPlan.id);

  console.log("Seed complete.");
  console.log("Platform owner login: owner@bangsa.org / Bangsa123!");
}

main()
  .catch((error) => {
    console.error(error);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });