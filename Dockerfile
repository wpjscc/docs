ARG PHP_VERSION="wpjscc/php:8.0.9-fpm-alpine3.13"
FROM ${PHP_VERSION}

COPY  . /var/www/html

WORKDIR /var/www/html

RUN composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

RUN rm -rf /var/www/html/docker && composer install --ignore-platform-reqs --no-dev --no-interaction -o -vvv

# RUN composer install -vvv
