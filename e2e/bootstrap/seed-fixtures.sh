#!/bin/bash
# Named fixture records the e2e specs assert on, for the TESTING database.
#
# Distinct from seed-demo-data.sh: that generates *bulk* plausible data through the
# app's demo-mode extension. This creates the *specific, named* records individual
# specs look for — "lists the seeded loan", "the sign-up session", a leave type
# flagged attachment-mandatory. Those records only ever existed in one developer's
# dev database and were never captured anywhere, which is why the suite could not be
# reproduced on a clean environment.
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
APP_CONTAINER="${APP_CONTAINER:-icehrm-testing-icehrm-1}"

if ! docker ps --format '{{.Names}}' | grep -qx "$DB_CONTAINER"; then
  echo "seed-fixtures: database container '$DB_CONTAINER' is not running." >&2
  exit 1
fi

# --- leave attachment file -----------------------------------------------------
# leave-attachment-view opens a seeded leave and expects the dialog to render a real
# <a> and <img> pointing at the signed download URL. That needs an actual file on
# disk in the data directory, not just the Files row — otherwise the img 404s.
LEAVE_ATT="e2e-leave-attachment"
docker exec "$APP_CONTAINER" sh -c '
  d=/var/www/html/app/data
  mkdir -p "$d"
  # 1x1 transparent PNG
  printf "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" \
    | base64 -d > "$d/e2e-leave-attachment.png" 2>/dev/null || \
  printf "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" \
    | base64 --decode > "$d/e2e-leave-attachment.png"
' 2>/dev/null || echo "seed-fixtures: WARNING — could not write the leave attachment file" >&2

if ! docker exec -i "$DB_CONTAINER" mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
     2> >(grep -v "Using a password" >&2) <<'SQL'
SET @u1    = (SELECT employee FROM Users WHERE username = 'user1');
SET @mgr   = (SELECT employee FROM Users WHERE username = 'manager');
SET @admin = (SELECT employee FROM Users WHERE username = 'admin');
-- Currency ids are not stable across seeds (testing starts at 3), so resolve by code.
SET @cur   = (SELECT id FROM CurrencyTypes WHERE code = 'USD' LIMIT 1);
SET @cur   = IFNULL(@cur, (SELECT MIN(id) FROM CurrencyTypes));

-- NOTE ON ORDER: leave periods come FIRST. The adjustment and the attachment
-- fixtures below resolve the current period by id, and on a freshly-initialised
-- database there is no current-year period until this block creates one — the
-- inserts then fail with "Column 'leave_period' cannot be null". Incremental runs
-- never hit it because a previous run had already created the periods.
-- ── Leave periods ────────────────────────────────────────────────────────────
-- The seed ships periods for 2015-2017 only, so any leave applied for today falls
-- outside every period and the endpoints answer "The leave period for your leave
-- application is not defined" — which surfaces in the specs as an unrelated
-- assertion failure. Generate last/this/next year RELATIVE TO NOW so the fixture
-- does not rot the way a hardcoded year would.
INSERT INTO LeavePeriods (name, date_start, date_end, status)
SELECT CONCAT('Year ', y.yr),
       DATE(CONCAT(y.yr, '-01-01')), DATE(CONCAT(y.yr, '-12-31')), 'Active'
  FROM (SELECT YEAR(CURDATE()) - 1 AS yr
        UNION ALL SELECT YEAR(CURDATE())
        UNION ALL SELECT YEAR(CURDATE()) + 1) y
 WHERE NOT EXISTS (
   SELECT 1 FROM (SELECT * FROM LeavePeriods) lp
    WHERE YEAR(lp.date_start) = y.yr
 );

-- ── Leave type flags ─────────────────────────────────────────────────────────
-- leave-attachment-required and leave-reason-required drive the real getLeaveDays /
-- addLeave endpoints and assert on the flags they return. The rows exist in the
-- testing seed but with defaults, so the endpoints answered "No" and the specs
-- failed against a perfectly healthy app.
UPDATE LeaveTypes SET reason_required = 'Yes'      WHERE name = 'Casual leave';
UPDATE LeaveTypes SET attachment_mandatory = 'Yes' WHERE name = 'Medical leave';
UPDATE LeaveTypes SET reason_required = 'No', attachment_mandatory = 'No'
 WHERE name = 'Annual leave';

