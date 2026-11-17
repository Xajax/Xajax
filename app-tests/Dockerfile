# Test image for the app integration harnesses (see README.md).
#
# php:8.2-apache plus the extensions these old applications expect, with this
# branch's xajax staged at /src so the setup scripts can swap it into each app.
#
# Build from the repository root:
#   docker build -t xajax-scratch:latest -f app-tests/Dockerfile .
FROM php:8.2-apache
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y --no-install-recommends \
        mariadb-server libonig-dev libxml2-dev libzip-dev libpng-dev \
    && docker-php-ext-install mysqli mbstring xml pdo_mysql gd \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
RUN { \
        echo 'display_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'error_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING'; \
        echo 'mysqli.default_socket = /run/mysqld/mysqld.sock'; \
        echo 'pdo_mysql.default_socket = /run/mysqld/mysqld.sock'; \
        echo 'output_buffering = 1048576'; \
    } > /usr/local/etc/php/conf.d/zz-app-tests.ini
COPY xajax_core/ /src/xajax_core/
COPY xajax_js/ /src/xajax_js/
COPY xajax_plugins/ /src/xajax_plugins/
COPY copyright.inc.php /src/copyright.inc.php
COPY app-tests/fix_ds.php /fix_ds.php
