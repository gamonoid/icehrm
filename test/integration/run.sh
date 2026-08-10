#!/usr/bin/env bash
#
# Run the integration tests inside the docker-compose-testing stack.
#
#   test/integration/run.sh                 # bring up DB, run ALL *Test.php
#   test/integration/run.sh --fresh         # wipe docker/testing/db_data first (clean seed)
#   test/integration/run.sh security/EmployeeElementAccessTest.php   # run one named test
#   test/integration/run.sh EmployeeElementAccessTest       # path prefix and .php suffix optional
#   test/integration/run.sh EmployeeElement OnlyMe   # several; unknown names list the available tests
#
# The tests run as a PHP CLI process inside the `icehrm` testing container, against
# the `mysql-testing` DB (seeded from docker/init.sql). They connect via the app's
# own DB layer, so nginx/php-fpm need not be healthy — we use `compose run`, not `up`.
set -euo pipefail
cd "$(dirname "$0")/../.."   # repo root

COMPOSE="docker compose -f docker-compose-testing.yaml"
FRESH=0
TESTS=()

# All available tests (so new tests are picked up automatically), including
# category subdirectories (e.g. security/); names are kept relative to
# test/integration so the runner and the resolver agree on paths.
AVAILABLE=()
for p in test/integration/*Test.php test/integration/*/*Test.php; do
  [ -e "$p" ] || continue
  AVAILABLE+=("${p#test/integration/}")
done

# Resolve a user-supplied name to one of AVAILABLE: exact file name, name without
# .php, or a unique substring (e.g. "OnlyMe"). Unknown/ambiguous names abort with
# the list of available tests.
resolve_test() {
  local name="$1" matches=()
  for t in "${AVAILABLE[@]}"; do
    if [ "$t" = "$name" ] || [ "$t" = "$name.php" ]; then echo "$t"; return 0; fi
  done
  for t in "${AVAILABLE[@]}"; do
    case "$t" in *"$name"*) matches+=("$t");; esac
  done
  if [ ${#matches[@]} -eq 1 ]; then echo "${matches[0]}"; return 0; fi
  if [ ${#matches[@]} -gt 1 ]; then
    echo "!! '$name' is ambiguous: ${matches[*]}" >&2
  else
    echo "!! unknown test '$name'" >&2
  fi
  echo "   available tests:" >&2
  for t in "${AVAILABLE[@]}"; do echo "     $t" >&2; done
  return 1
}

for a in "$@"; do
  if [ "$a" = "--fresh" ]; then
    FRESH=1
  else
    TESTS+=("$(resolve_test "$a")") || exit 1
  fi
done
# Default: run every test.
if [ ${#TESTS[@]} -eq 0 ]; then
  TESTS=("${AVAILABLE[@]}")
fi

if [ "$FRESH" = "1" ]; then
  echo ">> wiping docker/testing/db_data for a clean seed"
  $COMPOSE down -v >/dev/null 2>&1 || true
  rm -rf docker/testing/db_data
fi

echo ">> building + starting mysql-testing"
$COMPOSE up -d --build mysql-testing

echo ">> waiting for MySQL to accept connections"
for i in $(seq 1 60); do
  if $COMPOSE exec -T mysql-testing sh -c 'mysqladmin ping -h127.0.0.1 -uroot -pIceHrmR00t --silent' >/dev/null 2>&1; then
    echo "   MySQL ready"; break
  fi
  sleep 2
  if [ "$i" = "60" ]; then echo "   MySQL did not become ready" >&2; exit 1; fi
done

# Marketplace / licence SystemData. Some models and module managers are only
# reachable on a licensed instance, so inject the same rows the e2e bootstrap uses
# (values live in e2e/.env, git-ignored). Non-fatal: most integration tests do not
# need it, and a missing e2e/.env should not block them.
if [ -x e2e/bootstrap/seed-systemdata.sh ]; then
  echo ">> injecting marketplace SystemData"
  e2e/bootstrap/seed-systemdata.sh || echo "   (skipped — see e2e/.env)"
fi

rc=0
for t in "${TESTS[@]}"; do
  echo ">> running test/integration/$t"
  $COMPOSE run --rm --no-deps icehrm \
    php -d xdebug.mode=off "/var/www/html/test/integration/$t" || rc=$?
done

exit $rc
