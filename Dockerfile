FROM webdevops/php-nginx:8.2
ENV WEB_DOCUMENT_ROOT /app/public
ENV SERVICE_NGINX_CLIENT_MAX_BODY_SIZE 100m
ENV FPM_PM_MAX_CHILDREN 20
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
COPY --chown=application:application composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts
COPY --chown=application:application . .
RUN mv start.sh /opt/docker/provision/entrypoint.d/
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY user.ini $PHP_INI_DIR/conf.d/
RUN composer install -o --no-dev
RUN php artisan optimize
EXPOSE 80
