#!/bin/bash
# Seed the accounts every e2e spec logs in as, in the TESTING database.
#
# The testing stack (docker-compose-testing.yaml) is seeded from docker/init.sql,
# which ships schema + modules + settings but essentially no people: one employee
# and one admin user. The specs expect admin / manager / user1..user4, all with the
# password Admin123$, and a reporting line so the manager-scope tests have
# subordinates to see.
#
# Idempotent: run it as often as you like. Employees are matched by employee_id and
# users by username, so re-running updates rather than duplicating.
#
# Usage:  e2e/bootstrap/seed-users.sh            (called automatically by globalSetup)
#         DB_CONTAINER=... APP_CONTAINER=... e2e/bootstrap/seed-users.sh
set -euo pipefail

DB_CONTAINER="${DB_CONTAINER:-icehrm-testing-mysql-testing-1}"
APP_CONTAINER="${APP_CONTAINER:-icehrm-testing-icehrm-1}"
DB_USER="${DB_USER:-testing}"
DB_PASS="${DB_PASS:-testing}"
DB_NAME="${DB_NAME:-icehrm}"
PASSWORD="${E2E_PASSWORD:-Admin123\$}"

if ! docker ps --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
  echo "seed-users: database container '$DB_CONTAINER' is not running." >&2
  echo "            start the stack:  docker compose -f docker-compose-testing.yaml up -d" >&2
  exit 1
fi

# Hash with the app's own PasswordManager settings (bcrypt cost 13) so the stored
# format matches what login.php expects. Generated in the app container so the
# cost/algorithm follow the application, not this script.
#
# The password is passed as an env var (-e PW) rather than interpolated into the PHP
# snippet: it contains a '$', which the shell and PHP would both try to expand.
HASH="$(docker exec -e PW="$PASSWORD" "$APP_CONTAINER" php -r \
  'echo password_hash(getenv("PW"), PASSWORD_BCRYPT, ["cost" => 13]);' 2>/dev/null | tr -d '\r')"

if [ -z "$HASH" ]; then
  echo "seed-users: could not generate a password hash via '$APP_CONTAINER'." >&2
  exit 1
fi

# Prove the hash actually verifies before writing it to every account. An earlier
# version of this script dropped the -e flag, so getenv("PW") returned false and it
# silently seeded a hash of the EMPTY STRING — every login then failed, and because
# the suite retries six accounts across 84 specs it drove them all into the
# five-strike lockout, which looks like a broken app rather than a broken fixture.
if ! docker exec -e H="$HASH" -e PW="$PASSWORD" "$APP_CONTAINER" php -r \
  'exit(password_verify(getenv("PW"), getenv("H")) ? 0 : 1);' 2>/dev/null; then
  echo "seed-users: generated hash does not verify against the intended password — refusing to seed." >&2
  exit 1
fi

mysql_run() {
  docker exec -i "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>/dev/null
}

# One employee row per login, then one user row bound to it.
#
#   admin    Admin     — employee 1 (already seeded by init.sql)
#   manager  Manager   — supervises user1 and user3, so manager-scope specs have data
#   user1    Employee  — the main self-service actor
#   user2    Manager   — a second manager with NO subordinates (negative scope cases)
#   user3    Employee  — a second subordinate
#   user4    Employee  — unrelated to the manager (out-of-scope assertions)
mysql_run <<SQL
SET @pw = '${HASH}';

-- Employees ---------------------------------------------------------------
INSERT INTO Employees (employee_id, first_name, last_name, work_email, status)
VALUES ('E-MANAGER', 'Mia', 'Manager', 'manager@example.com', 'Active')
ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name),
                        work_email = VALUES(work_email), status = VALUES(status);
SET @mgr = (SELECT id FROM Employees WHERE employee_id = 'E-MANAGER');

INSERT INTO Employees (employee_id, first_name, last_name, work_email, status, supervisor)
VALUES ('E-USER1', 'Uma', 'One', 'user1@example.com', 'Active', @mgr)
ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name),
                        work_email = VALUES(work_email), status = VALUES(status),
                        supervisor = VALUES(supervisor);

