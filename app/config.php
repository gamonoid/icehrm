<?php
$configFile = __DIR__.'/'.getenv( 'ICE_CONFIG_FILE' );
if ( $configFile ) {
    require_once $configFile;
} else {
    require_once 'config-dev.php';
}
