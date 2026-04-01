# IceHrm All-in-One Docker Image
# Combines: Nginx + PHP-FPM + MySQL + Cron Worker
#
# This image is suitable for:
# - Small deployments
# - Demos and testing
# - Platforms that only support single containers
#
# For production with high availability, use the separate services instead.

# Build stage - compile assets
FROM node:18-alpine AS builder

WORKDIR /build

# Copy package files
COPY package.json package-lock.json* ./
COPY web/package.json web/package-lock.json* ./web/
COPY gulpfile.js ./

# Copy all source files needed for build
COPY index.php ./
COPY web/ ./web/
COPY app/ ./app/
COPY core/ ./core/
COPY extensions/ ./extensions/

# Install dependencies and build assets
RUN npm install && cd web && npm install && cd ..
RUN npm run asset:build:prod

# Production stage - All-in-One
FROM alpine:3.17.1
LABEL maintainer="Thilina Pituwala <thilina@icehrm.com>" \
      description="IceHrm All-in-One Container with Nginx, PHP-FPM, MySQL, and Cron Worker" \
      org.opencontainers.image.title="IceHrm Open Source" \
      org.opencontainers.image.description="Open source Human Resource Management System" \
      org.opencontainers.image.url="https://icehrm.com" \
      org.opencontainers.image.source="https://github.com/gamonoid/icehrm" \
      org.opencontainers.image.vendor="IceHrm" \
      org.opencontainers.image.documentation="https://github.com/gamonoid/icehrm/blob/master/docker-all-in-one/README.md"

# Install all required packages
RUN apk --no-cache add \
    # PHP and extensions
    php81 php81-fpm php81-opcache php81-mysqli php81-json php81-openssl php81-curl \
    php81-zlib php81-xml php81-phar php81-intl php81-dom php81-simplexml php81-xmlreader \
    php81-ctype php81-session php81-mbstring php81-gd php81-tokenizer php81-zip php81-iconv \
    # Web server
    nginx \
    # Process manager
    supervisor \
    # MySQL
    mysql mysql-client \
    # Utilities
    curl bash

# Create symlink for php command
RUN ln -sf /usr/bin/php81 /usr/bin/php

# Create required directories
RUN mkdir -p /var/www/html \
    /run/nginx \
    /run/mysqld \
    /var/lib/mysql \
    /var/log/mysql \
    /var/log/icehrm

# Set permissions for MySQL
RUN chown -R mysql:mysql /var/lib/mysql /run/mysqld /var/log/mysql

# Copy configuration files
COPY docker-all-in-one/config/nginx.conf /etc/nginx/nginx.conf
COPY docker-all-in-one/config/fpm-pool.conf /etc/php81/php-fpm.d/www.conf
COPY docker-all-in-one/config/php.ini /etc/php81/conf.d/custom.ini
COPY docker-all-in-one/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Remove default MariaDB config that disables networking, then add our config
RUN rm -f /etc/my.cnf.d/mariadb-server.cnf
COPY docker-all-in-one/config/my.cnf /etc/my.cnf.d/icehrm.cnf

# Copy initialization script
COPY docker-all-in-one/scripts/init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

# Copy application code from builder
COPY --from=builder /build/index.php /var/www/html/index.php
COPY --from=builder /build/app /var/www/html/app/
COPY --from=builder /build/core /var/www/html/core/
COPY --from=builder /build/extensions /var/www/html/extensions/
COPY --from=builder /build/web /var/www/html/web/

# Note: config.php is generated at runtime by init.sh from environment variables

# Copy database initialization script
COPY docker/init.sql /docker-entrypoint-initdb.d/init.sql

# Copy cron configuration
COPY docker-all-in-one/config/ice-cron /etc/crontabs/root

# Create data directory
RUN mkdir -p /var/www/html/app/data && \
    chown -R nobody:nobody /var/www/html/app/data

# Set permissions
RUN chown -R nobody:nobody /var/www/html && \
    chown -R nobody:nobody /run/nginx && \
    chown -R nobody:nobody /var/lib/nginx && \
    chown -R nobody:nobody /var/log/nginx

WORKDIR /var/www/html

# Expose web port
EXPOSE 8080

# Volumes for data persistence
VOLUME ["/var/lib/mysql", "/var/www/html/app/data"]

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s \
    CMD curl --silent --fail http://127.0.0.1:8080/app/health.php || exit 1

# Start all services via init script
CMD ["/usr/local/bin/init.sh"]
