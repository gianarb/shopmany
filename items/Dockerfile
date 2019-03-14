FROM php:7.2-apache

RUN a2enmod rewrite
RUN docker-php-ext-install pdo_mysql

RUN find /etc/apache2/sites-enabled/* -exec sed -i 's/#*[Cc]ustom[Ll]og/#CustomLog/g' {} \;
RUN find /etc/apache2/sites-enabled/* -exec sed -i 's/#*[Ee]rror[Ll]og/#ErrorLog/g' {} \;
RUN a2disconf other-vhosts-access-log

