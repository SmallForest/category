FROM php:7.4.19-fpm

RUN  sed -i s@/deb.debian.org/@/mirrors.aliyun.com/@g /etc/apt/sources.list
RUN  apt-get clean
RUN apt update
RUN  apt -y upgrade

RUN pecl install yaf
RUN docker-php-ext-enable yaf

RUN pecl install redis
RUN docker-php-ext-enable redis

RUN apt install -y libzip-dev

RUN pecl install zip
RUN docker-php-ext-enable zip

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mysqli
RUN docker-php-ext-install bcmath
RUN apt install -y libpng-dev
RUN docker-php-ext-install -j$(nproc) gd


RUN apt-get install -y wget
# 安装composer
RUN cd /tmp && wget https://install.phpcomposer.com/installer && mv installer a.php && php a.php && rm a.php && mv composer.phar /usr/local/bin/composer

