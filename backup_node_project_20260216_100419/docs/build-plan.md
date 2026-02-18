# Step-by-Step Build Plan

## Phase 1: Foundation (Week 1)

1. Create monorepo or single-repo structure (`apps/api`, `apps/web`, `packages/shared`, `infra`).
2. Provision Postgres, Redis, object storage (S3-compatible), and secrets management.
3. Implement CI/CD, linting, tests, and migration pipeline.
4. Implement auth module (register, login, JWT, password hashing).

## Phase 2: Core Domain (Week 2)

1. Build multi-tenant schema (`groups`, `group_memberships`, `member_profiles`).
2. Implement subscription plans + subscriptions + payment records.
3. Enforce subscription gate before group creation.
4. Add seed data and fixtures for `prasassti`, `usahawan`, platform owner.

## Phase 3: Group & Membership (Week 3)

1. Implement group creation and group settings endpoints.
2. Implement membership request flow: submit, list pending, approve, reject.
3. Enforce group visibility (`PUBLIC` or `PRIVATE`) and membership-based access.
4. Add featured members controls for group admins.

## Phase 4: Directory & Profiles (Week 4)

1. Implement searchable member directory with filters (city, country, featured).
2. Implement SEO-friendly member profile endpoint by username.
3. Add profile edit with role-based controls (self vs admin).
4. Track audit events for compliance and moderation.

## Phase 5: Dashboards & Operations (Week 5)

1. Build platform dashboard metrics (groups, members, MRR, subscriptions).
2. Build group dashboard metrics (pending requests, member counts, featured count).
3. Implement super-admin moderation (`suspend` / `activate` group).
4. Add observability (structured logs, error monitoring, metrics).

## Phase 6: Production Hardening (Week 6)

1. Add rate limiting, WAF rules, and abuse protection.
2. Move JWT to short-lived access + refresh token rotation.
3. Add caching strategy for directory/profile reads.
4. Add integration adapters for Stripe, Toyyibpay, and manual payment reconciliation.
5. Run load tests for tenant scale (thousands of groups).