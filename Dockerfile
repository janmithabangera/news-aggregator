# Use the official PHP 8.3 image
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application code
COPY . .

# Install application dependencies
RUN composer install

# Set appropriate permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]

# Set PHP configuration for memory and execution time
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory-limit.ini
RUN echo "max_execution_time = 300" > /usr/local/etc/php/conf.d/execution-time.ini

# Assuming you have already installed dependencies in previous steps
RUN php artisan route:cache
RUN php artisan config:cache

# In Dockerfile
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

