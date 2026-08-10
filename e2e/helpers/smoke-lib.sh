#!/usr/bin/env bash
#
# Shared plumbing for the shell smoke tests under e2e/smoke/.
# -----------------------------------------------------------
# The leave smoke tests grew a copy of this code each, and the copies drifted: when
# the CSRF origin gate landed, `ca()` had to be fixed in two places and the second
# copy's failure surfaced three phases later as a misleading "no working day" error.
# Everything that talks to the app or the database therefore lives here once.
#
# Source it, do not execute it — it is deliberately NOT under smoke/, because
# smoke/run-all.sh runs every *.sh it finds there.
#
#   . "$(dirname "$0")/../helpers/approval-lib.sh"
#
# Provides:
#   BASE                     app base URL (from e2e/.env, never hardcoded)
#   dbq <sql>                query the DB that belongs to the app under test
#   emp_of <username>        employee id for a login
#   login <username>         authenticate; cookie jar per user
#   ca <user> <sa> <req> <t> <mod>     custom action (a=ca)
#   add_record <user> <t> <mg> <mn> <curl-args…>   create a record (a=add)
#   api_token <user>         bearer token for the REST API
#   api_post <token> <path> <json>     POST to the REST API
#   json_field <json> <key>  read a scalar field out of a JSON response
#   setting_get / setting_set <name> [value]
#   ok / bad / info          assertion counters -> PASS / FAIL
#   finish "<summary>"       print the result banner and exit 0/1

set -uo pipefail

# The app URL comes from e2e/.env, shared by every test under e2e/. helpers/env.sh
# also derives the matching DB container from the app port, so a run can never end
# up driving one environment over HTTP while reading another's database.
. "$(dirname "${BASH_SOURCE[0]}")/env.sh"
BASE="$E2E_APP"

JARDIR=$(mktemp -d)
# Subclass scripts add their own restore logic via add_cleanup.
CLEANUP_CMDS=""
add_cleanup() { CLEANUP_CMDS="$CLEANUP_CMDS
$1"; }
_run_cleanup() {
  [ -n "$CLEANUP_CMDS" ] && eval "$CLEANUP_CMDS"
  rm -rf "$JARDIR"
}
trap _run_cleanup EXIT

PASS=0
FAIL=0
ok()   { echo "  [PASS] $1"; PASS=$((PASS+1)); }
bad()  { echo "  [FAIL] $1"; FAIL=$((FAIL+1)); }
info() { echo "  ...   $1"; }

dbq() {
  docker exec "$E2E_DB_CONTAINER" mysql -u "$E2E_DB_USER" -p"$E2E_DB_PASS" "$E2E_DB_NAME" -N -e "$1" 2>/dev/null
}

emp_of() { dbq "SELECT employee FROM Users WHERE username='$1' LIMIT 1"; }

# Resolve the employee ids of the standard e2e accounts. These must never be
# hardcoded: the ids differ between the dev and testing stacks, and a literal that
# is right on one silently configures unrelated employees on the other.
resolve_employees() {
  EMP_ADMIN=$(emp_of admin)
  EMP_MANAGER=$(emp_of manager)
  EMP_USER1=$(emp_of user1)
  EMP_USER2=$(emp_of user2)
  EMP_USER3=$(emp_of user3)
  EMP_USER4=$(emp_of user4)
  local v id
  for v in EMP_ADMIN EMP_MANAGER EMP_USER1 EMP_USER2 EMP_USER3 EMP_USER4; do
    eval "id=\$$v"
    if [ -z "${id:-}" ]; then
      echo "  [FAIL] could not resolve $v from $E2E_DB_CONTAINER — are the e2e accounts seeded?" >&2
      echo "         run: bash e2e/bootstrap/seed-users.sh" >&2
      exit 1
    fi
  done
}

# login <username> -> cookie jar at $JARDIR/<username>.jar
login() {
  local u="$1" jar="$JARDIR/$1.jar" html csrf landing
  # Earlier runs may have tripped the lockout counter; clear it so a failure here
  # always means "the credentials/flow are broken", never "the previous test locked
  # this account out".
  dbq "UPDATE Users SET wrong_password_count=0, last_wrong_attempt_at=NULL WHERE username='$u'" >/dev/null
  html=$(curl -s -c "$jar" "$BASE/login.php")
  # Read the token out of the csrf field itself — do NOT match a fixed-width hex
  # string. The token length is not stable (it moved from 40 to 64 hex chars) and a
  # stale width silently yields an empty token, which fails login and then surfaces
  # much later as a confusing NO_USER_FOUND.
  csrf=$(printf '%s' "$html" | grep -oE '<input[^>]*id="csrf"[^>]*>' | head -1 \
         | grep -oE 'value="[^"]*"' | sed 's/value="//;s/"$//')
  if [ -z "$csrf" ]; then bad "login $u: could not read csrf token from login.php"; return 1; fi
  landing=$(curl -s -o /dev/null -b "$jar" -c "$jar" -L -w '%{url_effective}' \
    --data-urlencode "username=$u" --data-urlencode 'password=Admin123$' \
    --data-urlencode "csrf=$csrf" "$BASE/login.php")
  case "$landing" in
    *login.php*) bad "login $u failed (landed on $landing)"; return 1 ;;
  esac
  return 0
}

