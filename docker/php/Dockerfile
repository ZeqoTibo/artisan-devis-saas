FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    libicu-dev \
    default-libmysqlclient-dev \
    libzip-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl opcache pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
