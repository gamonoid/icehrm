#!/bin/bash
# Named fixture records the e2e specs assert on, for the TESTING database.
#
# Distinct from seed-demo-data.sh: that generates *bulk* plausible data through the
# app's demo-mode extension. This creates the *specific, named* records individual
# specs look for — "lists the seeded loan", "the sign-up session", the owner-less
# report file. Those records only ever existed in one developer's dev database and
# were never captured anywhere, which is why the suite could not be reproduced on a
# clean environment.
#
# Free-build only. The pro fixtures (leave, performance reviews, recruitment,
# payroll rosters) are gone: PerformanceReviews, ReviewFeedbacks, EmployeeGoals,
# ReviewTemplates, Job and Candidates have no table in this schema, and because the
# whole block runs as ONE mysql batch that aborts on the first error, keeping them
# made every later fixture — company and employee documents included — silently
# never get created, then failed the run.
#
# Every statement is idempotent (matched on the natural key), so this is safe to
# re-run and safe to run against an already-populated database.
#
# Usage:  e2e/bootstrap/seed-fixtures.sh      (called by globalSetup)
set -uo pipefail

DB_CONTAINER="${DB_CONTAINER:-icehrm-testing-mysql-testing-1}"
DB_USER="${DB_USER:-testing}"
DB_PASS="${DB_PASS:-testing}"
DB_NAME="${DB_NAME:-icehrm}"

if ! docker ps --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
  echo "seed-fixtures: database container '$DB_CONTAINER' is not running." >&2
  exit 1
fi

if ! docker exec -i "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
     2> >(grep -v "Using a password" >&2) <<'SQL'
SET @u1    = (SELECT employee FROM Users WHERE username = 'user1');
SET @mgr   = (SELECT employee FROM Users WHERE username = 'manager');
-- Currency ids are not stable across seeds (testing starts at 3), so resolve by code.
SET @cur   = (SELECT id FROM CurrencyTypes WHERE code = 'USD' LIMIT 1);
SET @cur   = IFNULL(@cur, (SELECT MIN(id) FROM CurrencyTypes));

-- ── Employee salary (salary-native) ──────────────────────────────────────────
-- The spec names the employee explicitly, so the fixture has to match it.
INSERT INTO Employees (employee_id, first_name, last_name, work_email, status)
VALUES ('E-SALARY', 'Andrew', 'Clark', 'andrew.clark@example.com', 'Active')
ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name),
                        status = VALUES(status);
SET @sal = (SELECT id FROM Employees WHERE employee_id = 'E-SALARY');

DELETE FROM EmployeeSalary WHERE employee = @sal;
INSERT INTO EmployeeSalary (employee, component, pay_frequency, currency, amount, details)
VALUES (@sal, 1, 'Monthly', @cur, 5500.00, 'Seeded by e2e fixtures'),
       (@sal, 3, 'Monthly', @cur, 750.00,  'Seeded by e2e fixtures');

-- ── Employee loans (loans-native) ────────────────────────────────────────────
-- Admin tab lists it; the employee tab is the same row seen as its owner, so it
-- must belong to user1.
-- The spec asserts the exact values it will see: an "Approved" status tag, and the
-- details text rendered into the read-only view form. Keep them in step with
-- loans-native.spec.js if either side changes.
DELETE FROM EmployeeCompanyLoans WHERE employee IN (@u1, @mgr);
INSERT INTO EmployeeCompanyLoans
  (employee, loan, start_date, last_installment_date, period_months, currency,
   amount, monthly_installment, status, details)
VALUES
  (@u1, (SELECT id FROM CompanyLoans WHERE name = 'Personal loan'),
   '2026-01-01', '2026-12-01', 12, @cur, 12000.00, 1000.00, 'Approved',
   'Laptop purchase loan'),
  -- Deliberately NOT Approved: the admin tab asserts on a single "Approved" tag, and
  -- a second approved loan makes that locator ambiguous under strict mode. Keeping the
  -- statuses distinct lets the spec stay strict instead of falling back to .first().
  (@mgr, (SELECT id FROM CompanyLoans WHERE name = 'Educational loan'),
   '2026-02-01', '2027-01-01', 12, @cur, 6000.00, 500.00, 'Repayment',
   'Evening degree course');

-- ── Training session (training-native) ───────────────────────────────────────
-- attendanceType 'Sign Up' is what surfaces the sign-up button the spec clicks;
-- status must be Approved or the session is not offered.
INSERT INTO TrainingSessions
  (name, course, description, scheduled, dueDate, deliveryMethod, deliveryLocation,
   status, attendanceType, requireProof)
SELECT 'Marketing Fundamentals Workshop', 1, 'Seeded by e2e fixtures',
       DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 30 DAY),
       'Classroom', 'Head Office', 'Approved', 'Sign Up', 'No'
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM TrainingSessions) t
                    WHERE t.name = 'Marketing Fundamentals Workshop');