-- ── Leave entitlement adjustment (leave-entitlement-adjustments) ─────────────
-- LeaveStartingBalance is the "Adjustments" line on an entitlement panel. The spec
-- checks that an adjusted type shows Adjustments + Total (in that order) and an
-- unadjusted one shows neither, so exactly ONE type may carry an adjustment: Annual
-- for the admin, and Medical deliberately left alone.
DELETE FROM LeaveStartingBalance WHERE note = 'Seeded by e2e fixtures';
INSERT INTO LeaveStartingBalance (leave_type, employee, leave_period, amount, note)
SELECT (SELECT id FROM LeaveTypes WHERE name = 'Annual leave' LIMIT 1),
       @admin,
       (SELECT id FROM LeavePeriods WHERE YEAR(date_start) = YEAR(CURDATE()) LIMIT 1),
       -2.5, 'Seeded by e2e fixtures';

-- ── Carry-forward leave type (leave-calc-chart) ──────────────────────────────
-- The propagation chart only has anything to draw for a leave type that carries
-- forward, and the spec names it. carried_forward_leave_availability is the number
-- of days the carried balance stays usable into the next period.
INSERT INTO LeaveTypes
  (name, default_per_year, carried_forward, carried_forward_percentage,
   carried_forward_leave_availability, max_carried_forward_amount,
   employee_can_apply, supervisor_leave_assign, attachment_mandatory, reason_required)
SELECT 'Rooty', 10.000, 'Yes', 100, 365, 10, 'Yes', 'Yes', 'No', 'No'
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM LeaveTypes) t WHERE t.name = 'Rooty');

-- ── Leave groups ─────────────────────────────────────────────────────────────
-- model-admin-leaves checks that an admin can still READ leave groups through the
-- generic path (a regression guard from the LeaveGroup permission work). With an
-- empty table the endpoint correctly returns [] and the guard cannot tell "locked
-- down by mistake" from "nothing to return", so it needs at least one row.
INSERT INTO LeaveGroups (name, details)
SELECT * FROM (SELECT 'Latvia' AS n, 'Seeded by e2e fixtures' AS d) x
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM LeaveGroups) g WHERE g.name = 'Latvia');
INSERT INTO LeaveGroups (name, details)
SELECT * FROM (SELECT 'Germany' AS n, 'Seeded by e2e fixtures' AS d) x
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM LeaveGroups) g WHERE g.name = 'Germany');

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

-- ── Leave with an attachment (leave-attachment-view) ─────────────────────────
-- The spec opens a leave by a HARDCODED id (1840) via
-- window.modJs.getLeaveDaysReadonly(1840), so the row has to carry that exact id.
-- EmployeeLeaves.attachment stores the Files.NAME (not the filename), and the file
-- itself is written to the data directory above so the <img> resolves.
INSERT INTO Files (name, filename, employee, file_group, size, size_text)
SELECT 'e2e-leave-attachment', 'e2e-leave-attachment.png', -1, 'EmployeeLeaveAll', 95, '95 B'
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM Files) f WHERE f.name = 'e2e-leave-attachment');

DELETE FROM EmployeeLeaves WHERE id = 1840;
INSERT INTO EmployeeLeaves
  (id, employee, leave_type, leave_period, date_start, date_end, details, status, attachment)
SELECT 1840, @admin,
       (SELECT id FROM LeaveTypes WHERE name = 'Medical leave' LIMIT 1),
       (SELECT id FROM LeavePeriods WHERE YEAR(date_start) = YEAR(CURDATE()) LIMIT 1),
       DATE(CONCAT(YEAR(CURDATE()),'-06-01')), DATE(CONCAT(YEAR(CURDATE()),'-06-01')),
       'Seeded by e2e fixtures', 'Pending', 'e2e-leave-attachment';

