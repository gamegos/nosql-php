# syntax=docker/dockerfile:1
FROM php:8.4-cli AS test

# Install system dependencies
RUN --mount=type=cache,target=/var/cache/apt \
    --mount=type=cache,target=/var/lib/apt/lists \
    apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        libxml2-dev \
        libzip-dev \
        unzip \
        zip && \
    rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN --mount=type=cache,target=/tmp/pear \
    docker-php-ext-install zip dom xml

# Install Composer
RUN --mount=type=cache,target=/tmp/composer-install \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Xdebug for code coverage
RUN --mount=type=cache,target=/tmp/pear \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    echo "xdebug.mode=coverage,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

ENV COMPOSER_DISABLE_XDEBUG_WARN=1

# Create app user
RUN useradd -m -u 1000 -s /bin/bash appuser

# Switch to app user for composer operations
USER appuser

# ----------------------#
# Stage: vendor-install #
# ----------------------#
FROM test AS vendor-install

# Set working directory and permissions
WORKDIR /app
RUN chown appuser:appuser /app

# Copy composer files
COPY --chown=appuser:appuser composer.* ./

# Install composer dependencies
RUN --mount=type=ssh,uid=1000 \
    --mount=type=cache,target=/home/appuser/.composer/cache \
    composer install --no-interaction --no-scripts --no-cache --prefer-dist

#----------------------#
# Stage: vendor-export #
#----------------------#
FROM scratch AS vendor-export
COPY --from=vendor-install /app/vendor /vendor
COPY --from=vendor-install /app/composer.lock /composer.lock
