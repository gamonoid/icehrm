<?php
// Hash-protected direct resource access (no login):
//   /app/secure/?resource=<resource_id>&hash=<secure_hash>
// Run from the app/ directory so config.php and its relative includes resolve.
chdir(dirname(__DIR__));
include ('config.php');
include (APP_BASE_PATH.'secure.php');