# ca <user> <sub-action> <req-json> <model> <mod>
#
# The Referer is required, not cosmetic: service.php gates state-changing actions
# (a=ca among them) on the request being same-origin — see
# BaseService::isSameOriginRequest(). A browser always sends one; curl does not, so
# without it every call returns {"code":"CSRF_ORIGIN_DENIED"} and the failure
# surfaces far from its cause.
ca() {
  local jar="$JARDIR/$1.jar"
  curl -s -b "$jar" -H "Referer: $BASE/ui/" -G "$BASE/service.php" \
    --data-urlencode "t=$4" --data-urlencode "a=ca" \
    --data-urlencode "sa=$2" --data-urlencode "mod=$5" \
    --data-urlencode "req=$3"
}

# add_record <user> <model> <module-group> <module-name> <curl args…>
# mg/mn set the module scope for the request the same way the SPA does.
add_record() {
  local user="$1" model="$2" mg="$3" mn="$4"
  shift 4
  curl -s -b "$JARDIR/$user.jar" -H "Referer: $BASE/ui/" -X POST "$BASE/service.php" \
    -d "a=add" -d "t=$model" --data-urlencode "mg=$mg" --data-urlencode "mn=$mn" "$@"
}

# api_token <username> -> the SPA's short-lived JWT, which the REST API accepts as a
# bearer token. The shell embeds it, so this reads it back out of /ui/ (the same trick
# bootstrap/seed-demo-data.sh uses). Requires login <username> first.
api_token() {
  curl -s -b "$JARDIR/$1.jar" "$BASE/ui/" | grep -oE 'eyJ[A-Za-z0-9_.-]{40,}' | head -1
}

# api_post <token> <path-under-/api/> <json-body>
api_post() {
  curl -s -X POST "$BASE/api/$2" \
    -H "Authorization: Bearer $1" \
    -H 'Content-Type: application/json' \
    -d "$3"
}

# json_field <json> <key> -> the scalar value of "key", unquoted and unescaped. Good
# enough for the flat responses these tests assert on; not a general JSON parser.
#
# Two things it has to get right, both of which bit:
#
#  * sed -E, because the alternation must be ERE. In a BRE, `\|` is a GNU extension
#    that BSD sed (macOS) does not implement — it silently matches nothing, so every
#    field reads as empty and every assertion fails with no hint as to why.
#  * escaped characters INSIDE the value. Error messages quote identifiers, so the
#    JSON contains \" and \/ — a naive "[^"]*" pattern stops at the first escaped
#    quote and returns a truncated string, which then fails to match anything.
json_field() {
  printf '%s' "$1" | tr -d '\n' \
    | sed -n -E "s/.*\"$2\"[[:space:]]*:[[:space:]]*(\"([^\"\\\\]|\\\\.)*\"|[^,}[:space:]]+).*/\1/p" \
    | sed -E 's/^"//; s/"$//' \
    | sed -e 's|\\/|/|g' -e 's/\\"/"/g' -e 's/\\\\/\\/g'
}

setting_get() { dbq "SELECT value FROM Settings WHERE name='$1'"; }
setting_set() { dbq "UPDATE Settings SET value='$2' WHERE name='$1'" >/dev/null; }

# capture_chain <employee-id>
#
# Record an employee's supervisor/approver1..3 and register a cleanup that restores
# them exactly. IFNULL(...,'NULL') yields the literal string NULL, which interpolates
# into the restore statement as the SQL keyword — so a column that started NULL goes
# back to NULL rather than 0. Sets ORIG_CHAIN for the closing banner.
capture_chain() {
  local empId="$1" sup a1 a2 a3
  sup=$(dbq "SELECT IFNULL(supervisor,'NULL') FROM Employees WHERE id=$empId")
  a1=$(dbq "SELECT IFNULL(approver1,'NULL') FROM Employees WHERE id=$empId")
  a2=$(dbq "SELECT IFNULL(approver2,'NULL') FROM Employees WHERE id=$empId")
  a3=$(dbq "SELECT IFNULL(approver3,'NULL') FROM Employees WHERE id=$empId")
  ORIG_CHAIN="supervisor=$sup approver1=$a1 approver2=$a2 approver3=$a3"
  add_cleanup "dbq \"UPDATE Employees SET supervisor=$sup, approver1=$a1, approver2=$a2, approver3=$a3 WHERE id=$empId\" >/dev/null"
}

# set_chain <employee-id> <supervisor> <approver1> <approver2> <approver3>
# Pass the literal NULL for "unset".
set_chain() {
  dbq "UPDATE Employees SET supervisor=$2, approver1=$3, approver2=$4, approver3=$5 WHERE id=$1" >/dev/null
}

# read_chain <employee-id> -> "sup|a1|a2|a3" with empty strings for NULL
read_chain() {
  dbq "SELECT CONCAT_WS('|',IFNULL(supervisor,''),IFNULL(approver1,''),IFNULL(approver2,''),IFNULL(approver3,'')) FROM Employees WHERE id=$1"
}

# A future weekday, N days out, as YYYY-MM-DD (BSD and GNU date).
future_date() {
  date -v+"$1"d +%Y-%m-%d 2>/dev/null || date -d "+$1 days" +%Y-%m-%d
}

# Fail fast with the counters printed, so a broken precondition is not reported as
# a passing run.
die() { echo; echo "  ABORTED: $1"; echo "PASS=$PASS FAIL=$FAIL"; exit 1; }

finish() {
  echo
  echo "=================================================================="
  echo " RESULT: $PASS passed, $FAIL failed   ${1:-}"
  echo "=================================================================="
  [ "$FAIL" -eq 0 ]
}
