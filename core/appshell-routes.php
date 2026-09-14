<?php
/**
 * App-shell REST routes (SPA migration Phase 0).
 *
 * Included by core/api-rest.php and core/api-url-based.php after REST_API_PATH is
 * defined and module managers have registered their endpoints. Registers the
 * read-only endpoints that feed the React app shell. See docs/SPA_MIGRATION_PLAN.md.
 */

\Classes\Macaw::get(REST_API_PATH . 'appshell/menu', function () {
    (new \Classes\AppShellRestEndPoint())->process('getMenu');
});

\Classes\Macaw::get(REST_API_PATH . 'appshell/bootstrap', function () {
    (new \Classes\AppShellRestEndPoint())->process('getBootstrap');
});

\Classes\Macaw::get(REST_API_PATH . 'appshell/permissions', function () {
    (new \Classes\AppShellRestEndPoint())->process('getPermissions');
});

\Classes\Macaw::get(REST_API_PATH . 'appshell/extensions', function () {
    (new \Classes\AppShellRestEndPoint())->process('getExtensions');
});

\Classes\Macaw::get(REST_API_PATH . 'appshell/dashboard', function () {
    (new \Classes\AppShellRestEndPoint())->process('getDashboard');
});

\Classes\Macaw::get(REST_API_PATH . 'appshell/employee-dashboard', function () {
    (new \Classes\AppShellRestEndPoint())->process('getEmployeeDashboard');
});

\Classes\Macaw::get(REST_API_PATH . 'appshell/news', function () {
    (new \Classes\AppShellRestEndPoint())->process('getNews');
});

\Classes\Macaw::get(REST_API_PATH . 'appshell/module-context', function () {
    (new \Classes\AppShellRestEndPoint())->process('getModuleContext');
});

\Classes\Macaw::get(REST_API_PATH . 'appshell/org-structure', function () {
    (new \Classes\AppShellRestEndPoint())->process('getOrgStructure');
});
