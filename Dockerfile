# syntax=docker/dockerfile:1

##### Base build with common steps
FROM dunglas/frankenphp:1-php8.4 AS base

WORKDIR /app

ARG USER=appuser

RUN useradd ${USER}; \
	setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
	chown -R ${USER}:${USER} /config/caddy /data/caddy

RUN install-php-extensions \
	@composer \
    apcu \
	intl \
	opcache \
	zip

##### Build development image target. Expects source (incl. composer dependencies in vendor/) to be mounted at /app
FROM base AS dev

VOLUME /app

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

USER ${USER}

##### Build production image target. Installs composer dependencies and copies source code to container.
FROM base AS prod

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY composer.* ./
RUN composer install --no-cache --no-interaction --no-dev --no-progress

COPY . .

RUN composer dump-autoload --optimize

RUN chown -R ${USER}:${USER} /app && \
    chmod -R 755 /app

USER ${USER}
