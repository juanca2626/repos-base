# Used for DEV & Local.
FROM php:8.1-fpm AS php

LABEL org.opencontainers.image.authors="jeanpierre.garay@tui.com"


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
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN apt-get autoremove -y && apt-get autoclean -y

# Install PHP extensions.
RUN docker-php-ext-install soap exif pcntl pdo_mysql pdo_pgsql bcmath intl gmp gd zip

# Install PECL extensions.
RUN pecl install redis imagick xdebug && docker-php-ext-enable redis imagick xdebug

# Composer
COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer


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

# Fix files ownership for all directories.
RUN chown -R www-data:www-data /var/www

# Set correct permissions.
RUN chmod -R 775 /var/www/storage
RUN chmod -R 775 /var/www/storage/logs
RUN chmod -R 775 /var/www/bootstrap/cache

# Make entrypoint script executable.
RUN chmod +x docker/entrypoint.sh

# Run the entrypoint file.
CMD ["./docker/entrypoint.local.sh"]

COPY docker/init-permissions.sh /usr/local/bin/init-permissions.sh
ENTRYPOINT ["./docker/init-permissions.sh"]
