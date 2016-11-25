<?php
if(php_sapi_name() != 'cli'){
    exit();
}

include ('config.php');
include (APP_BASE_PATH.'crons/cron.php');