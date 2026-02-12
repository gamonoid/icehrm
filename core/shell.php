<?php
include (__DIR__."/../app/config.php");
include (__DIR__."/../core/config.base.php");
include (__DIR__."/../core/include.common.php");
include(__DIR__."/../core/server.includes.inc.php");

// Include all extensions (admin and user)
$extensionsPath = __DIR__."/../extensions/";
if (is_dir($extensionsPath)) {
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
