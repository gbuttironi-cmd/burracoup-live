FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash \
    libpq \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    $PHPIZE_DEPS

RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo_pgsql intl zip opcache

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Useful php.ini tweaks
RUN { \
  echo "memory_limit=512M"; \
  echo "upload_max_filesize=50M"; \
  echo "post_max_size=50M"; \
} > /usr/local/etc/php/conf.d/99-custom.ini
