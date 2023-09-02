--TEST--
Mail_RFC822::parseAddressList invalid periods in mail address
--FILE--
<?php
require "Mail/RFC822.php";

$result[] = Mail_RFC822::parseAddressList('.name@example.com');
$result[] = Mail_RFC822::parseAddressList('name.@example.com');
$result[] = Mail_RFC822::parseAddressList('name..name@example.com');

foreach ($result as $r) {
    if (is_a($r, 'PEAR_Error')) {
        echo "OK\n";
    }
}
--EXPECT--
OK
OK
OK
