# Bangsa Laravel Application

Laravel 12 + Blade application for the Bangsa community platform.

## Stack

- PHP 8.2+
- Laravel 12
- Blade views
- Single database tenancy (`group_id` isolation)
- Eloquent models + policies + middleware

## Implemented Scope

- Auth: register/login/logout/password reset/email verification
- Group lifecycle: paid group creation, settings, visibility
- Membership workflow: join request, admin approve/reject
- Paid communities: TAUT checkout handoff and signed payment webhook activation
- Community branding uploads with image size and dimension validation
- Member profiles: rich fields + SEO URL per group
- Directory: searchable/filterable per group
- Dashboards:
  - Platform owner dashboard + group moderation (suspend/activate)
  - Group admin dashboard + membership stats
- Billing:
  - Plans/subscriptions
  - Subscription check before group creation
  - Payment gateway stubs (Stripe/Toyyibpay/manual)
- Seed data:
  - `prasassti`
  - `usahawan`
  - `owner@bangsa.org` (platform owner/admin)

## URL Structure

- Landing: `/`
- Group page: `/{group_slug}`
- Member profile: `/{group_slug}/member/{username}`

## Setup

1. Install dependencies:

```bash
composer install
npm install
```

2. Environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure database in `.env` (`DB_*` values).

For paid communities, configure the same random secret in both Bangsa and TAUT:

```env
TAUT_BANGSA_WEBHOOK_SECRET=replace-with-a-long-random-secret
```

4. Run migrations + seeders:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

5. Start app:

```bash
php artisan serve
```

## Seed Login

- Email: `owner@bangsa.org`
- Password: `Bangsa123!`

## Important Runtime Note

This project targets **Laravel 12 / PHP 8.2+**. If your local PHP is below 8.2, Artisan commands may fail. Upgrade PHP first.

## Paid Community Flow

1. The organiser creates a membership product in TAUT and pastes its checkout URL into Bangsa.
2. Bangsa signs the member and community context before redirecting to TAUT checkout.
3. TAUT stores that context with the order and remains the source of truth for payments and order management.
4. When TAUT marks the order paid, it sends a signed webhook to `/integrations/taut/webhook`.
5. Bangsa verifies the signature and activates the membership idempotently.

## Key Files

- `routes/web.php`
- `routes/auth.php`
- `app/Http/Middleware/ResolveGroupFromSlug.php`
- `app/Policies/GroupPolicy.php`
- `app/Policies/GroupMembershipPolicy.php`
- `app/Http/Controllers/*`
- `app/Services/Payments/*`
- `database/migrations/*`
- `database/seeders/*`
- `resources/views/*`
