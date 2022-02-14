FROM php:7.4-fpm

    RUN apt-get update && apt-get install -y \
           openssl \
           git \
           unzip \
           libpng-dev \
           libxml2-dev \
           libzip-dev \
           libcurl4-gnutls-dev \
           libonig-dev \
           libpq-dev \
           fontconfig \
           libfreetype6 \
           libjpeg62-turbo \
           libxrender1 \
           xfonts-75dpi \
           xfonts-base \
           wget
   RUN docker-php-ext-install pdo pdo_pgsql pgsql opcache zip curl json mbstring xml intl



   RUN pecl install apcu
    
   RUN apt-get update && \
   apt-get install -y \
   libzip-dev
    
   RUN docker-php-ext-install zip
   RUN docker-php-ext-enable apcu
    
   WORKDIR /usr/src/app
    
   COPY --chown=1000:1000 ./ /usr/src/app
    
   RUN PATH=$PATH:/usr/src/app/vendor/bin:bin

   COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

