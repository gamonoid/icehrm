# Build stage - compile assets
FROM node:18-alpine AS builder

WORKDIR /build

# obfuscate-js.sh is a bash script (process substitution), not POSIX sh
RUN apk add --no-cache bash

# Copy package files
COPY package.json package-lock.json* ./
COPY web/package.json web/package-lock.json* ./web/
COPY gulpfile.js ./
COPY obfuscate-js.sh ./

# Copy all source files needed for build
COPY index.php ./
COPY web/ ./web/
COPY app/ ./app/
COPY core/ ./core/
COPY extensions/ ./extensions/
COPY docker/ ./docker/

# Install dependencies and build assets
RUN npm install && cd web && npm install && cd ..
RUN npm run asset:build:prod

# Drop the toolchain now that the bundles are built. The production stage copies
# /build/web wholesale, so anything left here ships in the final image.
# web/dist (the built output) and web/bower_components (served at runtime, and
# the source of the tinymce/simplemde/flag-icon assets) are deliberately kept.
RUN rm -rf node_modules web/node_modules \
    && find extensions -type d -name node_modules -prune -exec rm -rf {} +

# Verify files exist
RUN ls -la /build/ && ls -la /build/app/

# Production stage
FROM alpine:3.20
LABEL Maintainer="Thilina Pituwala <thilina@icehrm.com>" \
      Description="IceHrm Production Container with Nginx & PHP-FPM based on Alpine Linux."

# Install packages (no dev dependencies, no xdebug)
RUN apk --no-cache add \
    php83 php83-fpm php83-opcache php83-mysqli php83-json php83-openssl php83-curl \
    php83-zlib php83-xml php83-phar php83-intl php83-dom php83-simplexml php83-xmlreader \
    php83-ctype php83-session php83-mbstring php83-gd php83-tokenizer php83-zip php83-iconv \
    nginx supervisor curl

# Create symlink for php command (if not exists)
RUN ln -sf /usr/bin/php83 /usr/bin/php

# Configure nginx
COPY --from=builder /build/docker/prod/config/nginx.conf /etc/nginx/nginx.conf

# Configure PHP-FPM
COPY --from=builder /build/docker/prod/config/fpm-pool.conf /etc/php83/php-fpm.d/www.conf
COPY --from=builder /build/docker/prod/config/php.ini /etc/php83/conf.d/custom.ini

# Configure supervisord
COPY --from=builder /build/docker/prod/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup document root
RUN mkdir -p /var/www/html

# Copy application code from builder (with compiled assets)
COPY --from=builder --chown=nobody:nobody /build/index.php /var/www/html/index.php
COPY --from=builder --chown=nobody:nobody /build/app /var/www/html/app/
COPY --from=builder --chown=nobody:nobody /build/core /var/www/html/core/
COPY --from=builder --chown=nobody:nobody /build/extensions /var/www/html/extensions/
COPY --from=builder --chown=nobody:nobody /build/web /var/www/html/web/

# Copy production config (overwrites the one from app/)
COPY --from=builder --chown=nobody:nobody /build/docker/prod/config/config.php /var/www/html/app/config.php

# Verify files were copied
RUN ls -la /var/www/html/ && ls -la /var/www/html/app/

# Create data directory for uploads and logs. .dockerignore keeps app/data out of
# the build context, so this is an empty directory and a non-recursive chown is
# enough. A `chown -R` here would rewrite every file underneath into a new layer.
RUN mkdir -p /var/www/html/app/data && \
    chown nobody:nobody /var/www/html/app/data

# Make sure files/folders needed by the processes are accessible when they run under the nobody user
RUN chown -R nobody:nobody /run && \
    chown -R nobody:nobody /var/lib/nginx && \
    chown -R nobody:nobody /var/log/nginx

# Add application
WORKDIR /var/www/html

# Expose the port nginx is reachable on
EXPOSE 8080

# Switch to use a non-root user from here on
USER nobody

# Let supervisord start nginx & php-fpm
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Configure a healthcheck to validate that everything is up & running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/app/health.php || exit 1
