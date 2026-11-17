FROM php:8.2-apache

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# MariaDB server + the PHP extensions ONA needs (mbstring, mysqli, xml, gmp, yaml)
RUN apt-get update && apt-get install -y --no-install-recommends \
        mariadb-server \
        libyaml-dev libonig-dev libgmp-dev libxml2-dev \
        sendemail jq curl git unzip \
    && docker-php-ext-install mysqli mbstring xml gmp \
    && pecl install yaml \
    && docker-php-ext-enable yaml \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Point PHP's mysqli/pdo at the MariaDB socket. Log all diagnostics to a file
# (instead of the page) so the 2007-era UI stays clean for preview while
# deprecations/notices remain available for inspection.
RUN { \
      echo 'mysqli.default_socket = /run/mysqld/mysqld.sock'; \
      echo 'pdo_mysql.default_socket = /run/mysqld/mysqld.sock'; \
      echo 'display_errors = Off'; \
      echo 'log_errors = On'; \
      echo 'error_log = /var/log/php_errors.log'; \
      echo 'error_reporting = E_ALL'; \
    } > /usr/local/etc/php/conf.d/ona.ini \
    && touch /var/log/php_errors.log && chmod 666 /var/log/php_errors.log

# Fetch the real MVC app: OpenNetAdmin (ONA) v19.0.3-era, which bundles its own
# modified 2007-era xajax. Pinned to a commit SHA for reproducibility.
ARG ONA_COMMIT=75305184bc4e14bd064da250dd5d70c47b53ae4b
RUN mkdir -p /opt/ona \
    && curl -sL "https://api.github.com/repos/opennetadmin/ona/tarball/${ONA_COMMIT}" \
      | tar xz -C /opt/ona --strip-components=1 -f - \
    && rm -rf /opt/ona/.git

# ONA lives at /opt/ona; serve it under /ona via an Alias (cleaner than a
# docroot symlink, which Apache refuses to follow across the docroot).
RUN rm -f /var/www/html/index.html

RUN echo '/opt/ona' > /etc/onabase \
    && touch /var/log/ona.log && chmod 666 /var/log/ona.log \
    && mkdir -p /opt/ona/www/local/config && chown -R www-data:www-data /opt/ona/www/local

RUN printf 'Alias /ona /opt/ona/www\n<Directory /opt/ona/www>\n    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
      > /etc/apache2/conf-available/ona.conf \
    && a2enconf ona

# Swap ONA's bundled (2007-era) xajax for the PHP 8.2-compatible build from this
# repository. ONA's own xajax integration wrappers (xajax_drag, xajax_suggest,
# xajax_webwin) and include/xajax_setup.inc.php live outside include/xajax/ and
# are left untouched.
RUN rm -rf /opt/ona/www/include/xajax
COPY xajax_core/ /opt/ona/www/include/xajax/xajax_core/
COPY xajax_js/ /opt/ona/www/include/xajax/xajax_js/
COPY xajax_plugins/ /opt/ona/www/include/xajax/xajax_plugins/
COPY copyright.inc.php /opt/ona/www/include/xajax/copyright.inc.php

# The preview proxy terminates TLS and forwards plain HTTP, so PHP sees no
# HTTPS and SERVER_PORT=80; xajax would then build an http:// requestURI that
# the browser blocks as mixed content. Reconstruct the public origin from the
# forwarded headers before the app runs.
COPY ona-docker/proxy_shim.php /usr/local/etc/php/proxy_shim.php
RUN echo 'auto_prepend_file = /usr/local/etc/php/proxy_shim.php' \
      > /usr/local/etc/php/conf.d/zz-proxy-shim.ini

COPY ona-docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
