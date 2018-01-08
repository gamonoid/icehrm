--TEST--
Net_SMTP: SMTP Authentication
--SKIPIF--
<?php if (!@include('config.php')) die("skip\n");
--FILE--
<?php

require_once 'Net/SMTP.php';
require_once 'config.php';

if (! ($smtp = new Net_SMTP(TEST_HOSTNAME, TEST_PORT, TEST_LOCALHOST))) {
	die("Unable to instantiate Net_SMTP object\n");
}

if (PEAR::isError($e = $smtp->connect())) {
	die($e->getMessage() . "\n");
}

if (PEAR::isError($e = $smtp->auth(TEST_AUTH_USER, TEST_AUTH_PASS))) {
	die("Authentication failure\n");
}

$smtp->disconnect();

echo 'Success!';

--EXPECT--
Success!
