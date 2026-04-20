# 1. Use the official PHP Apache image
FROM php:8.2-apache

# 2. Install the mysqli extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# 3. Enable Apache mod_rewrite for your .htaccess
RUN a2enmod rewrite

# 4. CRITICAL FIX: Allow .htaccess to override Apache defaults
# This tells Apache to actually read the routing rules in your .htaccess file
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 5. Copy your project files into the server
COPY . /var/www/html/

# 6. Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html
