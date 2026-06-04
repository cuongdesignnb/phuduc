FROM php:8.2-cli

# System deps
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl zip unzip libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libwebp-dev libzip-dev libonig-dev libxml2-dev sqlite3 libsqlite3-dev \
    ca-certificates gnupg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Node.js 20 LTS
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# PHP deps first (cache layer)
COPY composer.json composer.lock* ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Node deps (cache layer)
COPY package.json package-lock.json* ./
RUN npm install --legacy-peer-deps

# Copy full source
COPY . .

# Finish composer
RUN composer dump-autoload --optimize

# Storage & cache permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8741 5179

ENTRYPOINT ["docker-entrypoint.sh"]
