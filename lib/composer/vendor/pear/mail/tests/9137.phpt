--TEST--
Mail: Test for bug #9137
--FILE--
<?php

require_once dirname(__FILE__) . '/../Mail/RFC822.php';
require_once 'PEAR.php';

$addresses = array(
    array('name' => 'John Doe', 'email' => 'test@example.com'),
    array('name' => 'John Doe\\', 'email' => 'test@example.com'),
    array('name' => 'John "Doe', 'email' => 'test@example.com'),
    array('name' => 'John "Doe\\', 'email' => 'test@example.com'),
);

for ($i = 0; $i < count($addresses); $i++) {
    // construct the address
    $address = "\"" . addslashes($addresses[$i]['name']) . "\" ".
        "<".$addresses[$i]['email'].">";

    $parsedAddresses = Mail_RFC822::parseAddressList($address);
    if (is_a($parsedAddresses, 'PEAR_Error')) {
        echo $address." :: Failed to validate\n";
    } else {
        echo $address." :: Parsed\n";
    }
}

--EXPECT--
"John Doe" <test@example.com> :: Parsed
"John Doe\\" <test@example.com> :: Parsed
"John \"Doe" <test@example.com> :: Parsed
"John \"Doe\\" <test@example.com> :: Parsed
