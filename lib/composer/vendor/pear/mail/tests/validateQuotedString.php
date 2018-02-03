<?php
require_once '../Mail/RFC822.php';
$address_string = '"Joe Doe \(from Somewhere\)" <doe@example.com>, postmaster@example.com, root';
// $address_string = "Joe Doe from Somewhere <doe@example.com>, postmaster@example.com, root";
echo $address_string . "\n";

$address_array = Mail_RFC822::parseAddressList($address_string, "example.com");
if (!is_array($address_array) || count($address_array) < 1) {
    die("something is wrong\n");
}

foreach ($address_array as $val) {
    echo "mailbox : " . $val->mailbox . "\n";
    echo "host    : " . $val->host . "\n";
    echo "personal: " . $val->personal . "\n";
}
print_r($address_array);
