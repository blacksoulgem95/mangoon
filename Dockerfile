FROM php:8.4-cli

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    imagemagick \
    libmagickwand-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN useradd -G www-data,root -u 1000 -d /home/dev dev \
    && mkdir -p /home/dev/.composer && \
    chown -R dev:dev /home/dev

WORKDIR /var/www/html

EXPOSE 8000

USER dev

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
