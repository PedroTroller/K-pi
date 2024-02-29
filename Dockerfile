FROM composer:2.7.1 AS composer

WORKDIR /K-pi

COPY composer.* ./

RUN composer install --ignore-platform-reqs --no-scripts --no-plugins

##################################

FROM php:8.3.0-cli-alpine3.19

WORKDIR /K-pi

COPY                 .            .
COPY --from=composer /K-pi/vendor /K-pi/vendor
