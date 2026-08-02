<?php
/**
 * Standalone regression guard for Classes\MenuService menu filtering
 * (SPA migration Phase 0 — see docs/SPA_MIGRATION_PLAN.md).
 *
 * Pure-logic test: no DB, no HTTP. Asserts the user-level / user-role /
 * blacklist visibility rules and the empty-group drop, which MUST stay
 * identical to the legacy filter in core/modules.php (~lines 570-633).
 *
 * Run:  php test/appshell/menu_filter_test.php   (exit 0 = pass)
 */

error_reporting(E_ERROR | E_PARSE); // silence vendor deprecation noise

require __DIR__ . '/../../core/lib/composer/vendor/autoload.php';

use Classes\MenuService;

function u($level, $roles = null)
{
    $o = new stdClass();
    $o->user_level = $level;
    $o->user_roles = $roles ? json_encode($roles) : null;
    return $o;
}
function item($name, $levels, $roles = [], $black = [])
{
    return [
        'name' => $name, 'label' => $name,
        'user_levels' => $levels, 'user_roles' => $roles, 'user_roles_blacklist' => $black,
    ];
}
function labels($tree)
{
    $o = [];
    foreach ($tree as $g) {
        $o[$g['name']] = array_map(function ($i) {
            return $i['label'];
        }, array_values($g['menu']));
    }
    return $o;
}

$admin = [
    ['name' => 'Admin', 'menu' => [ item('Employees', ['Admin', 'Manager']), item('Settings', ['Admin']) ]],
    ['name' => 'Reports', 'menu' => [ item('PayReport', ['Admin'], ['Report Manager']) ]],
    ['name' => 'Secret', 'menu' => [ item('SecretThing', ['Admin'], [], ['Blacklisted Role']) ]],
];
$user = [ ['name' => 'About You', 'menu' => [ item('Profile', ['Admin', 'Manager', 'Employee']) ]] ];

$svc = MenuService::getInstance();
$svc->setRawMenus($admin, $user, ['Admin' => 'fa-cog']);

$pass = 0; $fail = 0;
function check($desc, $got, $want)
{
    global $pass, $fail;
    if (json_encode($got) === json_encode($want)) {
        $pass++;
        echo "PASS  $desc\n";
    } else {
        $fail++;
        echo "FAIL  $desc\n   got : " . json_encode($got) . "\n   want: " . json_encode($want) . "\n";
    }
}

$r = $svc->getMenuForUser(u('Admin'));
check('Admin sees all admin groups', labels($r['adminModules']), ['Admin' => ['Employees', 'Settings'], 'Reports' => ['PayReport'], 'Secret' => ['SecretThing']]);
check('Admin sees user Profile', labels($r['userModules']), ['About You' => ['Profile']]);

$r = $svc->getMenuForUser(u('Manager'));
check('Manager: only Employees; role/blacklist groups dropped', labels($r['adminModules']), ['Admin' => ['Employees']]);

$r = $svc->getMenuForUser(u('Employee', ['Report Manager']));
check('Employee+role sees PayReport via role only', labels($r['adminModules']), ['Reports' => ['PayReport']]);
check('Employee sees Profile', labels($r['userModules']), ['About You' => ['Profile']]);

$r = $svc->getMenuForUser(u('Admin', ['Blacklisted Role']));
$secret = array_filter($r['adminModules'], function ($g) {
    return $g['name'] === 'Secret';
});
check('Blacklist hides item even for Admin', empty($secret), true);

// Regression guard for the legacy "Marketplace visible in Employee View" bug
// (caused by a hardcoded hide-list in user-view-switch.js drifting from the
// real user_levels). MenuService is data-driven, so an admin-only extension
// MUST never appear for a non-admin. Marketplace's real config: user_levels=["Admin"].
$mkt = [['name' => 'Marketplace', 'menu' => [item('Extensions', ['Admin'])]]];
$svc->setRawMenus($mkt, [], []);
$adminGroups = labels($svc->getMenuForUser(u('Admin'))['adminModules']);
$mgrGroups = labels($svc->getMenuForUser(u('Manager'))['adminModules']);
$empGroups = labels($svc->getMenuForUser(u('Employee'))['adminModules']);
check('Admin-only extension visible to Admin', $adminGroups, ['Marketplace' => ['Extensions']]);
check('Admin-only extension HIDDEN from Manager', $mgrGroups, []);
check('Admin-only extension HIDDEN from Employee', $empGroups, []);

// Admin/Employee view toggle: an Admin switching to Employee View must see EXACTLY
// the employee menu. Mirrors the real data: an admin-only extension (Marketplace)
// vs an admin-group module that is employee-accessible (candidates, user_levels
// includes Employee) within the SAME tree.
function viewLabels($groups)
{
    $o = [];
    foreach ($groups as $g) {
        $o[$g['name']] = array_map(function ($it) {
            return $it['label'];
        }, $g['items']);
    }
    return $o;
}
$rawAdmin2 = [
    ['name' => 'Marketplace', 'menu' => [item('Extensions', ['Admin'])]],
    ['name' => 'Recruitment', 'menu' => [
        item('Candidates', ['Admin', 'Manager', 'Employee']),
        item('Job Positions', ['Admin', 'Manager']),
    ]],
];
$rawUser2 = [['name' => 'About You', 'menu' => [item('Profile', ['Admin', 'Manager', 'Employee'])]]];
$svc->setRawMenus($rawAdmin2, $rawUser2, []);
$views = $svc->getViewMenus(u('Admin'));
check('Admin view = full admin menu', viewLabels($views['admin']), [
    'Marketplace' => ['Extensions'],
    'Recruitment' => ['Candidates', 'Job Positions'],
]);
check('Employee view hides Marketplace + admin-only items, keeps employee-accessible', viewLabels($views['employee']), [
    'Recruitment' => ['Candidates'],
    'About You' => ['Profile'],
]);

echo "\n$pass passed, $fail failed\n";
exit($fail > 0 ? 1 : 0);
