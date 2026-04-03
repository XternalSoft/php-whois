FROM php:8.2-cli

RUN apt update && apt install -y \
    git \
    zip \
    unzip \
    libicu-dev \
    libcurl4-openssl-dev \
    libonig-dev

RUN docker-php-ext-install intl mbstring curl

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN php -v

CMD ["sleep", "infinity"]