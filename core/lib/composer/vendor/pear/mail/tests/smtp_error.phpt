--TEST--
Mail: SMTP Error Reporting
--SKIPIF--
<?php

require_once 'PEAR/Registry.php';
$registry = new PEAR_Registry();

if (!$registry->packageExists('Net_SMTP')) die("skip\n");
--FILE--
<?php
require_once 'Mail.php';

/* Reference a bogus SMTP server address to guarantee a connection failure. */
$params = array('host' => 'bogus.host.tld');

/* Create our SMTP-based mailer object. */
$mailer = Mail::factory('smtp', $params);

/* Attempt to send an empty message in order to trigger an error. */
$e = $mailer->send(array(), array(), '');
if (is_a($e, 'PEAR_Error')) {
     $err = $e->getMessage();
     if (preg_match('/Failed to connect to bogus.host.tld:25 \[SMTP: Failed to connect socket:.*/i', $err)) {
        echo "OK";
     }
}

--EXPECT--
OK