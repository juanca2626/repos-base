# Used for prod build.

ARG BASE_IMAGE

#FROM 211125785984.dkr.ecr.us-east-1.amazonaws.com/ecr_docker-php8.1-composer:latest AS php

# Usar el argumento como el FROM
FROM ${BASE_IMAGE} AS php

# Copy configuration files.
COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
COPY ./docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf

# add supervisor
RUN mkdir -p /var/log/supervisor
COPY --chown=root:root ./docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set working directory to /var/www.
WORKDIR /var/www

#COPY .env.prod /var/www/.env

# Copy files from current folder to container current folder (set in workdir).
COPY --chown=www-data:www-data . .

# Create laravel caching folders.
RUN mkdir -p /var/www/storage/framework
RUN mkdir -p /var/www/storage/framework/cache
RUN mkdir -p /var/www/storage/framework/testing
RUN mkdir -p /var/www/storage/framework/sessions
RUN mkdir -p /var/www/storage/framework/views

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

## Adjust user permission & group
#RUN usermod --uid 1000 www-data
#RUN groupmod --gid 1001 www-data

RUN chmod +x docker/entrypoint.sh

# Run the entrypoint file.
CMD [ "./docker/entrypoint.sh" ]
