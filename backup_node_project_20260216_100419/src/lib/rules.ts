import { GroupRole, MembershipStatus, SubscriptionStatus } from "@prisma/client";

export function isGroupAdminRole(role: GroupRole): boolean {
  return role === GroupRole.OWNER || role === GroupRole.ADMIN;
}

export function isApprovedMembership(status: MembershipStatus): boolean {
  return status === MembershipStatus.APPROVED;
}

export function isSubscriptionActiveWindow(
  status: SubscriptionStatus,
  currentPeriodEnd: Date | null,
  gracePeriodEndsAt: Date | null,
  now: Date = new Date()
): boolean {
  if (status !== SubscriptionStatus.ACTIVE && status !== SubscriptionStatus.TRIALING) {
    return false;
  }

  if (!currentPeriodEnd) {
    return true;
  }

  if (currentPeriodEnd >= now) {
    return true;
  }

  return Boolean(gracePeriodEndsAt && gracePeriodEndsAt >= now);
}