# Development image for running the bundle's test suite / QA in a container.
# This is NOT needed to *use* the bundle — it only gives a reproducible
# environment to run tests against a chosen PHP version without installing it
# on the host. It is export-ignored, so it never ships in the Packagist package.
#
#   docker build --build-arg PHP_VERSION=8.5 -t sofascore-bundle-dev .
#   docker run --rm -v "$PWD":/app sofascore-bundle-dev composer qa
ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-cli

# git/unzip for composer; zip+sockets extensions (sockets is required by some
# dev dependencies); pcov for coverage.
RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip libzip-dev \
 && docker-php-ext-install zip sockets \
 && pecl install pcov \
 && docker-php-ext-enable pcov \
 && rm -rf /var/lib/apt/lists/* \
 && git config --global --add safe.directory '*' \
 && echo 'memory_limit=-1' > /usr/local/etc/php/conf.d/memory-limit.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_CS_FIXER_IGNORE_ENV=1

WORKDIR /app

CMD ["composer", "qa"]
