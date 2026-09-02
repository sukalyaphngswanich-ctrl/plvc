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

# Configure PHP settings
RUN echo "upload_max_filesize = 25M" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size = 30M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "date.timezone = Asia/Bangkok" >> /usr/local/etc/php/conf.d/custom.ini

# Ensure write permissions for database and upload folders
RUN mkdir -p /var/www/html/database /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html/database \
    && chmod -R 777 /var/www/html/uploads

# Create entrypoint script to dynamically configure port at runtime
RUN printf '#!/bin/bash\nPORT="${PORT:-80}"\nsed -i "s/Listen [0-9]*/Listen $PORT/g" /etc/apache2/ports.conf\nsed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:$PORT>/g" /etc/apache2/sites-available/000-default.conf\nexec apache2-foreground\n' > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

CMD ["/usr/local/bin/entrypoint.sh"]