INSERT INTO Employees (employee_id, first_name, last_name, work_email, status)
VALUES ('E-USER2', 'Uri', 'Two', 'user2@example.com', 'Active')
ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name),
                        work_email = VALUES(work_email), status = VALUES(status);

INSERT INTO Employees (employee_id, first_name, last_name, work_email, status, supervisor)
VALUES ('E-USER3', 'Ivy', 'Three', 'user3@example.com', 'Active', @mgr)
ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name),
                        work_email = VALUES(work_email), status = VALUES(status),
                        supervisor = VALUES(supervisor);

INSERT INTO Employees (employee_id, first_name, last_name, work_email, status)
VALUES ('E-USER4', 'Ora', 'Four', 'user4@example.com', 'Active')
ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name),
                        work_email = VALUES(work_email), status = VALUES(status);

-- Users -------------------------------------------------------------------
INSERT INTO Users (username, email, password, user_level, employee)
VALUES ('manager', 'manager@example.com', @pw, 'Manager',
        (SELECT id FROM Employees WHERE employee_id = 'E-MANAGER'))
ON DUPLICATE KEY UPDATE password = @pw, user_level = VALUES(user_level),
                        email = VALUES(email), employee = VALUES(employee);

INSERT INTO Users (username, email, password, user_level, employee)
VALUES ('user1', 'user1@example.com', @pw, 'Employee',
        (SELECT id FROM Employees WHERE employee_id = 'E-USER1'))
ON DUPLICATE KEY UPDATE password = @pw, user_level = VALUES(user_level),
                        email = VALUES(email), employee = VALUES(employee);

INSERT INTO Users (username, email, password, user_level, employee)
VALUES ('user2', 'user2@example.com', @pw, 'Manager',
        (SELECT id FROM Employees WHERE employee_id = 'E-USER2'))
ON DUPLICATE KEY UPDATE password = @pw, user_level = VALUES(user_level),
                        email = VALUES(email), employee = VALUES(employee);

INSERT INTO Users (username, email, password, user_level, employee)
VALUES ('user3', 'user3@example.com', @pw, 'Employee',
        (SELECT id FROM Employees WHERE employee_id = 'E-USER3'))
ON DUPLICATE KEY UPDATE password = @pw, user_level = VALUES(user_level),
                        email = VALUES(email), employee = VALUES(employee);

INSERT INTO Users (username, email, password, user_level, employee)
VALUES ('user4', 'user4@example.com', @pw, 'Employee',
        (SELECT id FROM Employees WHERE employee_id = 'E-USER4'))
ON DUPLICATE KEY UPDATE password = @pw, user_level = VALUES(user_level),
                        email = VALUES(email), employee = VALUES(employee);

-- The admin shipped by init.sql: force the shared password and make sure it is
-- bound to an employee (several specs read the admin's own profile).
UPDATE Users SET password = @pw, user_level = 'Admin' WHERE username = 'admin';
UPDATE Users SET employee = (SELECT MIN(id) FROM Employees)
 WHERE username = 'admin' AND (employee IS NULL OR employee = 0);

-- A legacy MD5 hash would divert every login into the forced-password-reset
-- screen and fail the whole suite; make sure none survive here.
UPDATE Users SET password = @pw WHERE CHAR_LENGTH(password) = 32;

-- Clear the brute-force lockout. The suite logs in as the same six accounts
-- repeatedly across 84 specs, so a single bad fixture (or an interrupted run) trips
-- MAX_FAILED_LOGIN_ATTEMPTS=5 and every later spec then fails with
-- "login.php?f=1&locked=1" for the whole 15-minute window — which reads like a
-- broken application rather than a stale counter. Starting each run from a clean
-- slate keeps failures attributable to the thing under test.
UPDATE Users SET wrong_password_count = 0, last_wrong_attempt_at = NULL;
SQL

echo "seed-users: accounts ready (admin, manager, user1..user4 — password Admin123\$)"
docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
  "SELECT CONCAT('  ', u.username, '  ', u.user_level, '  employee=', IFNULL(u.employee,'-'),
                 '  supervisor=', IFNULL((SELECT e.supervisor FROM Employees e WHERE e.id = u.employee), '-'))
     FROM Users u ORDER BY u.username;" 2>/dev/null
