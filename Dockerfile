FROM php:8.0-apache
# Cài đặt extension để PHP kết nối được MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql