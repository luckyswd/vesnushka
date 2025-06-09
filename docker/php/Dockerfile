FROM php:8.4-fpm-alpine3.19

WORKDIR /var/www/html

# Устанавливаем composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

# Устанавливаем нужные пакеты и PHP расширения
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
        g++ \
        make \
        autoconf \
        libtool \
    && apk add --no-cache \
        make \
        libpng \
        libpng-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        icu \
        icu-dev \
        libzip \
        libzip-dev \
        libxslt \
        libxslt-dev \
        libpq \
        postgresql-dev \
        oniguruma-dev \
        acl \
        procps \
        openssl \
        gnupg \
        unzip \
        git \
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

