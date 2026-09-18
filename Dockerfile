FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json ./
RUN npm install --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js ./vite.config.js
RUN npm run build

FROM php:8.4-fpm-alpine
RUN apk add --no-cache \
    bash icu-dev libzip-dev oniguruma-dev mysql-client nginx supervisor gettext \
    && docker-php-ext-install pdo_mysql intl zip bcmath opcache

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY docker/nginx.template.conf /etc/nginx/http.d/default.conf.template
COPY docker/start-container.sh /usr/local/bin/start-container
COPY docker/supervisord.conf /etc/supervisord.conf

RUN chmod +x /usr/local/bin/start-container \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENV PORT=8080
EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/start-container"]
