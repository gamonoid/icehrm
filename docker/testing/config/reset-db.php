<?php

$output = shell_exec('cd /var/www/html/core/robo; php robo.phar reset:db test; php robo.phar create:tables test; php robo.phar migrate:all test; php robo.phar execute:fixtures test;');
echo "<pre>$output</pre>";
