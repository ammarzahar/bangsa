# Bangsa SaaS Platform (MVP Foundation)

Backend-first, production-oriented MVP foundation for **Bangsa**: a multi-tenant networking SaaS where paid admins create and manage private/public groups with rich member directories.

## Core Capabilities Implemented

- Multi-tenant group model with tenant isolation by `groupId`
- Email/password auth (JWT)
- Paid subscription gating before group creation
- Group creation at `/api/v1/groups` with mandatory active subscription
- Admin approval workflow for new members
- Group member directory and SEO-friendly member profile URLs
- Group and platform dashboards (analytics + subscription overview)
- Platform owner controls to suspend/activate groups
- Seed data for:
  - `prasassti`
  - `usahawan`
  - platform owner with owner/admin access to both

## Quick Start

1. Copy `.env.example` to `.env` and fill credentials.
2. Start PostgreSQL (local or managed).
3. Install dependencies:

```bash
npm install
```

4. Generate Prisma client:

```bash
npm run prisma:generate
```

5. Run migrations:

```bash
npm run prisma:migrate
```

6. Seed initial data:

```bash
npm run db:seed
```

7. Start API:

```bash
npm run dev
```

Health endpoint: `GET /health`

## Login From Seed

- Email: `owner@bangsa.org`
- Password: `Bangsa123!`

## Reference Docs

- Build plan: `docs/build-plan.md`
- API routes: `docs/api-routes.md`
- Page structure: `docs/page-structure.md`
- Auth flow: `docs/auth-flow.md`
- Recommended project structure: `docs/project-structure.md`