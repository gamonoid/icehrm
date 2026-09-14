# Installation using Docker

## Prerequisites

Install Docker on your system. See [Docker Installation Guide](docker-installation.md) for detailed instructions for macOS, Windows, and Linux.

## Quick Start (Production)

```bash
# Clone the repository
git clone https://github.com/gamonoid/icehrm.git
cd icehrm

# Build and start IceHrm
docker compose up -d --build
```

Visit [http://localhost:5555](http://localhost:5555) and login with username `admin` and password `admin`. Change that password before putting the installation on a network.

`docker-compose.yaml` is the default file name, so `-f docker-compose.yaml` is optional on every command in this guide.

**Note:** The Docker build runs `npm install` and `npm run asset:build:prod` inside the image to compile frontend assets, so the first build takes a few minutes. There is no installer to walk through — the application config is baked into the image and the database is seeded from `docker/init.sql` on first start, so you land straight on the login page.

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

# Host port for the bundled MySQL, bound to 127.0.0.1 only (default: 9555)
DB_PORT=9555
```

`DB_PORT` is not listed in `docker-prod.env.example`, but `docker-compose.yaml` honours it.

Then restart the containers:

```bash
docker compose down
docker compose up -d
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
docker compose up -d icehrm icehrm-worker
```

Naming the services explicitly is what keeps the bundled MySQL out of it: the
application declares `depends_on: mysql` with `required: false`, so Compose is happy
to start without it. This needs Docker Compose v2.20 or newer.

## Docker Services

| Service | Container | Description | Port on the host |
|---------|-----------|-------------|------------------|
| icehrm | `icehrm-app` | Main application | `5555` (`APP_PORT`) |
| mysql | `icehrm-mysql` | MySQL 8.0.32, seeded from `docker/init.sql` | `127.0.0.1:9555` (`DB_PORT`) |
| icehrm-worker | `icehrm-worker` | Background jobs and scheduled tasks | none |

MySQL is published on the loopback interface only. Binding it to `0.0.0.0` would put
the database in reach of anything on the same network, so it is reachable from the
host — for a backup or a GUI client — and from nowhere else.

## Data Persistence

All data is persisted in Docker volumes:
- `icehrm-mysql-data` - Database files
- `icehrm-app-data` - Uploaded files and logs

## Updating

From v36, IceHrm updates itself: an administrator sees a banner on the dashboard when
a newer release is published, and the updater replaces the program files in place. It
backs the current version aside first, never touches `app/config.php`, `app/data/` or
`app/cache/`, leaves extensions you installed yourself alone, and can roll back from
the same screen.

That update lives in the container's writable layer, so rebuilding the image
(`up -d --build`) puts you back on the version the image was built from. To make an
update permanent, pull the newer source and rebuild:

```bash
git pull
docker compose up -d --build
```

Your database and uploads are in named volumes and are not affected by a rebuild.

## Stopping IceHrm

```bash
docker compose down
```

This keeps both volumes. To discard the database and uploads as well, add `-v` —
that is not reversible.

## Viewing Logs

```bash
docker compose logs -f            # everything
docker compose logs -f icehrm     # just the application
```
