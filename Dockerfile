FROM php:8.2-cli

WORKDIR /app

# Install dependency system
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
    && docker-php-ext-install zip mysqli pdo pdo_mysql

# Copy project
COPY . .

# Railway pakai PORT dynamic
ENV PORT=8080

# Run PHP built-in server dari app/public
CMD php -S 0.0.0.0:$PORT -t app/public