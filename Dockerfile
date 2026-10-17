FROM php:8.1-fpm

# git and unzip are used by Composer to download the packages
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

# Install necessary extensions
RUN docker-php-ext-install pdo pdo_mysql

# Composer inside the container:  docker compose exec -u www-data app composer install
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_HOME=/tmp/composer

# PHP-FPM runs as www-data. Giving www-data the user ID of the WSL user who owns
# the mounted project files lets the app write logs/app.log, and files created
# by Composer stay editable from WSL. 1000 is the first user on Ubuntu/WSL;
# check yours with `id -u` and build with --build-arg UID=<id> if it differs.
ARG UID=1000
RUN usermod -u ${UID} www-data && groupmod -g ${UID} www-data

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .
