--TEST--
Net_SMTP: quotedata()
--FILE--
<?php

require_once 'Net/SMTP.php';

$tests = array(
    /* Newlines */
    "\n"               => "\r\n",
    "\r\n"             => "\r\n",
    "\nxx"             => "\r\nxx",
    "xx\n"             => "xx\r\n",
    "xx\nxx"           => "xx\r\nxx",
    "\n\nxx"           => "\r\n\r\nxx",
    "xx\n\nxx"         => "xx\r\n\r\nxx",
    "xx\n\n"           => "xx\r\n\r\n",
    "\r\nxx"           => "\r\nxx",
    "xx\r\n"           => "xx\r\n",
    "xx\r\nxx"         => "xx\r\nxx",
    "\r\n\r\nxx"       => "\r\n\r\nxx",
    "xx\r\n\r\nxx"     => "xx\r\n\r\nxx",
    "xx\r\n\r\n"       => "xx\r\n\r\n",
    "\r\n\nxx"         => "\r\n\r\nxx",
    "\n\r\nxx"         => "\r\n\r\nxx",
    "xx\r\n\nxx"       => "xx\r\n\r\nxx",
    "xx\n\r\nxx"       => "xx\r\n\r\nxx",
    "xx\r\n\n"         => "xx\r\n\r\n",
    "xx\n\r\n"         => "xx\r\n\r\n",
    "\r"               => "\r\n",
    "\rxx"             => "\r\nxx",
    "xx\rxx"           => "xx\r\nxx",
    "xx\r"             => "xx\r\n",
    "\r\r"             => "\r\n\r\n",
    "\r\rxx"           => "\r\n\r\nxx",
    "xx\r\rxx"         => "xx\r\n\r\nxx",
    "xx\r\r"           => "xx\r\n\r\n",
    "xx\rxx\nxx\r\nxx" => "xx\r\nxx\r\nxx\r\nxx",
    "\r\r\n\n"         => "\r\n\r\n\r\n",

    /* Dots */
    "."                 => "..",
    "xxx\n."            => "xxx\r\n..",
    "xxx\n.\nxxx"       => "xxx\r\n..\r\nxxx",
    "xxx.\n.xxx"        => "xxx.\r\n..xxx",
);

function literal($x)
{
    return str_replace(array("\r", "\n"), array('\r', '\n'), $x);
}

$smtp = new Net_SMTP();
$error = false;
foreach ($tests as $input => $expected) {
    $output = $input;
    $smtp->quotedata($output);
    if ($output != $expected) {
        printf("Error: '%s' => '%s' (expected: '%s')",
            literal($input), literal($output), literal($expected));
        $error = true;
    }
}

if (!$error) {
    echo "success\n";
}

--EXPECT--
success
