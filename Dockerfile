# 1. Use the official PHP Apache image
FROM php:8.2-apache

# 2. Install the mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# 3. Copy your project files into the server
COPY . /var/www/html/

# 4. Enable Apache mod_rewrite for your .htaccess
RUN a2enmod rewrite

# 5. Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html
