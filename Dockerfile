FROM php:8.1-apache

LABEL description="Locus - Sistema de gestion de asistencias con QR"
LABEL version="1.0"

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install PHP extensions for MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Configure Apache to use public/ as document root, allow .htaccess, and pass Authorization header
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf \
    && echo 'SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1' >> /etc/apache2/apache2.conf

# Ensure Apache can write to the directory
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Set environment variables (can be overridden at runtime)
ENV DB_HOST=db \
    DB_PORT=3306 \
    DB_NAME=locus \
    DB_USER=locus \
    DB_PASS=locus_pass \
    JWT_SECRET=change-this-secret-key-in-production

EXPOSE 80

CMD ["apache2-foreground"]