-- ── Payroll employees (switch-back-data) ─────────────────────────────────────
-- The payroll_config bespoke view asserts "Payroll Employees (N)" with N >= 1.
-- The demo generator creates the Payroll row but leaves PayrollEmployees empty
-- (that list is normally filled when a payroll is processed in the UI), so the
-- roster has to be seeded directly.
DELETE FROM PayrollEmployees WHERE employee IN (@u1, @mgr, @admin);
-- pay_frequency here is an INT FK into PayFrequency (not the enum string that
-- EmployeeSalary.pay_frequency uses) — the two columns share a name and differ.
SET @freq = (SELECT id FROM PayFrequency WHERE name = 'Monthly' LIMIT 1);
INSERT INTO PayrollEmployees (employee, pay_frequency, currency)
VALUES (@u1, @freq, @cur), (@mgr, @freq, @cur), (@admin, @freq, @cur);

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

-- ── Performance reviews (performance-user-native, performance-native) ────────
-- The demo generator creates reviews for the employees it invents, so the accounts
-- the specs log in as own none and their "Self Assessments" tab renders empty.
-- Give user1 a pending self assessment and the admin a completed review to open.
-- Remove any demo-generated reviews for the e2e accounts first: the specs assert on
-- a SINGLE status tag, and the generator may have produced its own review for the
-- same employee, which makes that locator ambiguous under strict mode.
DELETE FROM ReviewFeedbacks WHERE review IN
  (SELECT id FROM (SELECT id FROM PerformanceReviews WHERE employee IN (@u1, @admin)) r);
DELETE FROM EmployeeGoals WHERE review IN
  (SELECT id FROM (SELECT id FROM PerformanceReviews WHERE employee IN (@u1, @admin)) r);
DELETE FROM PerformanceReviews WHERE employee IN (@u1, @admin);
INSERT INTO PerformanceReviews
  (name, employee, coordinator, attendees, form, status, review_date,
   review_period_start, review_period_end, self_assessment_due, notes)
VALUES
  (CONCAT('Self Assessment ', YEAR(CURDATE())), @u1, @mgr, @u1,
   (SELECT MIN(id) FROM ReviewTemplates), 'Pending',
   DATE_ADD(CURDATE(), INTERVAL 30 DAY),
   DATE(CONCAT(YEAR(CURDATE()),'-01-01')), DATE(CONCAT(YEAR(CURDATE()),'-12-31')),
   DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Seeded by e2e fixtures'),
  (CONCAT('Annual Review ', YEAR(CURDATE())), @admin, @mgr, @admin,
   (SELECT MIN(id) FROM ReviewTemplates), 'Completed',
   CURDATE(),
   DATE(CONCAT(YEAR(CURDATE()),'-01-01')), DATE(CONCAT(YEAR(CURDATE()),'-12-31')),
   CURDATE(), 'Seeded by e2e fixtures');

-- Goals hang off the review; performance-native asserts on the goal title.
SET @rev = (SELECT id FROM PerformanceReviews
             WHERE notes = 'Seeded by e2e fixtures' AND employee = @admin LIMIT 1);
DELETE FROM EmployeeGoals WHERE description = 'Seeded by e2e fixtures';
INSERT INTO EmployeeGoals (employee, review, status, title, description)
VALUES (@admin, @rev, 'Public', 'Goal 1', 'Seeded by e2e fixtures'),
       (@u1,    (SELECT id FROM PerformanceReviews
                  WHERE notes = 'Seeded by e2e fixtures' AND employee = @u1 LIMIT 1),
        'Public', 'Goal 1', 'Seeded by e2e fixtures');

-- ── Peer feedback (performance-native "review view has four tabs") ───────────
-- The Peer Feedback tab asserts on a "Feedback from …" card carrying a rating and a
-- percentage. Only SUBMITTED feedback is shown to a viewer, so status matters. This
-- is easy to miss: the demo generator produces feedback for the reviews IT creates,
-- so the tab looked populated on a database that had accumulated demo runs and empty
-- on a freshly-seeded one.
DELETE FROM ReviewFeedbacks WHERE review IN
  (SELECT id FROM (SELECT id FROM PerformanceReviews WHERE notes = 'Seeded by e2e fixtures') r);
INSERT INTO ReviewFeedbacks (employee, review, subject, form, status, dueon, rating)
SELECT @mgr, r.id, @admin, r.form, 'Submitted', CURDATE(), 51
  FROM (SELECT id, form FROM PerformanceReviews
         WHERE notes = 'Seeded by e2e fixtures' AND employee = @admin LIMIT 1) r;
INSERT INTO ReviewFeedbacks (employee, review, subject, form, status, dueon, rating)
SELECT @u1, r.id, @admin, r.form, 'Submitted', CURDATE(), 52
  FROM (SELECT id, form FROM PerformanceReviews
         WHERE notes = 'Seeded by e2e fixtures' AND employee = @admin LIMIT 1) r;

-- Normalise every OTHER feedback row to Submitted with a rating. The peer-feedback
-- tab renders "(Not completed yet)" and no rating widget for anything else, and the
-- spec opens whichever review is first in the list — not necessarily a seeded one.
UPDATE ReviewFeedbacks SET status = 'Submitted',
       rating = COALESCE(NULLIF(rating, 0), 55)
 WHERE status <> 'Submitted' OR rating IS NULL OR rating = 0;

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

-- ── Recruitment: job position + candidates ───────────────────────────────────
-- jobpositions-native names the record explicitly ("JO123 / Software Eng") and
-- derives the public apply URL from the code, so the code has to match exactly.
-- Note the Job table is empty in the dev database too, so these specs fail there as
-- well — seeding here makes the testing environment the reliable one.
-- Remove any demo-generated jobs: the spec acts on the FIRST job card and asserts
-- its code, so JO123 has to be the only one.
DELETE FROM Job WHERE code LIKE 'DEMO%';
INSERT INTO Job (title, code, shortDescription, description, status, display, closingDate)
SELECT 'Software Engineer', 'JO123', 'Seeded by e2e fixtures',
       'Seeded by e2e fixtures', 'Active', 'Yes',
       DATE_ADD(CURDATE(), INTERVAL 90 DAY)
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT * FROM Job) j WHERE j.code = 'JO123');
SET @job = (SELECT id FROM Job WHERE code = 'JO123' LIMIT 1);

