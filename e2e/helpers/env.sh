# Shared env loader for the shell-based e2e tests under e2e/.
#
# Source this from any test script:
#     . "$(dirname "$0")/../helpers/env.sh"
#
# It reads e2e/.env and exports:
#   E2E_BASE_URL  the SPA entry point, exactly as configured (…/app/ui/)
#   E2E_ORIGIN    scheme+host+port                            (http://localhost:9080)
#   E2E_APP       the PHP app base used by login.php/service.php (…/app)
#
# An already-exported E2E_BASE_URL in the environment wins over the file, so a
# one-off run against another host is just:
#     E2E_BASE_URL=https://staging.example.com/app/ui/ sh e2e/smoke/<test>.sh

# shellcheck shell=sh

_e2e_dir="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
_e2e_env_file="$_e2e_dir/.env"

# Print an actionable error and stop. $1 = the specific problem.
_e2e_env_fail() {
  echo >&2
  echo "  ──────────────────────────────────────────────────────────────" >&2
  echo "   e2e configuration missing" >&2
  echo "  ──────────────────────────────────────────────────────────────" >&2
  echo "   $1" >&2
  echo >&2
  echo "   Every e2e test reads the app URL from e2e/.env, which is" >&2
  echo "   git-ignored so each machine can point at its own app." >&2
  echo >&2
  echo "   Create it:" >&2
  echo "       cp e2e/.env.example e2e/.env" >&2
  echo >&2
  echo "   For icehrm-pro the URL is:" >&2
  echo "       E2E_BASE_URL=http://localhost:9080/app/ui/" >&2
  echo >&2
  echo "   Expected file: $_e2e_env_file" >&2
  echo "  ──────────────────────────────────────────────────────────────" >&2
  echo >&2
  exit 1
}

if [ -z "${E2E_BASE_URL:-}" ]; then
  [ -f "$_e2e_env_file" ] || _e2e_env_fail "No e2e/.env file found."
  # Parse KEY=VALUE lines, ignoring comments/blanks and stripping optional quotes.
  E2E_BASE_URL=$(sed -n 's/^[[:space:]]*E2E_BASE_URL[[:space:]]*=[[:space:]]*//p' "$_e2e_env_file" \
    | sed 's/^"//;s/"$//;s/^'\''//;s/'\''$//' | tail -1)
  [ -n "$E2E_BASE_URL" ] || _e2e_env_fail "e2e/.env exists but does not set E2E_BASE_URL."
fi

[ -n "$E2E_BASE_URL" ] || _e2e_env_fail "E2E_BASE_URL is set but empty."

# origin = everything up to the third slash (scheme://host:port)
E2E_ORIGIN=$(printf '%s' "$E2E_BASE_URL" | sed -E 's#^([a-zA-Z][a-zA-Z0-9+.-]*://[^/]+).*#\1#')
# app base = the URL with any trailing slash and trailing "ui" segment removed,
# because the smoke tests talk to the PHP endpoints (login.php, service.php)
# that live one level above the SPA entry point.
E2E_APP=$(printf '%s' "$E2E_BASE_URL" | sed -E 's#/+$##; s#/ui$##')

export E2E_BASE_URL E2E_ORIGIN E2E_APP

# --- database that belongs to the SAME app -------------------------------------
#
# The smoke tests both call the app over HTTP and read/write its database directly.
# Those two MUST point at the same environment. They used to disagree: BASE came
# from e2e/.env (the testing stack) while the db helper was hardcoded to the dev
# container, so setup wrote to one database and the assertions ran against another —
# and the early phases still "passed" because they were self-consistent within dev.
#
# Derive the database from the app's port, overridable for other setups.
case "$E2E_ORIGIN" in
  *:9380*) _e2e_db_default_container="icehrm-testing-mysql-testing-1"
           _e2e_db_default_user="testing"; _e2e_db_default_pass="testing" ;;
  *)       _e2e_db_default_container="icehrm-pro-mysql-1"
           _e2e_db_default_user="root";    _e2e_db_default_pass="IceHrmR00t" ;;
esac

E2E_DB_CONTAINER="${E2E_DB_CONTAINER:-$_e2e_db_default_container}"
E2E_DB_USER="${E2E_DB_USER:-$_e2e_db_default_user}"
E2E_DB_PASS="${E2E_DB_PASS:-$_e2e_db_default_pass}"
E2E_DB_NAME="${E2E_DB_NAME:-icehrm}"

export E2E_DB_CONTAINER E2E_DB_USER E2E_DB_PASS E2E_DB_NAME
