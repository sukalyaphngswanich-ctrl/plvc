# Dockerfile for PLVC Internship Management System on Render
FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Configure PHP settings
RUN echo "upload_max_filesize = 20M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 25M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "date.timezone = Asia/Bangkok" >> /usr/local/etc/php/conf.d/uploads.ini

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose HTTP port (Render uses PORT env variable, Apache defaults to 80 or we can bind)
EXPOSE 80

CMD ["apache2-foreground"]
