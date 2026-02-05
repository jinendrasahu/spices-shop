# Use official WordPress image based on PHP
FROM wordpress:latest

# Install additional PHP extensions that might be needed
RUN docker-php-ext-install mysqli pdo pdo_mysql

WORKDIR /var/www/html

EXPOSE 80