SET @ts = (SELECT id FROM TrainingSessions WHERE name = 'Marketing Fundamentals Workshop' LIMIT 1);

-- A second, already-enrolled session so "my sessions" is not empty and the
-- Mark Completed control has something to act on.
INSERT INTO TrainingSessions
  (name, course, description, scheduled, dueDate, deliveryMethod, deliveryLocation,
   status, attendanceType, requireProof)
-- 'Assign', NOT 'Sign Up': the spec asserts a single sign-up icon on the All
-- Training Sessions tab, and a second sign-up-able session makes that locator
-- ambiguous under strict mode. Only the Marketing session is signed up for.
SELECT 'People Management Essentials', 2, 'Seeded by e2e fixtures',
       DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 21 DAY),
       'Classroom', 'Head Office', 'Approved', 'Assign', 'No'
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM TrainingSessions) t
                    WHERE t.name = 'People Management Essentials');
SET @ts2 = (SELECT id FROM TrainingSessions WHERE name = 'People Management Essentials' LIMIT 1);

-- Enrol user1 in the MARKETING session: "my sessions tab shows the enrolment with
-- Mark Completed" asserts on that name with a Scheduled tag. The second session is
-- left un-enrolled and Sign Up, so the All Training Sessions tab still offers a
-- sign-up action for the first test.
DELETE FROM EmployeeTrainingSessions WHERE employee = @u1 AND trainingSession IN (@ts, @ts2);
INSERT INTO EmployeeTrainingSessions (employee, trainingSession, status)
VALUES (@u1, @ts, 'Scheduled');

-- ── Owner-less report file (security-file-access) ────────────────────────────
-- The a=file guard refuses an owner-less file to anyone but an admin. The spec
-- probes a specific report name, so the row has to exist AND have no employee —
-- against an empty Files table the endpoint answers "not found" and the guard looks
-- like it failed open when it was never exercised.
INSERT INTO Files (name, filename, employee, file_group, size, size_text)
SELECT 'Report_Active_Employee_Report-2026-07-08_12-24-11',
       'Report_Active_Employee_Report-2026-07-08_12-24-11.csv',
       NULL, 'report', 1024, '1 KB'
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM Files) f
                    WHERE f.name = 'Report_Active_Employee_Report-2026-07-08_12-24-11');

-- An owner-scoped file too, so the "own file still resolves" side of the matrix has
-- something to resolve.
INSERT INTO Files (name, filename, employee, file_group, size, size_text)
SELECT CONCAT('e2e-own-file-', @u1), CONCAT('e2e-own-file-', @u1, '.png'),
       @u1, 'profile_image', 512, '512 B'
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM Files) f
                    WHERE f.name = CONCAT('e2e-own-file-', @u1));

-- ── Company documents (documents-native) ─────────────────────────────────────
-- "Company Documents" is the FIRST tab of admin::documents, so it is the pane the
-- mount test asserts on. Seeding only EmployeeDocuments left that first tab empty.
INSERT INTO CompanyDocuments (name, details, status, valid_until)
SELECT 'Employee Handbook', 'Seeded by e2e fixtures', 'Active',
       DATE_ADD(CURDATE(), INTERVAL 2 YEAR)
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM CompanyDocuments) d
                    WHERE d.name = 'Employee Handbook');
INSERT INTO CompanyDocuments (name, details, status, valid_until)
SELECT 'Code of Conduct', 'Seeded by e2e fixtures', 'Active',
       DATE_ADD(CURDATE(), INTERVAL 2 YEAR)
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM CompanyDocuments) d
                    WHERE d.name = 'Code of Conduct');

-- ── Employee documents (documents-native) ────────────────────────────────────
-- The spec only needs a card to render, for admin and for the employee.
DELETE FROM EmployeeDocuments WHERE details = 'Seeded by e2e fixtures';
INSERT INTO EmployeeDocuments (employee, document, date_added, valid_until, status, details)
VALUES (@u1,  1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 YEAR), 'Active', 'Seeded by e2e fixtures'),
       (@u1,  2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 YEAR), 'Active', 'Seeded by e2e fixtures'),
       (@mgr, 3, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 2 YEAR), 'Active', 'Seeded by e2e fixtures');
SQL
then
  echo "seed-fixtures: SQL failed — fixtures are incomplete." >&2
  exit 1
fi

echo "seed-fixtures: named records ready —"
docker exec "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "
SELECT CONCAT('  salary rows      ', COUNT(*)) FROM EmployeeSalary;
SELECT CONCAT('  employee loans   ', COUNT(*)) FROM EmployeeCompanyLoans;
SELECT CONCAT('  training sess.   ', COUNT(*)) FROM TrainingSessions;
SELECT CONCAT('  enrolments       ', COUNT(*)) FROM EmployeeTrainingSessions;
SELECT CONCAT('  emp documents    ', COUNT(*)) FROM EmployeeDocuments;" 2>/dev/null
