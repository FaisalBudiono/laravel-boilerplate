FROM webdevops/php-nginx:8.3 AS base
ENV WEB_DOCUMENT_ROOT=/app/public
ENV SERVICE_NGINX_CLIENT_MAX_BODY_SIZE=100m
ENV FPM_PM_MAX_CHILDREN=20
WORKDIR /app
RUN apt-get update && apt-get install -y \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libgmp-dev \
    re2c \
    libmhash-dev \
    libmcrypt-dev \
    file \
    libxrender1 \
    libfontconfig1 \
    libfreetype6

FROM base AS dev
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY user.ini $PHP_INI_DIR/conf.d/
RUN composer global require spatie/phpunit-watcher \
    && composer global require phpstan/phpstan \
    && echo 'export PATH="$PATH:$HOME/.composer/vendor/bin"' >> ~/.bashrc
RUN mkdir -p /custom-config
COPY .dev/.bashrc /custom-config/.bashrc
RUN cat /custom-config/.bashrc >> ~/.bashrc
EXPOSE 80

FROM dev AS dev-test
RUN pecl install pcov \
    && echo "extension=pcov.so" >> /usr/local/etc/php/php.ini
RUN cp /usr/local/etc/php/php.ini /usr/local/etc/php/php.ini-backup \
    && sed -E 's/(memory_limit).+/\1 = -1/' /usr/local/etc/php/php.ini-backup > /usr/local/etc/php/php.ini \
    && rm /usr/local/etc/php/php.ini-backup
ENTRYPOINT []
CMD php artisan test --parallel --coverage --coverage-text

FROM base AS api
COPY --chown=application:application composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts
COPY start.sh /opt/docker/provision/entrypoint.d/
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY user.ini $PHP_INI_DIR/conf.d/
COPY --chown=application:application . .
RUN composer install -o --no-dev
RUN php artisan optimize
EXPOSE 80
