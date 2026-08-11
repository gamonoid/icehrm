# Installation using Docker

## Prerequisites

Install Docker on your system. See [Docker Installation Guide](docker-installation.md) for detailed instructions for macOS, Windows, and Linux.

## Quick Start (Production)

```bash
# Clone the repository
git clone https://github.com/gamonoid/icehrm.git
cd icehrm

# Build and start IceHrm
docker compose -f docker-compose.yaml up -d --build
```

Visit [http://localhost:5555](http://localhost:5555) and login with username `admin` and password `admin`.

**Note:** The Docker build automatically runs `npm install` and `npm run asset:build:prod` to compile frontend assets. The first build may take a few minutes.

## Configuration (Optional)

To customize your installation, create a `.env` file:

```bash
cp docker-prod.env.example .env
```

Edit `.env` to change settings:

```env
# Port where IceHrm will be accessible
APP_PORT=5555

# Base URL (change for production domain)
APP_BASE_URL=http://localhost:5555

# Database credentials (change these in production!)
DB_HOST=mysql
DB_NAME=icehrm
DB_USER=icehrm
DB_PASSWORD=your_secure_password
DB_ROOT_PASSWORD=your_secure_root_password
```

Then restart the containers:

```bash
docker compose -f docker-compose.yaml down
docker compose -f docker-compose.yaml up -d
```

## Using an External Database

To connect IceHrm to an external MySQL database (e.g., AWS RDS, Azure Database, or your own MySQL server):

1. Create a `.env` file with your external database settings:

```env
APP_PORT=5555
APP_BASE_URL=http://localhost:5555

# External database configuration
DB_HOST=your-database-host.example.com
DB_NAME=icehrm
DB_USER=your_db_user
DB_PASSWORD=your_db_password
```

2. Import the database schema to your external database:

```bash
mysql -h your-database-host.example.com -u your_db_user -p icehrm < docker/init.sql
```

3. Start only the application containers (without the bundled MySQL):

```bash
docker compose -f docker-compose.yaml up -d icehrm icehrm-worker
```

## Docker Services

| Service | Description | Port |
|---------|-------------|------|
| icehrm | Main application | 5555 |
| mysql | Database server | - |
| icehrm-worker | Background jobs | - |

## Data Persistence

All data is persisted in Docker volumes:
- `icehrm-mysql-data` - Database files
- `icehrm-app-data` - Uploaded files and logs

## Stopping IceHrm

```bash
docker compose -f docker-compose.yaml down
```

## Viewing Logs

```bash
docker compose -f docker-compose.yaml logs -f
```
