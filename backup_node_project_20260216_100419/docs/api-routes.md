# API Routes (MVP)

Base URL: `/api/v1`

## Auth

- `POST /auth/register`
- `POST /auth/login`
- `GET /auth/me`

## Billing

- `GET /billing/plans`
- `POST /billing/subscriptions`
- `GET /billing/subscriptions/me`

## Groups

- `POST /groups` (requires active subscription)
- `GET /groups/:slug`
- `PATCH /groups/:slug/settings`

## Membership & Profiles

- `POST /groups/:slug/join`
- `GET /groups/:slug/membership-requests`
- `POST /groups/:slug/membership-requests/:requestId/approve`
- `POST /groups/:slug/membership-requests/:requestId/reject`
- `GET /groups/:slug/members`
- `GET /groups/:slug/member/:username`
- `PATCH /groups/:slug/member/:username`

## Dashboards

- `GET /dashboard/platform/overview`
- `GET /dashboard/group/:slug/overview`

## Platform Owner Controls

- `GET /platform/groups`
- `POST /platform/groups/:slug/suspend`
- `POST /platform/groups/:slug/activate`