FROM mcr.microsoft.com/mssql/server:2019-latest AS mssql

FROM php:7.4-cli

RUN apt-get update && \
    apt-get install -y \
    apache2 \
    gcc \
    make \
    autoconf \
    curl \
    libcurl4-openssl-dev \
    libssl-dev \
    sqlite3 \
    libsqlite3-dev && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-install pdo_sqlite

# Install FreeTDS and pdo_dblib driver for MSSQL connection
RUN apt-get update && \
    apt-get install -y freetds-dev freetds-bin \
    gnupg2 curl apt-transport-https && \
    docker-php-ext-configure pdo_dblib --with-libdir=lib/x86_64-linux-gnu && \
    docker-php-ext-install pdo_dblib && \
    rm -rf /var/lib/apt/lists/*

# Enable Apache modules and install PDO extension
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY web_app/ /var/www/html/

EXPOSE 80

CMD ["apache2ctl", "-D", "FOREGROUND"]
