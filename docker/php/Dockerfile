FROM php:8.3-fpm-alpine

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

RUN apk add --no-cache \
        zlib \
        libzip \
        libpng \
        libjpeg-turbo \
        postgresql-libs \
    && apk add --no-cache --virtual .build-deps \
        zlib-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        postgresql-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        opcache \
        zip \
        gd \
        bcmath \
        pgsql \
        pdo_pgsql \
    && (php -m | grep -iq '^fileinfo$' || docker-php-ext-install -j"$(nproc)" fileinfo) \
    && apk del .build-deps

RUN { \
      echo 'expose_php = Off'; \
      echo 'memory_limit = 256M'; \
      echo 'upload_max_filesize = 16M'; \
      echo 'post_max_size = 20M'; \
      echo 'max_file_uploads = 20'; \
    } > /usr/local/etc/php/conf.d/pandata.ini

COPY . .
