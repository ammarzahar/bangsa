# Auth Flow Diagram (Text)

1. User registers with email/password at `POST /api/v1/auth/register`.
2. System hashes password and stores user record.
3. User logs in at `POST /api/v1/auth/login`.
4. System validates credentials and issues JWT token.
5. Client sends JWT in `Authorization: Bearer <token>`.
6. Protected routes verify JWT (`fastify.authenticate`).
7. For tenant-scoped endpoints:
   - Resolve group by `slug`.
   - Resolve membership by `(groupId, userId)`.
   - Enforce role (`OWNER`, `ADMIN`, `MEMBER`) + status (`APPROVED`).
8. For paid group creation:
   - Verify user has active unassigned subscription.
   - Only then allow `POST /api/v1/groups`.
9. For private groups:
   - Deny directory/profile/group access unless approved membership exists.
10. Platform owner bypass applies only for platform moderation routes.