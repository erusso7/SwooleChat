FROM php:cli

RUN pecl install swoole --enable-sockets \
    && pecl install redis \
    && echo  "extension=swoole.so" > /usr/local/etc/php/conf.d/swoole.ini \
    && echo  "extension=redis.so" > /usr/local/etc/php/conf.d/redis.ini

ADD . /var/www

WORKDIR /var/www
