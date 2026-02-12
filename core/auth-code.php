<?php
include("config.base.php");
include("include.common.php");
include("server.includes.inc.php");

\Classes\ConnectionService::handleOAuthCode($_REQUEST['auth_code']);
