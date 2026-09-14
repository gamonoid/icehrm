<?php
/**
 * Integration test — module-level access logic (the a=ca authorization gate).
 * ==========================================================================
 *
 * The custom-action dispatcher (core/service.php, a=ca) reaches a module's action
 * manager from any logged-in session; the module's meta.json user_levels only gate
 * the menu. The dispatcher fix enforces the module's declared user_levels via
 * ModuleAccessService::userMayAccessModuleLevels() before invoking the action.
 *
 * The end-to-end block is covered by e2e/tests/security-ca-authorization.spec.js
 * (it needs a real HTTP request through service.php). THIS test pins the decision
 * FUNCTION directly — every branch of the level/role logic — so a regression in the
 * rule itself is caught in milliseconds, independent of the web layer.
 *
 * Run:  php test/integration/security/ModuleAccessTest.php
 * Exit 0 = all cases correct; 1 = at least one wrong verdict.
 */

require __DIR__ . '/../bootstrap.php';

use Classes\ModuleAccessService;

/** Minimal user stand-in: the method reads ->user_level and ->user_roles. */
function moduleAccessUser($level, $roles = null)
{
    $u = new \Users\Common\Model\User();
    $u->user_level = $level;
    $u->user_roles = $roles === null ? null : json_encode($roles);
    return $u;
}

$svc = ModuleAccessService::getInstance();
$pass = 0;
$fail = 0;
$failures = array();

/**
 * @param string      $desc     what the case checks
 * @param array|null  $levels   module user_levels
 * @param array|null  $roles    module user_roles
 * @param object      $user     the requesting user
 * @param bool        $expected the correct verdict
 */
function expectAccess($desc, $levels, $roles, $user, $expected)
{
    global $svc, $pass, $fail, $failures;
    $got = $svc->userMayAccessModuleLevels($levels, $roles, $user);
    if ($got === $expected) {
        printf("  PASS  %s\n", $desc);
        $pass++;
    } else {
        printf("  FAIL  %s  (expected %s, got %s)\n", $desc, var_export($expected, true), var_export($got, true));
        $fail++;
        $failures[] = $desc;
    }
}

fwrite(STDOUT, "ModuleAccessService::userMayAccessModuleLevels — decision matrix\n\n");

// --- The core level gate ------------------------------------------------------
expectAccess(
    'Admin reaches an Admin-only module',
    array('Admin'), array(), moduleAccessUser('Admin'), true
);
expectAccess(
    'Manager is DENIED an Admin-only module (the a=ca hole)',
    array('Admin'), array(), moduleAccessUser('Manager'), false
);
expectAccess(
    'Employee is DENIED an Admin-only module (the demonstrated leak)',
    array('Admin'), array(), moduleAccessUser('Employee'), false
);
expectAccess(
    'Manager reaches an Admin+Manager module',
    array('Admin', 'Manager'), array(), moduleAccessUser('Manager'), true
);
expectAccess(
    'Employee is DENIED an Admin+Manager module',
    array('Admin', 'Manager'), array(), moduleAccessUser('Employee'), false
);
expectAccess(
    'Employee reaches a module that lists Employee',
    array('Admin', 'Manager', 'Employee'), array(), moduleAccessUser('Employee'), true
);

// --- Fail-closed on missing/empty declarations --------------------------------
expectAccess(
    'empty user_levels denies everyone (fail closed) — even Admin',
    array(), array(), moduleAccessUser('Admin'), false
);
expectAccess(
    'null user_levels denies everyone (fail closed)',
    null, null, moduleAccessUser('Admin'), false
);

// --- user_roles as an alternate grant -----------------------------------------
expectAccess(
    'a user whose role matches module user_roles is allowed, despite level mismatch',
    array('Admin'), array('hr_extra'), moduleAccessUser('Employee', array('hr_extra')), true
);
expectAccess(
    'a non-matching role does not grant access',
    array('Admin'), array('hr_extra'), moduleAccessUser('Employee', array('something_else')), false
);

// --- Restricted user levels require BOTH base level AND a matching role --------
expectAccess(
    'Restricted Manager with no roles is denied',
    array('Admin', 'Manager'), array('rm_role'), moduleAccessUser('Restricted Manager', null), false
);
expectAccess(
    'Restricted Manager whose base level is not permitted is denied',
    array('Admin'), array('rm_role'), moduleAccessUser('Restricted Manager', array('rm_role')), false
);
expectAccess(
    'Restricted Manager with permitted base level AND a matching role is allowed',
    array('Admin', 'Manager'), array('rm_role'), moduleAccessUser('Restricted Manager', array('rm_role')), true
);

echo "\n" . str_repeat('=', 64) . "\n";
printf("MODULE ACCESS LOGIC  PASS=%d  FAIL=%d\n", $pass, $fail);
if ($failures) {
    echo "FAILURES:\n  " . implode("\n  ", $failures) . "\n";
}
echo str_repeat('=', 64) . "\n";

exit($fail > 0 ? 1 : 0);
