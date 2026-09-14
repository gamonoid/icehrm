<?php
/**
 * Integration test — mass-assignment guard on the generic save path.
 * =================================================================
 *
 * BaseService::addElement copies every request column that matches a DB column, so a
 * model that grants an owner save/add lets that owner over-post sensitive columns.
 * getProtectedFields($user) drops those from the copy. This pins (a) the per-model
 * decision matrix and (b) end-to-end: an employee cannot self-approve their own
 * overtime by posting status='Approved' through a=save, while an Admin still can.
 *
 * Run:  php test/integration/security/MassAssignmentGuardTest.php
 */

require __DIR__ . '/../bootstrap.php';

use Classes\BaseService;
use Classes\IceResponse;

$ctx = new TestContext();
$bs  = BaseService::getInstance();

$pass = 0; $fail = 0; $failures = array();
function verdict($desc, $cond)
{
    global $pass, $fail, $failures;
    if ($cond) { printf("  PASS  %s\n", $desc); $pass++; }
    else { printf("  FAIL  %s\n", $desc); $fail++; $failures[] = $desc; }
}

function mkUser($level)
{
    $u = new stdClass();
    $u->id = 42; $u->email = 'x@example.com'; $u->user_level = $level;
    return $u;
}

fwrite(STDOUT, "Mass-assignment guard — getProtectedFields + end-to-end\n\n");

// ---- (a) decision matrix ----------------------------------------------------

$overtime = new \Overtime\Common\Model\EmployeeOvertime();
verdict("EmployeeOvertime: Employee actor protects 'status'",
    in_array('status', $overtime->getProtectedFields(mkUser('Employee')), true));
verdict("EmployeeOvertime: Manager actor protects 'status'",
    in_array('status', $overtime->getProtectedFields(mkUser('Manager')), true));
verdict("EmployeeOvertime: Admin actor protects nothing",
    $overtime->getProtectedFields(mkUser('Admin')) === array());

// Employee: protection applies only to a non-admin editing their OWN record.
list($mgr, $sub, $other) = $ctx->ensureManagerFixture();
$ctx->actAsRealUser($sub, 'Employee');
$ownEmp = new \Employees\Common\Model\Employee();
$ownEmp->Load('id = ?', array($sub));
$ownProtected = $ownEmp->getProtectedFields($bs->getCurrentUser());
verdict("Employee: self-edit protects supervisor",
    in_array('supervisor', $ownProtected, true));
verdict("Employee: self-edit protects pay_grade/job_title/status",
    in_array('pay_grade', $ownProtected, true)
    && in_array('job_title', $ownProtected, true)
    && in_array('status', $ownProtected, true));

$otherEmp = new \Employees\Common\Model\Employee();
$otherEmp->Load('id = ?', array($other));
verdict("Employee: editing SOMEONE ELSE's record is not self-protected here",
    $otherEmp->getProtectedFields($bs->getCurrentUser()) === array());

$ctx->actAsRealUser($mgr, 'Admin');
verdict("Employee: Admin actor protects nothing (own record)",
    $ownEmp->getProtectedFields($bs->getCurrentUser()) === array());

// ---- (b) end-to-end through addElement: self-approval is blocked ------------

// EmployeeOvertime.category is NOT NULL with an FK to OvertimeCategories, and
// init.sql seeds no categories (the demo generator creates them), so borrow an
// existing one rather than letting seedRow invent an id that violates the FK.
$catRow = $ctx->tableExists('OvertimeCategories')
    ? $ctx->query('SELECT id FROM OvertimeCategories LIMIT 1')
    : array();

if (!empty($catRow)) {
    $overtimeId = $ctx->seedRow('EmployeeOvertime', array(
        'employee'   => $sub,
        'status'     => 'Pending',
        'category'   => $catRow[0]['id'],
        'start_time' => '2020-02-03 09:00:00',
        'end_time'   => '2020-02-03 11:00:00',
    ));

    // Employee (owner) tries to self-approve via a generic save.
    $ctx->actAsRealUser($sub, 'Employee');
    try {
        $bs->addElement('EmployeeOvertime', array(
            'id' => $overtimeId, 't' => 'EmployeeOvertime', 'a' => 'save', 'status' => 'Approved',
        ));
    } catch (\Throwable $e) { /* ACL/validate paths are not what we assert here */ }
    $check = $ctx->query('SELECT status FROM EmployeeOvertime WHERE id = ?', array($overtimeId));
    $statusAfterEmployee = !empty($check) ? $check[0]['status'] : null;
    verdict("Employee CANNOT self-approve own overtime via a=save (status stays Pending)",
        $statusAfterEmployee === 'Pending');

    // Admin may set it.
    $ctx->actAsRealUser($mgr, 'Admin');
    try {
        $bs->addElement('EmployeeOvertime', array(
            'id' => $overtimeId, 't' => 'EmployeeOvertime', 'a' => 'save', 'status' => 'Approved',
        ));
    } catch (\Throwable $e) { /* ignore */ }
    $check2 = $ctx->query('SELECT status FROM EmployeeOvertime WHERE id = ?', array($overtimeId));
    $statusAfterAdmin = !empty($check2) ? $check2[0]['status'] : null;
    verdict("Admin CAN set overtime status via a=save (status -> Approved)",
        $statusAfterAdmin === 'Approved');
} else {
    fwrite(STDOUT, "  (skipped end-to-end: no OvertimeCategories row to attach to)\n");
}

$ctx->cleanup();

echo "\n" . str_repeat('=', 64) . "\n";
printf("MASS ASSIGNMENT GUARD  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
