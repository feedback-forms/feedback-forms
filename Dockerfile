# Build stage
FROM php:8.4-fpm-alpine AS builder

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    nodejs \
    npm \
    git \
    zip \
    unzip \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql zip opcache

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy composer files first for better caching
COPY composer.* ./
COPY package*.json ./

# Create required directories
RUN mkdir -p resources/svg

# Install PHP dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Install Node dependencies
RUN npm ci

# Copy application files
COPY . .

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Build frontend assets
RUN npm run build

# Production stage
FROM php:8.4-fpm-alpine

# Install production dependencies
RUN apk add --no-cache postgresql-dev
RUN docker-php-ext-install pdo pdo_pgsql opcache

WORKDIR /app

# Copy application from builder
COPY --from=builder /app /app

# Set proper permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Add health check
HEALTHCHECK --interval=30s --timeout=3s \
    CMD php artisan health || exit 1

EXPOSE 9000
CMD ["php-fpm"]