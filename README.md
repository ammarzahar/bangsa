# Bangsa Laravel 11 Rewrite

Laravel 11 + Blade (Breeze-style auth) rewrite of the Bangsa MVP backend.

## Stack

- PHP 8.2+
- Laravel 11
- Blade views
- Single database tenancy (`group_id` isolation)
- Eloquent models + policies + middleware

## Implemented Scope

- Auth: register/login/logout/password reset/email verification
- Group lifecycle: paid group creation, settings, visibility
- Membership workflow: join request, admin approve/reject
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

4. Run migrations + seeders:

```bash
php artisan migrate:fresh --seed
```

5. Start app:

```bash
php artisan serve
```

## Seed Login

- Email: `owner@bangsa.org`
- Password: `Bangsa123!`

## Important Runtime Note

This project targets **Laravel 11 / PHP 8.2+**. If your local PHP is below 8.2, Artisan commands may fail. Upgrade PHP first.

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