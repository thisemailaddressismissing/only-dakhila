# Use official PHP 8.3 Apache image
FROM php:8.3-apache

# Install system dependencies & PostgreSQL dev libraries
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql pgsql pdo_mysql mysqli zip \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite for clean extensionless URLs
RUN a2enmod rewrite

# Allow .htaccess overrides in Apache
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Set Working Directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set permissions for web server
RUN chown -R www-data:www-data /var/www/html

# Expose default HTTP port
EXPOSE 80

# Start Apache server in foreground
CMD ["apache2-foreground"]
