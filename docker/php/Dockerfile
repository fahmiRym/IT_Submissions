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

RUN printf "upload_max_filesize = 250M\npost_max_size = 250M\nmemory_limit = 512M\nmax_execution_time = 300\nmax_input_time = 300\n" > /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www
