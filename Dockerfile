FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
      bash \
      git \
      curl \
      icu-dev \
      libzip-dev \
      oniguruma-dev \
      postgresql-dev \
      unzip \
    && docker-php-ext-install -j$(nproc) \
      intl \
      mbstring \
      zip \
      pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY ./docker/php/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]

