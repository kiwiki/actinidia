FROM php:7.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install xdebug
RUN pecl install xdebug-2.9.6 && docker-php-ext-enable xdebug
COPY ./docker/xdebug/99-xdebug.ini /usr/local/etc/php/conf.d/

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/actinidia actinidia
RUN mkdir -p /home/actinidia/.composer && \
    chown -R actinidia:actinidia /home/actinidia

# Set working directory
WORKDIR /var/www

USER actinidia
