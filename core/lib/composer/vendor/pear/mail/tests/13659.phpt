--TEST--
Mail: Test for bug #13659
--FILE--
<?php
//require_once dirname(__FILE__) . '/../Mail/RFC822.php';
require_once 'Mail/RFC822.php';
require_once 'PEAR.php';

$address = '"Test Student" <test@mydomain.com> (test)';
$parser = new Mail_RFC822();
$result = $parser->parseAddressList($address, 'anydomain.com', TRUE);

if (!PEAR::isError($result) && is_array($result) && is_object($result[0]))
    if ($result[0]->personal == '"Test Student"' &&
        $result[0]->mailbox == "test" &&
	$result[0]->host == "mydomain.com" &&
	is_array($result[0]->comment) && $result[0]->comment[0] == 'test')
    {
        print("OK");
    }


?>
--EXPECT--
OK
