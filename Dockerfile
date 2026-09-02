# Production Dockerfile for PLVC Internship Management System on Render
FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install PDO extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Configure Apache & PHP settings
RUN echo "upload_max_filesize = 25M" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size = 30M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "date.timezone = Asia/Bangkok" >> /usr/local/etc/php/conf.d/custom.ini

# Allow .htaccess and directory index in Apache
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Ensure write permissions for database and upload folders
RUN mkdir -p /var/www/html/database /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html/database \
    && chmod -R 777 /var/www/html/uploads

# Create entrypoint script for dynamic $PORT binding on Render
RUN printf '#!/bin/bash\nTARGET_PORT="${PORT:-80}"\nsed -i "s/Listen 80/Listen $TARGET_PORT/" /etc/apache2/ports.conf\nsed -i "s/<VirtualHost \\*:80>/<VirtualHost \\*:$TARGET_PORT>/" /etc/apache2/sites-available/000-default.conf\nexec apache2-foreground\n' > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

CMD ["/usr/local/bin/entrypoint.sh"]
