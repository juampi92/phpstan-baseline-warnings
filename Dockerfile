FROM php:8.2-cli-alpine

COPY . /app
WORKDIR /app

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install --no-dev --no-progress --no-interaction

# Create symlink for the binary
RUN ln -s /app/vendor/bin/phpstan-baseline-warnings /usr/local/bin/phpstan-baseline-warnings

ENTRYPOINT ["phpstan-baseline-warnings"]
