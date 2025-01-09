FROM php:8.1-apache

RUN apt update \
    && apt install -y zip git libzip-dev libcurl3-dev libssl-dev telnet

ENV APACHE_DOCUMENT_ROOT /app/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Add ssh keys from secrets
RUN mkdir /root/.ssh
RUN ln -s /run/secrets/ssh_key /root/.ssh/id_rsa
RUN ln -s /run/secrets/gitconfig /root/.gitconfig
