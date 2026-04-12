FROM php:7.4-apache

RUN a2enmod rewrite && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    apt-get update && \
    apt-get install -y freetds-dev && \
    docker-php-ext-configure pdo_dblib --with-libdir=lib/x86_64-linux-gnu && \
    docker-php-ext-install pdo_dblib && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY index.php /var/www/html/index.php

EXPOSE 80
