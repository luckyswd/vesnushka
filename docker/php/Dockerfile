FROM php:8.4-fpm-buster
ARG TIMEZONE

WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet

RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev \
    libonig-dev \
    libxslt1-dev \
    acl \
    libpq-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install \
    pdo pdo_pgsql pgsql zip xsl gd intl opcache exif mbstring xml
