<?php
// Developer REPL bootstrap (psysh) — see .config/psysh/config.php and private/tools.md.
// It logs in as admin to give an interactive PHP shell full context, so it must NEVER
// run over HTTP. Refuse any non-CLI (web) request to prevent an unauthenticated
// admin-session bypass (security finding 2.3).
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
include (__DIR__."/../app/config.php");
include (__DIR__."/../core/config.base.php");
include (__DIR__."/../core/include.common.php");
include(__DIR__."/../core/server.includes.inc.php");

// Include all extensions (admin and user) from the free extensions/ root, plus
// the paid extensions-pro/ root only on a Pro/Cloud build.
$extensionRoots = array(__DIR__."/../extensions/");
if (function_exists('iceProExtensionsEnabled') && iceProExtensionsEnabled()) {
    $extensionRoots[] = __DIR__."/../extensions-pro/";
}
foreach ($extensionRoots as $extensionsPath) {
    if (!is_dir($extensionsPath)) {
        continue;
    }
    $extensionDirs = scandir($extensionsPath);
    foreach ($extensionDirs as $extensionDir) {
        if ($extensionDir === '.' || $extensionDir === '..') {
            continue;
        }
        $extensionBasePath = $extensionsPath . $extensionDir;
        if (!is_dir($extensionBasePath)) {
            continue;
        }

        // Include admin extension
        $adminFile = $extensionBasePath . '/admin/' . $extensionDir . '.php';
        if (file_exists($adminFile)) {
            include_once($adminFile);
        }

        // Include user extension
        $userFile = $extensionBasePath . '/user/' . $extensionDir . '.php';
        if (file_exists($userFile)) {
            include_once($userFile);
        }
    }
}

$user = new \Users\Common\Model\User();
$user->Load('username = ?', ['admin']);
\Classes\BaseService::getInstance()->setCurrentUser($user);
\Utils\SessionUtils::saveSessionObject('user', $user);
