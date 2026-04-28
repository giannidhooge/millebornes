FROM php:8.5.5-fpm

RUN apt-get update && apt-get install -y \
      curl \
      libzip-dev \
      libfontconfig \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
      libmcrypt-dev \
      libpng-dev \
      libgbm-dev \
      libxrender1 \
      expect && \
  pecl install ast && \
  docker-php-ext-install -j$(nproc) gd && \
  docker-php-ext-install zip && \
  docker-php-ext-install exif

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ci/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www
