FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libbz2-dev

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    bz2

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN echo "upload_max_filesize = 100M\npost_max_size = 100M\nmemory_limit = 256M" > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www
