FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl git unzip libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath exif gd intl opcache pdo_mysql zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/hospital-entrypoint
RUN chmod +x /usr/local/bin/hospital-entrypoint
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --optimize-autoloader

COPY package.json package-lock.json* ./
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN if [ -f package-lock.json ]; then \
        curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
        apt-get update && apt-get install -y --no-install-recommends nodejs && \
        npm ci && npm run build && rm -rf node_modules /var/lib/apt/lists/*; \
    fi

COPY . .
RUN DB_CONNECTION=sqlite DB_DATABASE=:memory: composer dump-autoload --no-dev --optimize \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
ENTRYPOINT ["hospital-entrypoint"]
