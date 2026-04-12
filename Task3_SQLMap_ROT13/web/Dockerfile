FROM php:7.4-apache

RUN a2enmod rewrite && \
    docker-php-ext-install mysqli && \
    apt-get update && \
    apt-get install -y git && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY rot13_vuln.php /var/www/html/index.php

EXPOSE 80
