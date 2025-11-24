FROM php:8.3-apache

RUN docker-php-ext-install mysqli pdo_mysql

RUN a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/uploads

EXPOSE 80

CMD ["apache2-foreground"]
