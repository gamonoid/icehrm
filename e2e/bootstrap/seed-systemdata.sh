#!/bin/bash
# Inject the marketplace / licence SystemData rows into the TESTING database.
#
# Run before any e2e or integration test. Every extensions-pro module (leave,
# performance, training, loans, recruitment, learn, expenses) is gated shell-side on
# the licence that UtilAdmin\LicenseService derives from
# SystemData['marketplace:my_extensions']; without it the SPA renders
# "No active IceHrmPro license" instead of the module and ~40 specs fail as
# "element not visible" — a failure mode that reads like a UI regression rather than
# a missing entitlement.
#
# The values live in e2e/.env (git-ignored) as base64 of the raw, PHP-serialised
# SystemData.value, so they survive a single KEY=VALUE line:
#
#   E2E_SYSTEMDATA_MARKETPLACE_CONNECTION_B64
#   E2E_SYSTEMDATA_MARKETPLACE_MY_EXTENSIONS_B64
#
# Usage:
#   e2e/bootstrap/seed-systemdata.sh            inject into the testing database
#   e2e/bootstrap/seed-systemdata.sh --export   print the two lines from a licensed
#                                               environment, to paste into e2e/.env
set -uo pipefail

HERE="$(cd "$(dirname "$0")" && pwd)"
ENV_FILE="${E2E_ENV_FILE:-$HERE/../.env}"

DB_CONTAINER="${DB_CONTAINER:-icehrm-testing-mysql-testing-1}"
DB_USER="${DB_USER:-testing}"
DB_PASS="${DB_PASS:-testing}"
DB_NAME="${DB_NAME:-icehrm}"

# Where to read licensed values FROM when exporting (a working, licensed instance).
SRC_CONTAINER="${SRC_DB_CONTAINER:-icehrm-pro-mysql-1}"
SRC_USER="${SRC_DB_USER:-root}"
SRC_PASS="${SRC_DB_PASS:-IceHrmR00t}"
SRC_NAME="${SRC_DB_NAME:-icehrm}"

KEYS=("marketplace:connection" "marketplace:my_extensions")
VARS=("E2E_SYSTEMDATA_MARKETPLACE_CONNECTION_B64" "E2E_SYSTEMDATA_MARKETPLACE_MY_EXTENSIONS_B64")

# --- export mode --------------------------------------------------------------
if [ "${1:-}" = "--export" ]; then
  echo "# Copy the two lines below into e2e/.env (git-ignored)."
  for i in "${!KEYS[@]}"; do
    v="$(docker exec "$SRC_CONTAINER" mysql -u "$SRC_USER" -p"$SRC_PASS" "$SRC_NAME" -N --raw \
          -e "SELECT TO_BASE64(value) FROM SystemData WHERE name='${KEYS[$i]}';" 2>/dev/null | tr -d '\n\r ')"
    if [ -z "$v" ]; then
      echo "# WARNING: '${KEYS[$i]}' not found in ${SRC_CONTAINER}/${SRC_NAME}" >&2
      continue
    fi
    echo "${VARS[$i]}=${v}"
  done
  exit 0
fi

# --- inject mode --------------------------------------------------------------
if [ ! -f "$ENV_FILE" ]; then
  echo "seed-systemdata: $ENV_FILE not found — copy e2e/.env.example and add the licence values." >&2
  exit 1
fi

# Minimal KEY=VALUE reader, matching the one in playwright.config.js.
read_env() {
  sed -n "s/^[[:space:]]*$1[[:space:]]*=[[:space:]]*//p" "$ENV_FILE" \
    | tail -1 | sed -e 's/^["'\'']//' -e 's/["'\'']$//' | tr -d '\r'
}

if ! docker ps --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
  echo "seed-systemdata: database container '$DB_CONTAINER' is not running." >&2
  echo "                 start it:  docker compose -f docker-compose-testing.yaml up -d" >&2
  exit 1
fi

missing=0
for i in "${!KEYS[@]}"; do
  name="${KEYS[$i]}"
  b64="$(read_env "${VARS[$i]}")"

  if [ -z "$b64" ]; then
    echo "seed-systemdata: ${VARS[$i]} is not set in $ENV_FILE — skipping '$name'." >&2
    echo "                 populate it with: e2e/bootstrap/seed-systemdata.sh --export" >&2
    missing=1
    continue
  fi

  # FROM_BASE64 in SQL, so the serialised payload never passes through the shell
  # (it contains quotes, semicolons and braces that would need escaping).
  docker exec -i "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>/dev/null <<SQL
INSERT INTO SystemData (name, value) VALUES ('${name}', FROM_BASE64('${b64}'))
ON DUPLICATE KEY UPDATE value = VALUES(value);
SQL

  # SystemData.name may not carry a unique key on every schema version; fall back to
  # an explicit update so a duplicate row is never left behind.
  docker exec -i "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>/dev/null <<SQL
UPDATE SystemData SET value = FROM_BASE64('${b64}') WHERE name = '${name}';
DELETE s1 FROM SystemData s1 JOIN SystemData s2
  ON s1.name = s2.name AND s1.id > s2.id
 WHERE s1.name = '${name}';
SQL

  len="$(docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N \
          -e "SELECT LENGTH(value) FROM SystemData WHERE name='${name}';" 2>/dev/null | tail -1)"
  printf '  %-30s injected (%s bytes)\n' "$name" "${len:-0}"
done

if [ "$missing" = "1" ]; then
  echo "seed-systemdata: WARNING — at least one value was missing; pro modules may render the licence block." >&2
fi

echo "seed-systemdata: done."
