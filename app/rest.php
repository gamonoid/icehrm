<?php
include ('config.php');
if (isset($_REQUEST['method']) && isset($_REQUEST['url'])) {
    include (APP_BASE_PATH.'api.php');
} else {
    include (APP_BASE_PATH.'rest.php');
}
