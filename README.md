# Billing Panel — 4LivePro Latino

IPTV reseller billing panel, deployed on a Linux VPS with a LAMP stack (Apache + MySQL + PHP).
Customers buy M3U packages, upload their payment proof, an administrator approves the order,
and the system automatically provisions the line on a **XUI ONE** panel via its reseller API.

## Features

- Package catalog by category, with trial/demo packages and paid packages.
- Shopping cart and order flow with payment proof upload (bank transfer, Zelle, etc.).
- Admin panel: orders (approve/reject/retry), packages, categories, payment methods, users.
- **XUI ONE** integration to automatically create/query M3U lines when an order is approved.
- Automatic activation of trial lines after email verification (prevents abuse with fake emails).
- **Telegram** notifications (new orders, activated lines) and **email** notifications
  (SMTP configurable from the admin panel itself, no need to touch `.env`).
- Line expiration reminders by email (`php artisan lines:send-expiration-reminders`).
- Cloudflare Turnstile protection, configurable from the admin panel.

## Stack

PHP 8.3 · Apache · MySQL (production) / SQLite (local development) · Blade + Tailwind CSS +
Alpine.js · Vite.

## Documentation

- [`CLAUDE.md`](CLAUDE.md) — architecture, business flow, data model, infrastructure (VPS,
  deployment, repos) and change log. Start here to understand the project.
- [`INSTALL.md`](INSTALL.md) — how to install this panel on a fresh LAMP server
  (prerequisites, database, admin user) using [`install.sh`](install.sh).

## Local development

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed          # loads sample category/packages/payment methods
php artisan app:create-admin admin@example.com "your-password" --name="Administrator"
npm install && npm run dev
php artisan serve
```

## License

Private project of 4LivePro Latino. Not open-source software for redistribution.
