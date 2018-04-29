<?php
include (dirname(__FILE__)."/src/Utils/SessionUtils.php");
\Utils\SessionUtils::unsetClientSession();
$user = null;
header("Location:".CLIENT_BASE_URL."login.php?login=no");
