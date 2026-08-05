FROM php:8.2-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        curl \
        zip \
        unzip \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libzip-dev \
        libbz2-dev \
        xfonts-base \
        xfonts-75dpi \
        fontconfig \
        libjpeg62-turbo \
        ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# wkhtmltopdf (Debian bookworm repos drop it; install dari GitHub release)
RUN curl -L -o /tmp/wkhtmltox.deb \
      https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.bookworm_amd64.deb \
    && apt-get update \
    && apt-get install -y --no-install-recommends /tmp/wkhtmltox.deb \
    && rm /tmp/wkhtmltox.deb \
    && rm -rf /var/lib/apt/lists/* \
    && ln -sf /usr/local/bin/wkhtmltopdf /usr/bin/wkhtmltopdf

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
