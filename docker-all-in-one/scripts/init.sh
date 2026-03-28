#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Default values
MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD:-icehrm_root}
MYSQL_DATABASE=${MYSQL_DATABASE:-icehrm}
MYSQL_USER=${MYSQL_USER:-icehrm}
MYSQL_PASSWORD=${MYSQL_PASSWORD:-icehrm}

# Create log directory
mkdir -p /var/log/icehrm
touch /var/log/icehrm/php-error.log
touch /var/log/icehrm/cron.log
chown -R nobody:nobody /var/log/icehrm

# Initialize MySQL data directory if empty
if [ ! -d "/var/lib/mysql/mysql" ]; then
    log_info "Initializing MySQL data directory..."

    # Initialize MySQL
    mysql_install_db --user=mysql --datadir=/var/lib/mysql > /dev/null 2>&1

    log_info "Starting MySQL for initial setup..."

    # Start MySQL temporarily
    /usr/bin/mysqld --user=mysql --datadir=/var/lib/mysql &
    MYSQL_PID=$!

    # Wait for MySQL to start
    log_info "Waiting for MySQL to be ready..."
    for i in {1..30}; do
        if mysqladmin ping -h localhost --silent 2>/dev/null; then
            break
        fi
        sleep 1
    done

    if ! mysqladmin ping -h localhost --silent 2>/dev/null; then
        log_error "MySQL failed to start!"
        exit 1
    fi

    log_info "MySQL is ready. Setting up database..."

    # Secure MySQL and create database/user
    mysql -u root <<-EOSQL
        -- Set root password
        ALTER USER 'root'@'localhost' IDENTIFIED BY '${MYSQL_ROOT_PASSWORD}';

        -- Create database
        CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

        -- Create user and grant privileges
        CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'localhost' IDENTIFIED BY '${MYSQL_PASSWORD}';
        CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'127.0.0.1' IDENTIFIED BY '${MYSQL_PASSWORD}';
        GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'localhost';
        GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_USER}'@'127.0.0.1';

        FLUSH PRIVILEGES;
EOSQL

    log_info "Database '${MYSQL_DATABASE}' created with user '${MYSQL_USER}'"

    # Import initial schema if exists
    if [ -f "/docker-entrypoint-initdb.d/init.sql" ]; then
        log_info "Importing initial database schema..."
        mysql -u "${MYSQL_USER}" -p"${MYSQL_PASSWORD}" "${MYSQL_DATABASE}" < /docker-entrypoint-initdb.d/init.sql
        log_info "Database schema imported successfully"
    fi

    # Stop temporary MySQL
    log_info "Stopping temporary MySQL instance..."
    kill $MYSQL_PID
    wait $MYSQL_PID 2>/dev/null || true

    log_info "MySQL initialization complete"
else
    log_info "MySQL data directory already exists, skipping initialization"
fi

# Ensure proper permissions
chown -R mysql:mysql /var/lib/mysql /run/mysqld /var/log/mysql

# Create app data directory if it doesn't exist
mkdir -p /var/www/html/app/data
chown -R nobody:nobody /var/www/html/app/data

log_info "Starting all services via Supervisord..."
log_info "============================================"
log_info "IceHrm All-in-One Container"
log_info "============================================"
log_info "Web URL: http://localhost:5566"
log_info "Default login: admin / admin"
log_info "============================================"

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
