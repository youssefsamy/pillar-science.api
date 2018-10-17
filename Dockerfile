FROM php:fpm-alpine
MAINTAINER Mathieu Tanguay <mathieu@rdata.online>
ENV COMPOSER_HOME /app

RUN adduser -D -H -u 1000 -s /bin/bash nginx

# Install extra modules here
RUN apk update \
  && apk add zlib-dev \
  && /usr/local/bin/docker-php-ext-install pdo pdo_mysql zip

WORKDIR /app

ADD . /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
    && composer install