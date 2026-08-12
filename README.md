# Billing Panel — 4LivePro Latino

IPTV reseller billing panel, deployed on a Linux VPS with a LAMP stack (Apache + MySQL + PHP).
Customers buy M3U packages, upload their payment proof, an administrator approves the order,
and the system automatically provisions the line on a **XUI ONE** panel via its reseller API.

## Features

**Storefront**
- Package catalog organized by category, with a category sidebar and compact package cards.
- Trial/demo packages (automatic activation after email verification) and paid packages.
- Optional stock/availability limit per package (numeric cap counted from when it's set, or
  an instant manual "sold out" override) — orders are protected against overselling under
  real concurrent checkouts.
- Shopping cart and order flow with payment proof upload (bank transfer, Zelle, etc.).
- Customer support ticket system (logged-in customers and guests without an account).
- Customer dashboard: active M3U lines, order history with per-order invoice PDF download.
- Site marked `noindex` (not intended to be publicly indexed by search engines).

**Admin panel** (separate URL, login and session-timeout from the customer site)
- Orders: approve/reject/retry, with automatic line provisioning on approval.
- Lines: renew, apply a different package, suspend/reactivate, reset password, resend
  credentials, sync with XUI, delete — with a full audit trail per line.
- Packages, categories, payment methods (CRUD), with per-package stock control.
- Users: list, verify email manually, block/unblock.
- Support tickets: reply, reassign, close, with Telegram + email alerts.
- Email template editor for every transactional email, with live preview and test send.
- Dashboard with date-range filters and CSV export of orders.
- Configurable from the panel itself (no `.env` editing needed): XUI ONE connection, SMTP,
  Cloudflare Turnstile, Telegram bot.

**Integrations & automation**
- **XUI ONE** reseller API integration to create/query/renew/suspend M3U lines.
- **Telegram** bot: order/line/ticket notifications, a `/ventashoy` sales-summary command,
  and an automatic daily sales report.
- Automated line-expiration reminders and expired-line notices (`php artisan
  lines:send-expiration-reminders` / `lines:send-expired-notices`).
- Daily automated database backups on the server (rotated, kept 14 days).

## Stack

PHP 8.3 · Apache · MySQL · Blade + Tailwind CSS + Alpine.js · Vite.

## Documentation

- [`CLAUDE.md`](CLAUDE.md) — architecture, business flow, data model, infrastructure (VPS,
  deployment, repos), security notes and full change log. Start here to understand the project.
- [`INSTALL.md`](INSTALL.md) — how to install this panel on a fresh LAMP server
  (prerequisites, database, admin user) using [`install.sh`](install.sh).

## Local development

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed                          # loads sample category/packages/payment methods
php artisan app:create-admin admin "your-password" --name="Administrator"
npm install && npm run dev
php artisan serve
```

The admin panel lives at `/adm_4livepro` (not `/admin`), and admins log in with the
**username** given to `app:create-admin` — not an email address.

## License

Private project of 4LivePro Latino. Not open-source software for redistribution.
