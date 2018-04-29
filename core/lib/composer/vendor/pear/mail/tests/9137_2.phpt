--TEST--
Mail: Test for bug #9137, take 2
--FILE--
<?php

require_once dirname(__FILE__) . '/../Mail/RFC822.php';
require_once 'PEAR.php';

$addresses = array(
    array('raw' => '"John Doe" <test@example.com>'),
    array('raw' => '"John Doe' . chr(92) . '" <test@example.com>'),
    array('raw' => '"John Doe' . chr(92) . chr(92) . '" <test@example.com>'),
    array('raw' => '"John Doe' . chr(92) . chr(92) . chr(92) . '" <test@example.com>'),
    array('raw' => '"John Doe' . chr(92) . chr(92) . chr(92) . chr(92) . '" <test@example.com>'),
    array('raw' => '"John Doe <test@example.com>'),
);

for ($i = 0; $i < count($addresses); $i++) {
    // construct the address
    $address = $addresses[$i]['raw'];
    $parsedAddresses = Mail_RFC822::parseAddressList($address);
    if (PEAR::isError($parsedAddresses)) {
        echo $address." :: Failed to validate\n";
    } else {
        echo $address." :: Parsed\n";
    }
}

--EXPECT--
"John Doe" <test@example.com> :: Parsed
"John Doe\" <test@example.com> :: Failed to validate
"John Doe\\" <test@example.com> :: Parsed
"John Doe\\\" <test@example.com> :: Failed to validate
"John Doe\\\\" <test@example.com> :: Parsed
"John Doe <test@example.com> :: Failed to validate
