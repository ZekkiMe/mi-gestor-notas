FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN docker-php-ext-install pdo_mysql

COPY src/ .

EXPOSE 9000

CMD ["php-fpm"]