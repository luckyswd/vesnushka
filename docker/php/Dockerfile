FROM php:8.4-fpm-alpine3.19

WORKDIR /var/www/html

# Устанавливаем composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

# Устанавливаем нужные пакеты и PHP расширения
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
        g++ \
        git \
        unzip \
        libzip-dev \
        freetype-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        icu-dev \
        oniguruma-dev \
        libxslt-dev \
        acl-dev \
        postgresql-dev \
    && apk add --no-cache \
        acl \
        procps \
        openssl \
        gnupg \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        xsl \
        gd \
        intl \
        opcache \
        exif \
        mbstring \
        xml \
    && apk del .build-deps
