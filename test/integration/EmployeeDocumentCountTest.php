<?php
/** Employee-document counts must accept the same query forms as BaseModel. */
require __DIR__ . '/bootstrap.php';

use Classes\BaseService;
use Documents\Common\Model\EmployeeDocumentFinderProxy;

$ctx = new TestContext();
$failures = 0;
$checks = 0;
$check = function ($label, $expected, $callback) use (&$failures, &$checks) {
    $checks++;
    try {
        $actual = $callback();
        if ($actual === $expected) {
            echo "PASS $label\n";
            return;
        }
        echo "FAIL $label: expected $expected, got " . var_export($actual, true) . "\n";
    } catch (\Throwable $e) {
        echo "FAIL $label: " . $e->getMessage() . "\n";
    }
    $failures++;
};

try {
    list($manager, $employee, $other) = $ctx->ensureManagerFixture();
    $shared = $ctx->seedRow('Documents', array('share_with_employee' => 'Yes'));
    $private = $ctx->seedRow('Documents', array('share_with_employee' => 'No'));
    foreach (array(
        array($shared, 'Owner', 0),
        array($shared, 'Owner Only', 0),
        array($shared, 'Manager', 0),
        array($shared, 'Owner', 1),
        array($private, 'Owner', 0),
    ) as $row) {
        $ctx->seedRow('EmployeeDocuments', array(
            'employee' => $employee,
            'document' => $row[0],
            'visible_to' => $row[1],
            'hidden' => $row[2],
        ));
    }

    $queries = array(
        'bare predicate' => 'employee = ?',
        'compound predicate' => '1 = 1 AND employee = ?',
        'leading whitespace' => '  employee = ?',
        'leading AND' => ' AND employee = ?',
        'lowercase AND' => '  and employee = ?',
        'ordered predicate' => 'employee = ? ORDER BY id',
        'limited predicate' => 'employee = ? LIMIT 1',
    );
    foreach (array(
        array('Admin', $other, false, 4),
        array('Employee', $employee, false, 2),
        array('Manager', $employee, false, 2),
        array('Manager', $manager, true, 3),
    ) as $scenario) {
        list($role, $actor, $team, $expected) = $scenario;
        $ctx->actAsRealUser($actor, $role);
        $finder = new EmployeeDocumentFinderProxy();
        $finder->setIsSubOrdinateQuery($team);
        $unfiltered = $finder->getTotalCount('', array());
        foreach (array(' ', 'ORDER BY id', 'limit 1') as $suffix) {
            $check("$role suffix '$suffix'", $unfiltered, function () use ($finder, $suffix) {
                return $finder->getTotalCount($suffix, array());
            });
        }
        foreach ($queries as $label => $query) {
            $check("$role team=" . (int) $team . " $label", $expected, function () use (
                $finder, $query, $employee
            ) {
                return $finder->getTotalCount($query, array($employee));
            });
        }
    }

    // Exercise the real service caller, which supplies a bare owner predicate.
    foreach (array(
        array('Employee', $employee, false, 2),
        array('Manager', $manager, true, 3),
        array('Employee', $other, false, 0),
    ) as $scenario) {
        list($role, $actor, $team, $expected) = $scenario;
        $ctx->actAsRealUser($actor, $role);
        $check("$role service count team=" . (int) $team, $expected, function () use ($team) {
            return BaseService::getInstance()->getData(
                'EmployeeDocument', null, null, null, null, null, '', $team, false, array(), true
            );
        });
    }
} finally {
    $ctx->cleanup();
}

echo "$checks checks, $failures failures\n";
exit($failures ? 1 : 0);
