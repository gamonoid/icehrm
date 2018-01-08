--TEST--
Net_SMTP: Basic Functionality
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

if (PEAR::isError($smtp->mailFrom(TEST_FROM))) {
    die('Unable to set sender to <' . TEST_FROM . ">\n");
}

if (PEAR::isError($res = $smtp->rcptTo(TEST_TO))) {
    die('Unable to add recipient <' . TEST_TO . '>: ' .
        $res->getMessage() . "\n");
}

$headers = 'Subject: ' . TEST_SUBJECT;
if (PEAR::isError($smtp->data(TEST_BODY, $headers))) {
    die("Unable to send data\n");
}

$smtp->disconnect();

echo 'Success!';

--EXPECT--
Success!
