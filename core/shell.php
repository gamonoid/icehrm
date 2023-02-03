<?php
include (__DIR__."/../app/config.php");
include (__DIR__."/../core/config.base.php");
include (__DIR__."/../core/include.common.php");
include(__DIR__."/../core/server.includes.inc.php");

$user = new \Users\Common\Model\User();
$user->Load('username = ?', ['admin']);
\Classes\BaseService::getInstance()->setCurrentUser($user);
\Utils\SessionUtils::saveSessionObject('user', $user);
