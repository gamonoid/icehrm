# IceHrm All-in-One Docker Image

This directory contains an all-in-one Docker setup that combines all IceHrm components into a single container:

- **Nginx** - Web server
- **PHP-FPM** - PHP processor
- **MySQL** - Database server
- **Crond** - Background job processor

## When to Use

This setup is ideal for:
- Small deployments
- Demos and testing
- Platforms that only support single containers
- Quick local development

For production with high availability, use the separate services in `docker-compose-prod.yaml` instead.

## Quick Start

```bash
cd docker-all-in-one
docker compose up -d
```

Access IceHrm at [http://localhost:5566](http://localhost:5566)
- Username: `admin`
- Password: `admin`

## Configuration

Create a `.env` file in this directory to customize:

```env
# Port to expose (default: 5566)
APP_PORT=5566

# Base URL (change for production)
APP_BASE_URL=http://localhost:5566

# Database credentials
MYSQL_ROOT_PASSWORD=your_secure_root_password
MYSQL_DATABASE=icehrm
MYSQL_USER=icehrm
MYSQL_PASSWORD=your_secure_password
```

## Data Persistence

Data is persisted in Docker volumes:
- `icehrm-aio-mysql-data` - MySQL database files
- `icehrm-aio-app-data` - Uploaded files and logs

## Architecture

```
┌─────────────────────────────────────────┐
│           All-in-One Container          │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │           Supervisord            │   │
│  │  (Process Manager)               │   │
│  └─────────────────────────────────┘   │
│           │                             │
│  ┌────────┼────────┬──────────┐        │
│  │        │        │          │        │
│  ▼        ▼        ▼          ▼        │
│ Nginx  PHP-FPM   MySQL      Crond     │
│ :8080   :9000    :3306    (worker)    │
│                                         │
└─────────────────────────────────────────┘
```

## Commands

```bash
# Start
docker compose up -d

# View logs
docker compose logs -f

# Stop
docker compose down

# Stop and remove data
docker compose down -v

# Rebuild
docker compose up -d --build
```

## Troubleshooting

### Container won't start
Check logs:
```bash
docker compose logs
```

### MySQL connection issues
The container needs ~30-60 seconds on first start to initialize MySQL. Check health:
```bash
docker compose ps
```

### Reset everything
```bash
docker compose down -v
docker compose up -d --build
```
