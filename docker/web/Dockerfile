FROM php:8.2-apache

COPY apache/apache2.conf /etc/apache2/apache2.conf
COPY apache/vhost.conf /etc/apache2/sites-enabled/000-default.conf
COPY apache/ssl/server.crt /etc/apache2/ssl/server.crt
COPY apache/ssl/server.key /etc/apache2/ssl/server.key

RUN apt-get update && apt-get install -y git unzip zip

WORKDIR /var/www/html

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd bcmath zip intl opcache exif

# xdebug
#RUN install-php-extensions xdebug

# composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# apache
RUN a2enmod rewrite
RUN a2enmod proxy
RUN a2enmod proxy_http
RUN a2enmod ssl
RUN service apache2 restart