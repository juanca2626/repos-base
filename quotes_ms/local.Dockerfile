# Used for DEV & Local.
FROM php:8.2-fpm AS php

LABEL org.opencontainers.image.authors="jeanpierre.garay@tui.com"

# Set environment variables
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_ENABLE_CLI=0
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV PHP_OPCACHE_REVALIDATE_FREQ=0

ENV COMPOSER_ALLOW_SUPERUSER='1'
ENV COMPOSER_MEMORY_LIMIT='-1'

# Install dependencies.
RUN apt-get update \
    && apt-get install -y --force-yes --no-install-recommends \
        supervisor \
        zlib1g-dev \
        libzip-dev \
        libz-dev \
        libpq-dev \
        libjpeg-dev \
        libpng-dev \
        libfreetype6-dev \
        libssl-dev \
        openssh-server \
        libmagickwand-dev \
        git \
        cron \
        nano \
        libxml2-dev \
        libreadline-dev \
        libgmp-dev \
        unzip \
        nginx \
        iputils-ping \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get autoremove -y && apt-get autoclean -y

# Install PHP extensions.
RUN docker-php-ext-install soap exif pcntl pdo_mysql pdo_pgsql bcmath intl gmp opcache zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd
# Install PECL extensions.
RUN pecl install redis imagick xdebug && docker-php-ext-enable redis imagick xdebug

# Composer
COPY --from=composer:2.7.7 /usr/bin/composer /usr/bin/composer


# Copy configuration files.
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

# add supervisor
RUN mkdir -p /var/log/supervisor
COPY --chown=root:root ./docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory to /var/www.
WORKDIR /var/www

# Copy files from current folder to container current folder (set in workdir).
COPY --chown=www-data:www-data . .

# Create laravel caching folders.
RUN mkdir -p /var/www/storage/framework /var/www/storage/framework/cache \
    /var/www/storage/framework/testing /var/www/storage/framework/sessions \
    /var/www/storage/framework/views

RUN mkdir -p /var/www/storage /var/www/storage/logs /var/www/storage/framework \
    /var/www/storage/framework/sessions /var/www/bootstrap

# Fix files ownership.
RUN chown -R www-data /var/www/storage
RUN chown -R www-data /var/www/storage/framework
RUN chown -R www-data /var/www/storage/framework/sessions

# Set correct permission.
RUN chmod -R 755 /var/www/storage
RUN chmod -R 755 /var/www/storage/logs
RUN chmod -R 755 /var/www/storage/framework
RUN chmod -R 755 /var/www/storage/framework/sessions
RUN chmod -R 755 /var/www/bootstrap

RUN chmod +x docker/entrypoint.local.sh

# Run the entrypoint file.
CMD ["./docker/entrypoint.local.sh"]
