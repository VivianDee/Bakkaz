FROM php:8.3-fpm-alpine

WORKDIR /var/www/app

RUN apk update && apk add \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo pdo_mysql \
    && apk --no-cache add nodejs npm

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy the .env file into the image
COPY .env /var/www/app/.env

USER root

RUN chmod 777 -R /var/www/app