-- One candidate per pipeline stage so every stage tab and every Board column has a
-- card, and the drag-drop test has something to move.
DELETE FROM Candidates WHERE notes = 'Seeded by e2e fixtures';
INSERT INTO Candidates (first_name, last_name, email, mobile_phone, hiringStage, jobId, notes, source)
SELECT c.fn, c.ln, c.em, '0700000000', c.st, @job, 'Seeded by e2e fixtures', 'Applied'
  FROM (
    SELECT 'Ada'   AS fn, 'Applicant'  AS ln, 'ada.applicant@example.com'  AS em, 1 AS st
    UNION ALL SELECT 'Ben','Screening','ben.screening@example.com', 2
    UNION ALL SELECT 'Cara','Resume','cara.resume@example.com', 3
    UNION ALL SELECT 'Dan','Interview','dan.interview@example.com', 4
    UNION ALL SELECT 'Eve','Second','eve.second@example.com', 5
    UNION ALL SELECT 'Finn','Final','finn.final@example.com', 6
    UNION ALL SELECT 'Gia','Offered','gia.offered@example.com', 7
    UNION ALL SELECT 'Hugo','Accepted','hugo.accepted@example.com', 8
    UNION ALL SELECT 'Iris','Declined','iris.declined@example.com', 9
  ) c;

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
SELECT CONCAT('  leave flags      casual_reason=',
              (SELECT reason_required FROM LeaveTypes WHERE name='Casual leave'),
              ' medical_attach=',
              (SELECT attachment_mandatory FROM LeaveTypes WHERE name='Medical leave'));
SELECT CONCAT('  salary rows      ', COUNT(*)) FROM EmployeeSalary;
SELECT CONCAT('  employee loans   ', COUNT(*)) FROM EmployeeCompanyLoans;
SELECT CONCAT('  training sess.   ', COUNT(*)) FROM TrainingSessions;
SELECT CONCAT('  enrolments       ', COUNT(*)) FROM EmployeeTrainingSessions;
SELECT CONCAT('  emp documents    ', COUNT(*)) FROM EmployeeDocuments;" 2>/dev/null
