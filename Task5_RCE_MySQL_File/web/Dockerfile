FROM php:7.4-apache

RUN a2enmod rewrite && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    apt-get update && \
    apt-get install -y curl wget && \
    rm -rf /var/lib/apt/lists/* && \
    mkdir -p /var/www/html/uploads && \
    chmod 777 /var/www/html/uploads

WORKDIR /var/www/html

COPY index.php /var/www/html/index.php

EXPOSE 80

CMD ["apache2ctl", "-D", "FOREGROUND"]
