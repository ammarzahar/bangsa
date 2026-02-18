import { GroupStatus, GroupVisibility, MembershipStatus } from "@prisma/client";
import { prisma } from "../../lib/prisma.js";
import { isGroupAdminRole } from "../../lib/rules.js";

export async function findGroupBySlug(slug: string) {
  return prisma.group.findUnique({
    where: { slug },
    include: {
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
}

export async function findApprovedMembership(groupId: string, userId: string) {
  const membership = await prisma.groupMembership.findUnique({
    where: {
      groupId_userId: {
        groupId,
        userId
      }
    }
  });

  if (!membership || membership.status !== MembershipStatus.APPROVED) {
    return null;
  }

  return membership;
}

export async function findAdminMembership(groupId: string, userId: string) {
  const membership = await findApprovedMembership(groupId, userId);
  if (!membership) {
    return null;
  }

  if (!isGroupAdminRole(membership.role)) {
    return null;
  }

  return membership;
}

export async function canUserViewGroup(
  groupId: string,
  visibility: GroupVisibility,
  userId?: string
): Promise<boolean> {
  if (visibility === GroupVisibility.PUBLIC) {
    return true;
  }

  if (!userId) {
    return false;
  }

  const membership = await findApprovedMembership(groupId, userId);
  return Boolean(membership);
}

export function isGroupOperational(status: GroupStatus): boolean {
  return status === GroupStatus.ACTIVE;
}