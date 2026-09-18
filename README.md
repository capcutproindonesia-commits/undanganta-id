# UNDANGANTA.ID — Laravel 13

Production-oriented SaaS undangan digital built natively on Laravel 13.

> `UNDANGANTA.ID` is the project/brand name. This repository does not assume that `undanganta.id` is an owned or configured domain.

## Stack
- Laravel 13 / PHP 8.3+
- MySQL 8+
- Blade + Vite
- Database/Redis-ready queue and cache
- Public filesystem with a production path for external object storage or Cloud Run volume mounts
- Docker, Nginx, PHP-FPM

## Core features
- Register/login/logout
- Multi-user invitation workspace
- Create/edit/delete/publish invitations
- Themes, cover, gallery, music, story, maps
- Personalized guest token links
- RSVP, wishes, party size, CSV export
- Signed check-in endpoint
- Digital gift account data
- Guest photo uploads with admin moderation
- Free / Premium / Pro plans and manual payment verification
- Admin control center
- Custom-domain resolution from the Host header
- View counter

## Local setup
```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Or run the Docker stack:
```bash
cp .env.example .env
docker compose up --build
```

The Docker web container listens on port `8080` by default.

## CI
GitHub Actions runs PHP dependency installation, frontend build, migrations/tests on SQLite, and a production Docker image build for pushes to `main` and pull requests.

## Production
- Never commit `.env` or production secrets.
- Store secrets in the hosting provider secret manager.
- Run database migrations as a release/deploy step.
- Use persistent/external storage for uploads; Cloud Run's writable filesystem is ephemeral.
- Read `docs/DEPLOYMENT.md`, `docs/GOOGLE_CLOUD.md`, and `docs/SECURITY.md` before going live.
