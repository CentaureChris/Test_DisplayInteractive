FROM php:8.3-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-interaction --prefer-dist --no-progress --optimize-autoloader

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
