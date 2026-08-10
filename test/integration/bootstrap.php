<?php
/**
 * Integration-test bootstrap — boots the FULL IceHRM application in a CLI context,
 * exactly as a web request would (all core modules + every extension registered),
 * so tests can enumerate the real model class map and exercise BaseService.
 *
 * It does NOT drop or reseed the database — the testing stack
 * (docker-compose-testing.yaml) already seeds the DB from docker/init.sql, and the
 * tests seed/clean only the throwaway fixtures they create.
 *
 * The app config is chosen in this order:
 *   1. $ICEHRM_TEST_CONFIG / ICEHRM_TEST_CONFIG env — explicit path
 *   2. /var/www/html/docker/testing/config/config.php  (the testing container)
 *   3. /var/www/html/docker/development/config/config.php (the dev container)
 *
 * Run from inside a container that has the app at /var/www/html, e.g.
 *   docker compose -f docker-compose-testing.yaml run --rm icehrm \
 *       php test/integration/security/EmployeeElementAccessTest.php
 */

error_reporting(E_ERROR | E_PARSE); // silence vendor deprecation noise on PHP 8.x

$APP_ROOT = getenv('ICEHRM_APP_ROOT') ?: '/var/www/html';

// server.includes.inc.php reads the current user from the session; in CLI there is
// no session, so back $_SESSION with a plain array that SessionUtils can read/write.
$_SESSION = array();

if (!defined('CLIENT_PATH')) {
    define('CLIENT_PATH', $APP_ROOT . '/core');
}

// The app config must load before config.base.php (it defines CLIENT_BASE_URL, the
// DB connection string, APP_BASE_PATH ...), mirroring app/index.php.
$configCandidates = array_filter(array(
    getenv('ICEHRM_TEST_CONFIG'),
    $APP_ROOT . '/docker/testing/config/config.php',
    $APP_ROOT . '/docker/development/config/config.php',
));
$appConfig = null;
foreach ($configCandidates as $c) {
    if ($c && file_exists($c)) { $appConfig = $c; break; }
}
if ($appConfig === null) {
    fwrite(STDERR, "bootstrap: no app config found (looked in: " . implode(', ', $configCandidates) . ")\n");
    exit(2);
}

chdir($APP_ROOT . '/app');
include $appConfig;

require APP_BASE_PATH . 'config.base.php';

// A user-module scope so initializeUserClasses() registers the userTables classes
// too (they are skipped under an admin scope). The model CLASS MAP is registered
// regardless of scope; this only affects which models are row-scoped on list.
if (!defined('MODULE_PATH')) {
    define('MODULE_PATH', 'modules/employees');
}

require APP_BASE_PATH . 'include.common.php';
require APP_BASE_PATH . 'server.includes.inc.php';

require __DIR__ . '/lib/TestContext.php';
require __DIR__ . '/lib/VerbAccessSweep.php';
