FROM php:8.1-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME=/tmp/composer

ARG UID=1000
RUN usermod -u ${UID} www-data && groupmod -g ${UID} www-data

WORKDIR /var/www/html

COPY . .
