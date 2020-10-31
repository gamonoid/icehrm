<?php
/**
 * File containing test code for the ConsoleTools component.
 *
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 * 
 *   http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 *
 * @package ConsoleTools
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */


return array(

    // Default width, no UTF-8, single line, no wrapping
    0 => array(
        // $input
        array(
            'str' => 'Some short string',
        ),
        // $expected
        'Some short string',
    ),

    // Default width, UTF-8, single line, no wrapping
    1 => array(
        // $input
        array(
            'str' => 'Söme shört string',
        ),
        // $expected
        'Söme shört string',
    ),

    // Default width, no UTF-8, single line, wrapping
    2 => array(
        // $input
        array(
            'str' => 'This is a string which is longer than 75 characters but does not contain UTF-8-encoded characters. So what?',
        ),
        // $expected
        "This is a string which is longer than 75 characters but does not contain\nUTF-8-encoded characters. So what?"
    ),

    // Default width, no UTF-8, multiple lines, no wrapping
    3 => array(
        // $input
        array(
            'str' => "This is a string which is longer than 75 \ncharacters but does not contain UTF-8-encoded\n characters. So what?",
        ),
        // $expected
        "This is a string which is longer than 75 \ncharacters but does not contain UTF-8-encoded\n characters. So what?"
    ),

    // Default width, UTF-8, multiple lines, no wrapping
    4 => array(
        // $inpüt
        array(
            'str' => "This is ä string which is lönger thän 75 \nchäräcters büt döes nöt cöntäin UTF-8-encöded\n chäräcters. Sö whät?",
        ),
        // $expected
        "This is ä string which is lönger thän 75 \nchäräcters büt döes nöt cöntäin UTF-8-encöded\n chäräcters. Sö whät?"
    ),

    // Default width, no UTF-8, multiple lines, wrapping
    4 => array(
        // $input
        array(
            'str' => "This is\n a string which is longer than 75 characters but does not contain UTF-8-encoded characters. So what?",
        ),
        // $expected
        "This is\n a string which is longer than 75 characters but does not contain\nUTF-8-encoded characters. So what?"
    ),

    // Default width, no UTF-8, multiple lines, wrapping
    6 => array(
        // $inpüt
        array(
            'str' => "This is\n ä string which is lönger thän 75 chäräcters büt döes nöt cöntäin UTF-8-encöded chäräcters. Sö whät?",
        ),
        // $expected
        "This is\n ä string which is lönger thän 75 chäräcters büt döes nöt cöntäin\nUTF-8-encöded chäräcters. Sö whät?"
    ),

// -----------------

    // Custom width, no UTF-8, single line, no wrapping
    7 => array(
        // $input
        array(
            'str'   => 'Some short string',
            'width' => 18,
        ),
        // $expected
        'Some short string',
    ),

    // Custom width, UTF-8, single line, no wrapping
    8 => array(
        // $input
        array(
            'str'   => 'Söme shört string',
            'width' => 18,
        ),
        // $expected
        'Söme shört string',
    ),

    // Custom width, no UTF-8, single line, wrapping
    9 => array(
        // $input
        array(
            'str'   => 'This is a string which is longer than 75 characters but does not contain UTF-8-encoded characters. So what?',
            'width' => 17,
        ),
        // $expected
        "This is a string\nwhich is longer\nthan 75\ncharacters but\ndoes not contain\nUTF-8-encoded\ncharacters. So\nwhat?"
    ),

    // Custom width, UTF-8, single line, wrapping
    10 => array(
        // $input
        array(
            'str'   => 'This is ä string which is lönger thän 75 chäräcters büt döes nöt cöntäin UTF-8-encöded chäräcters. Sö whät?',
            'width' => 17,
        ),
        // $expected
        "This is ä string\nwhich is lönger\nthän 75\nchäräcters büt\ndöes nöt cöntäin\nUTF-8-encöded\nchäräcters. Sö\nwhät?"
    ),

    // Default width, UTF-8, wrapping
    11 => array(
        // $input
        array(
            'str' => 'This is ä string which is lönger thän 75 chäräcters büt döes nöt cöntäin UTF-8-encöded chäräcters. Sö whät?',
        ),
        // $expected
        "This is ä string which is lönger thän 75 chäräcters büt döes nöt cöntäin\nUTF-8-encöded chäräcters. Sö whät?"
    ),

// -----------------

    // Cut, single line, non UTF-8
    12 => array(
        // $input
        array(
            'str'   => 'This is a string which is longer than 75 characters but does not contain UTF-8-encoded characters. So what?',
            'width' => 75,
            'break' => "\n",
            'cut'   => true,
        ),
        // $expected
        "This is a string which is longer than 75 characters but does not contain\nUTF-8-encoded characters. So what?"
    ),

    // Cut, single line, UTF-8
    13 => array(
        // $input
        array(
            'str'   => 'This is ä string which is lönger thän 75 chäräcters büt döes nöt cöntäin UTF-8-encöded chäräcters. Sö whät?',
            'width' => 75,
            'break' => "\n",
            'cut'   => true,
        ),
        // $expected
        "This is ä string which is lönger thän 75 chäräcters büt döes nöt cöntäin\nUTF-8-encöded chäräcters. Sö whät?"
    ),

    // No cut, multiple lines, non UTF-8
    14 => array(
        // $input
        array(
            'str'   => "This\nis a string which is longer than 75 characters but does not contain UTF-8-encoded characters. So what?",
            'width' => 75,
            'break' => "\n",
            'cut'   => true,
        ),
        // $expected
        "This\nis a string which is longer than 75 characters but does not contain\nUTF-8-encoded characters. So what?"
    ),

    // No cut, multiple lines, UTF-8
    15 => array(
        // $input
        array(
            'str'   => "This\nis ä string which is lönger thän 75 chäräcters büt döes nöt cöntäin UTF-8-encöded chäräcters. Sö whät?",
            'width' => 75,
            'break' => "\n",
            'cut'   => true,
        ),
        // $expected
        "This\nis ä string which is lönger thän 75 chäräcters büt döes nöt cöntäin\nUTF-8-encöded chäräcters. Sö whät?"
    ),

// -----------------

    // Default width, no UTF-8, single line, wrapping, custom wrap char
    16 => array(
        // $input
        array(
            'str'   => 'This is a string which is longer than 75 characters but does not contain UTF-8-encoded characters. So what?',
            'width' => 75,
            'break' => '---'
        ),
        // $expected
        "This is a string which is longer than 75 characters but does not contain---UTF-8-encoded characters. So what?"
    ),

    // Default width, no UTF-8, multiple lines, wrapping, custom wrap char
    17 => array(
        // $input
        array(
            'str'   => "This is a string which is longer than 75 \ncharacters but does not contain UTF-8-encoded\n characters. So what?",
            'width' => 75,
            'break' => '---'
        ),
        // $expected
        "This is a string which is longer than 75 \ncharacters but does not contain---UTF-8-encoded\n characters. So what?"
    ),

    // Default width, UTF-8, multiple lines, wrapping, custom wrap char
    18 => array(
        // $inpüt
        array(
            'str'   => "This is ä string which is lönger thän 75 \nchäräcters büt döes nöt cöntäin UTF-8-encöded\n chäräcters. Sö whät?",
            'width' => 75,
            'break' => '---'
        ),
        // $expected
        "This is ä string which is lönger thän 75 \nchäräcters büt döes nöt cöntäin---UTF-8-encöded\n chäräcters. Sö whät?"
    ),


// ---------- PHP tests to ensure compatibility ------------

// ext/standard/tests/strings/wordwrap.phpt

    19 => array(
        array(
            'str' => "12345 12345 12345 12345",
        ),
        "12345 12345 12345 12345",
    ),

    20 => array(
        array(
            'str' => "12345 12345 1234567890 1234567890",
            12
        ),
        "12345 12345\n1234567890\n1234567890",
    ),
    
    21 => array(
        array(
            'str' => "12345 12345 12345 12345",
            0
        ),
        "12345\n12345\n12345\n12345",
    ),
    22 => array(
        array(
            'str' => "12345 12345 12345 12345",
            0,
            "ab"
        ),
        "12345ab12345ab12345ab12345",
    ),
    23 => array(
        array(
            'str' => "12345 12345 1234567890 1234567890",
            12,
            "ab"
        ),
        "12345 12345ab1234567890ab1234567890"
    ),
    24 => array(
        array(
            'str' => "123ab123ab123",
            3,
            "ab"
        ),
        "123ab123ab123"
    ),
    25 => array(
        array(
            'str' => "123ab123ab123",
            5,
            "ab"
        ),
        "123ab123ab123",
    ),
    26 => array(
        array(
            'str' => "123  123ab123",
            3,
            "ab"
        ),
        "123ab 123ab123",
    ),
    27 => array(
        array(
            'str' => "123 123ab123",
            5,
            "ab"
        ),
        "123ab123ab123",
    ),
    28 => array(
        array(
            'str' => "123 123 123",
            10,
            "ab"
        ),
        "123 123ab123",
    ),
    29 => array(
        array(
            'str' => "123ab123ab123",
            3,
            "ab",
            1
        ),
        "123ab123ab123",
    ),
    30 => array(
        array(
            'str' => "123ab123ab123",
            5,
            "ab",
            1
        ),
        "123ab123ab123",
    ),
    31 => array(
        array(
            'str' => "123  123ab123",
            3,
            "ab",
            1
        ),
        "123ab 12ab3ab123",
    ),
    32 => array(
        array(
            'str' => "123  123ab123",
            5,
            "ab",
            1
        ),
        "123 ab123ab123"
    ),
    33 => array(
        array(
            'str' => "123  123  123",
            8,
            "ab",
            1
        ),
        "123  123ab 123"
    ),
    34 => array(
        array(
            'str' => "123  12345  123",
            8,
            "ab",
            1
        ),
        "123 ab12345 ab123"
    ),
    35 => array(
        array(
            'str' => "1234",
            1,
            "ab",
            1
        ),
        "1ab2ab3ab4"
    ),
    36 => array(
        array(
            'str' => "12345 1234567890",
            5,
            "|",
            1
        ),
        "12345|12345|67890"
    ),
    37 => array(
        array(
            'str' => "123 1234567890 123",
            10,
            "|==",
            1
        ),
        "123|==1234567890|==123"
    ),
    38 => array(
        array(
            chr(0),
            0,
            ""
        ),
        false
    ),

// ext/standard/tests/strings/wordwrap_basic.phpt

    39 => array(
        array(
            'str' => 'The quick brown foooooooooox jummmmmmmmmmmmped over the lazzzzzzzzzzzy doooooooooooooooooooooog.',
        ),
        "The quick brown foooooooooox jummmmmmmmmmmmped over the lazzzzzzzzzzzy\ndoooooooooooooooooooooog.",
    ),

    40 => array(
        array(
            'str' => 'The quick brown foooooooooox jummmmmmmmmmmmped over the lazzzzzzzzzzzy doooooooooooooooooooooog.',
            'width' => 80,
        ),
        "The quick brown foooooooooox jummmmmmmmmmmmped over the lazzzzzzzzzzzy\ndoooooooooooooooooooooog.",
    ),

    40 => array(
        array(
            'str' => 'The quick brown foooooooooox jummmmmmmmmmmmped over the lazzzzzzzzzzzy doooooooooooooooooooooog.',
            'width' => 80,
            'break' => '<br />\n',
        ),
        'The quick brown foooooooooox jummmmmmmmmmmmped over the lazzzzzzzzzzzy<br />\ndoooooooooooooooooooooog.',
    ),

    41 => array(
        array(
            'str' => 'The quick brown foooooooooox jummmmmmmmmmmmped over the lazzzzzzzzzzzy doooooooooooooooooooooog.',
            'width' => 10,
            'break' => '<br />\n',
            'cut' => true,
        ),
        'The quick<br />\nbrown<br />\nfooooooooo<br />\nox<br />\njummmmmmmm<br />\nmmmmped<br />\nover the<br />\nlazzzzzzzz<br />\nzzzy<br />\ndooooooooo<br />\noooooooooo<br />\nooog.'
    ),

    42 => array(
        array(
            'str' => 'The quick brown foooooooooox jummmmmmmmmmmmped over the lazzzzzzzzzzzy doooooooooooooooooooooog.',
            'width' => 10,
            'break' => '<br />\n',
            'cut' => false,
        ),
        'The quick<br />\nbrown<br />\nfoooooooooox<br />\njummmmmmmmmmmmped<br />\nover the<br />\nlazzzzzzzzzzzy<br />\ndoooooooooooooooooooooog.'
    ),

// ext/standard/tests/strings/wordwrap_variation5.phpt

    43 => array(
        array(
            'str' => 'Testing wordrap function',
            'width' => 1,
        ),
        "Testing\nwordrap\nfunction",
    ),
    
    44 => array(
        array(
            'str' => 'Testing wordrap function',
            'width' => 1,
            'break' => ' ',
        ),
        'Testing wordrap function'
    ),
    
    44 => array(
        array(
            'str' => 'Testing wordrap function',
            'width' => 1,
            'break' => '  ',
        ),
        'Testing  wordrap  function'
    ),

    45 => array(
        array(
            'str' => 'Testing wordrap function',
            'width' => 1,
            'break' => ' ',
            'cut' => false,
        ),
        'Testing wordrap function'
    ),
    
    46 => array(
        array(
            'str' => 'Testing wordrap function',
            'width' => 1,
            'break' => '  ',
            'cut' => false,
        ),
        'Testing  wordrap  function'
    ),

    47 => array(
        array(
            'str' => 'Testing wordrap function',
            'width' => 1,
            'break' => ' ',
            'cut' => true,
        ),
        'T e s t i n g w o r d r a p f u n c t i o n'
    ),
    
    48 => array(
        array(
            'str' => 'Testing wordrap function',
            'width' => 1,
            'break' => '  ',
            'cut' => true,
        ),
        'T  e  s  t  i  n  g  w  o  r  d  r  a  p  f  u  n  c  t  i  o  n'
    ),
);

?>
