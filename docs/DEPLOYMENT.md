# Deployment

## Generic container host

Required services:
- PHP/Laravel web container from `Dockerfile`
- MySQL 8+
- persistent object/file storage for uploads
- optional Redis
- optional separate queue worker

The container listens on `${PORT:-8080}`.

## Release checklist

1. Set `APP_ENV=production` and `APP_DEBUG=false`.
2. Generate a strong `APP_KEY` and store it as a secret.
3. Configure MySQL credentials through secrets/environment variables.
4. Configure persistent upload storage; never depend on an ephemeral container disk.
5. Build frontend assets through the Docker multistage build.
6. Run `php artisan migrate --force` as a controlled release step.
7. Configure HTTPS and the final `APP_URL`.
8. Configure a dedicated queue worker if asynchronous jobs are enabled.
9. Seed production data only when intentionally required.
10. Verify `/up`, login, invitation creation, publish, RSVP, upload, and admin flows after deployment.

## Local Docker

```bash
cp .env.example .env
docker compose up --build
```

Open `http://localhost:8080`.

For Google Cloud-specific instructions, see `docs/GOOGLE_CLOUD.md`.
