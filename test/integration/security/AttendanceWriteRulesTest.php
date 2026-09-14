<?php
/**
 * Integration test — attendance write rules on the generic save path.
 * ==================================================================
 *
 * Attendance grants the owner add/save on their own rows, and
 * BaseService::addElement copies every matching column. Three consequences were
 * exploitable and are pinned here:
 *
 *  1. Re-parenting: on UPDATE the owner came straight from the request, so an
 *     employee could move a row they own onto a colleague
 *     (a=add&t=Attendance&id=<own>&employee=<victim>). addElement now restores the
 *     persisted owner for non-Admin callers — this is SYSTEMIC, not attendance-only.
 *  2. Forged evidence: in_ip / map_lat / map_lng are captured server-side by the
 *     punch flow; an employee could set them by hand. Now in getProtectedFields().
 *  3. Business rules (single day, in < out, no overlap) lived only in savePunch, so
 *     a=add bypassed them. Now enforced in Attendance::validateSave().
 *
 * Run:  php test/integration/security/AttendanceWriteRulesTest.php
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

if (!$ctx->tableExists('Attendance')) {
    fwrite(STDOUT, "SKIP — Attendance table not present\n");
    exit(2);
}

list($mgr, $sub, $other) = $ctx->ensureManagerFixture();
$model = new \Attendance\Common\Model\Attendance();

fwrite(STDOUT, "Attendance write rules (protected fields, invariants, re-parenting)\n\n");

// --- 2. Protected evidence fields -----------------------------------------
$ctx->actAsRealUser($sub, 'Employee');
$protected = $model->getProtectedFields($bs->getCurrentUser());
verdict('Employee cannot set in_ip', in_array('in_ip', $protected, true));
verdict('Employee cannot set map_lat/map_lng',
    in_array('map_lat', $protected, true) && in_array('map_lng', $protected, true));

$ctx->actAsRealUser($mgr, 'Admin');
verdict('Admin may still set the evidence fields',
    $model->getProtectedFields($bs->getCurrentUser()) === array());

// --- 3. validateSave invariants -------------------------------------------
$ctx->actAsRealUser($sub, 'Employee');
function checkSave($fields)
{
    $a = new \Attendance\Common\Model\Attendance();
    foreach ($fields as $k => $v) { $a->$k = $v; }
    return $a->validateSave($a);
}
// Use a far-future date so the fixture's demo rows cannot collide.
$d = '2031-09-04';
verdict('multi-day entry is rejected',
    checkSave(array('employee' => $sub, 'in_time' => "$d 22:00:00", 'out_time' => '2031-09-05 06:00:00'))
        ->getStatus() === IceResponse::ERROR);
verdict('out_time before in_time is rejected',
    checkSave(array('employee' => $sub, 'in_time' => "$d 17:00:00", 'out_time' => "$d 09:00:00"))
        ->getStatus() === IceResponse::ERROR);
verdict('a normal single-day entry is accepted',
    checkSave(array('employee' => $sub, 'in_time' => "$d 09:00:00", 'out_time' => "$d 12:00:00"))
        ->getStatus() === IceResponse::SUCCESS);

// Overlap: seed one row, then a colliding one must be refused.
$seeded = $ctx->seedRow('Attendance', array(
    'employee' => $sub, 'in_time' => "$d 09:00:00", 'out_time' => "$d 12:00:00",
));
verdict('overlapping entry is rejected',
    checkSave(array('employee' => $sub, 'in_time' => "$d 10:00:00", 'out_time' => "$d 14:00:00"))
        ->getStatus() === IceResponse::ERROR);
verdict('adjacent non-overlapping entry is accepted',
    checkSave(array('employee' => $sub, 'in_time' => "$d 12:30:00", 'out_time' => "$d 17:00:00"))
        ->getStatus() === IceResponse::SUCCESS);

// --- 1. Re-parenting on update (end-to-end through addElement) -------------
$ctx->actAsRealUser($sub, 'Employee');
try {
    $bs->addElement('Attendance', array(
        'id' => $seeded, 't' => 'Attendance', 'a' => 'add',
        'employee' => $other, 'note' => 'reparent-attempt',
    ));
} catch (\Throwable $e) { /* ACL paths are not what this asserts */ }
$row = $ctx->query('SELECT employee FROM Attendance WHERE id = ?', array($seeded));
verdict('Employee CANNOT re-parent their row onto another employee',
    !empty($row) && (string) $row[0]['employee'] === (string) $sub);

$ctx->cleanup();

echo "\n" . str_repeat('=', 64) . "\n";
printf("ATTENDANCE WRITE RULES  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
