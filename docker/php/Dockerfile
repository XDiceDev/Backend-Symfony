FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN echo "xdebug.mode=debug_mode=debug_mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client=xdebug_client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=xdebug_start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html