FROM php:8.3-apache
RUN docker-php-ext-install mysqli pdo_mysql
COPY apache-nomada.conf /etc/apache2/conf-available/nomada.conf
RUN a2enconf nomada
