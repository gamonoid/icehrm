#!/bin/bash
# Recreate the TESTING database from scratch, so every run starts from the same state.
#
# Why this exists: the seeding steps that follow are only reproducible if they start
# from a known baseline. Left alone the database accumulates — the demo generator
# APPENDS rather than upserts, employee ids drift between runs, and specs start
# depending on residue from earlier runs. A spec that passes on a mutated database and
# fails on a clean one (or the reverse) tells you nothing.
#
# MySQL's entrypoint only runs docker/init.sql when the data directory is empty, so a
# real reset means deleting docker/testing/db_data (git-ignored) and letting the
# container re-initialise. Mirrors `test/integration/run.sh --fresh`.
#
# Usage:  e2e/bootstrap/reset-db.sh          (called by globalSetup when E2E_FRESH_DB=1)
set -uo pipefail

cd "$(dirname "$0")/../.." || exit 1

COMPOSE="docker compose -f docker-compose-testing.yaml"
DB_CONTAINER="${DB_CONTAINER:-icehrm-testing-mysql-testing-1}"
DB_USER="${DB_USER:-testing}"
DB_PASS="${DB_PASS:-testing}"
DB_NAME="${DB_NAME:-icehrm}"

echo "reset-db: tearing the testing stack down (volumes included)…"
$COMPOSE down -v >/dev/null 2>&1 || true

echo "reset-db: removing docker/testing/db_data…"
rm -rf docker/testing/db_data

echo "reset-db: starting the stack — MySQL will re-run docker/init.sql…"
$COMPOSE up -d >/dev/null 2>&1 || {
  echo "reset-db: 'docker compose up -d' failed." >&2; exit 1;
}

echo "reset-db: waiting for MySQL…"
for i in $(seq 1 90); do
  if docker exec "$DB_CONTAINER" mysqladmin ping -h127.0.0.1 -uroot -pIceHrmR00t --silent >/dev/null 2>&1; then
    break
  fi
  sleep 2
  if [ "$i" = "90" ]; then echo "reset-db: MySQL did not become ready." >&2; exit 1; fi
done

# Ping succeeding only means the server is up; init.sql may still be importing. Wait
# for the schema to actually be there before anyone tries to seed into it.
echo "reset-db: waiting for docker/init.sql to finish importing…"
# Wait on sentinels the import is known to create, not a table count — the count is a
# moving target as migrations are added, and a threshold silently rots.
#
# Note: Modules is deliberately NOT a sentinel. init.sql creates the table but leaves
# it empty; ExtensionManager::setupExtensions() fills it on the first web request. The
# app is warmed below so the seeding steps that follow see a populated module list.
for i in $(seq 1 90); do
  ready="$(docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "
      SELECT CASE WHEN (SELECT COUNT(*) FROM information_schema.tables
                         WHERE table_schema='$DB_NAME' AND table_name IN
                               ('Users','Employees','Settings','Modules')) = 4
                   AND (SELECT COUNT(*) FROM Users) > 0
                  THEN 'yes' ELSE 'no' END;" 2>/dev/null | tail -1)"
  if [ "$ready" = "yes" ]; then
    n="$(docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N \
          -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';" 2>/dev/null | tail -1)"
    echo "reset-db: schema ready ($n tables)."
    break
  fi
  sleep 2
  if [ "$i" = "90" ]; then
    echo "reset-db: schema did not finish importing." >&2; exit 1
  fi
done

# The app container starts before MySQL is ready, so nudge it to reconnect cleanly.
docker restart icehrm-testing-icehrm-1 >/dev/null 2>&1 || true

# Warm the app: the first web request is what runs ExtensionManager::setupExtensions()
# and populates Modules. Without this the seeding steps below (and the specs) would see
# an app with no modules registered.
echo "reset-db: warming the app so modules register…"
APP_URL="${E2E_APP_BASE:-http://localhost:9380/app}/login.php"
for i in $(seq 1 60); do
  code="$(curl -s -o /dev/null -w '%{http_code}' -m 10 "$APP_URL" 2>/dev/null)"
  if [ "$code" = "200" ]; then
    mods="$(docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N \
             -e "SELECT COUNT(*) FROM Modules;" 2>/dev/null | tail -1)"
    if [ "${mods:-0}" -gt 0 ] 2>/dev/null; then
      echo "reset-db: app warm ($mods modules registered)."
      break
    fi
  fi
  sleep 2
  if [ "$i" = "60" ]; then
    echo "reset-db: app did not warm up (HTTP $code, modules ${mods:-0})." >&2; exit 1
  fi
done

echo "reset-db: done — database is at the docker/init.sql baseline."
