#!/usr/bin/env sh
set -eu

PORT="${PORT:-8080}"
export PORT

envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

# Safe runtime optimizations. They are skipped if APP_KEY is intentionally absent,
# such as during a bare image smoke test.
php artisan storage:link >/dev/null 2>&1 || true

if [ -n "${APP_KEY:-}" ]; then
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
