FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Instalar extensiones de PHP necesarias
RUN docker-php-ext-install pdo_mysql

# Copiar los archivos de la aplicación
COPY src/ .

# Exponer el puerto 9000 para PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]