FROM php:8.2-apache

# Install necessary PHP extensions and utilities
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install redis \
    && docker-php-ext-enable redis

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

# Set working directory inside the container
WORKDIR /var/www/html

# Copy Apache configuration
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Copy Laravel code into the container
COPY . .

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Generate application key
RUN php artisan key:generate

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
