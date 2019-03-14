FROM php:7.2-apache

RUN a2enmod rewrite
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install bcmath

RUN find /etc/apache2/sites-enabled/* -exec sed -i 's/#*[Cc]ustom[Ll]og/#CustomLog/g' {} \;
RUN find /etc/apache2/sites-enabled/* -exec sed -i 's/#*[Ee]rror[Ll]og/#ErrorLog/g' {} \;
RUN a2disconf other-vhosts-access-log

RUN docker-php-ext-install sockets
RUN pecl install opencensus-alpha
RUN docker-php-ext-enable opencensus
RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip unzip

RUN apt-get install -y libgmp-dev re2c libmhash-dev libmcrypt-dev file
RUN ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/local/include/
RUN docker-php-ext-configure gmp
RUN docker-php-ext-install gmp

RUN curl --silent --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /usr/bin/composer
