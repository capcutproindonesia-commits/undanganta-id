# Google Cloud deployment

Recommended architecture:

- GitHub: source of truth
- Artifact Registry: container images
- Cloud Run: Laravel web container
- Cloud SQL for MySQL: relational data
- Secret Manager: APP_KEY, DB credentials and production secrets
- Cloud Storage: persistent uploaded media, preferably mounted or integrated through a dedicated Laravel filesystem adapter
- Optional Memorystore/Redis: cache and queues at scale

Suggested region for an Indonesia-focused audience: `asia-southeast2` (Jakarta), when the required products are available there.

## 1. Build the image

The included Docker image listens on the Cloud Run `PORT` environment variable and defaults to 8080.

Example Cloud Build invocation:

```bash
gcloud builds submit \
  --config cloudbuild.yaml \
  --substitutions _IMAGE=asia-southeast2-docker.pkg.dev/PROJECT_ID/undanganta/app:manual
```

## 2. Cloud SQL

Create a MySQL 8 instance and database, then attach the Cloud SQL instance to the Cloud Run service. Configure Laravel with environment variables/secrets such as:

```text
DB_CONNECTION=mysql
DB_DATABASE=undanganta
DB_USERNAME=...
DB_PASSWORD=...
DB_SOCKET=/cloudsql/PROJECT_ID:REGION:INSTANCE_NAME
```

Do not put the real values in GitHub.

## 3. Runtime variables

Production values should include at least:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-CLOUD-RUN-URL-OR-DOMAIN
APP_KEY=...
LOG_CHANNEL=stderr
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

`APP_KEY`, database credentials, mail credentials, payment credentials, and any third-party API keys belong in Secret Manager.

## 4. Persistent uploads

Cloud Run container files are ephemeral. Do not rely on the container filesystem for invitation covers, galleries, or guest photos in production.

Two safe approaches are:

1. Mount a Cloud Storage bucket to the app's upload path and keep Laravel's local/public disk abstraction.
2. Add a dedicated Google Cloud Storage Laravel filesystem adapter and configure the application to use it.

Before public launch, choose one approach and test upload, read, delete, and signed/public URL behavior end-to-end.

## 5. Migrations

Run migrations separately from normal web startup:

```bash
php artisan migrate --force
```

For Cloud Run, use a Cloud Run Job or a controlled CI/CD release step. Do not let every web instance race to run migrations at boot.

## 6. Queue workers

The web image intentionally does not run a queue worker inside the web process. For production queues, run a separate worker service/job using:

```bash
php artisan queue:work --sleep=2 --tries=3 --timeout=120
```

This makes the same repository portable to Cloud Run, a VPS, Railway-style platforms, and other container hosts.

## 7. Custom domains

The application already resolves published invitations by request `Host` when a matching `custom_domain` is stored. DNS and TLS must still be configured at the hosting/CDN layer. Do not assume the brand name `UNDANGANTA.ID` is the deployment domain.
