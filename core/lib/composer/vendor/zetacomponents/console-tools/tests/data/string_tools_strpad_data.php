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


// ---------- PHP tests to ensure compatibility ------------

// ext/standard/tests/strings/str_pad.phpt
//
$testSets = array(

    0 => array(
        // $input
        array(
            'input' => 'str_pad()',
            'padLegth' => 20,
        ),
        // $expected
        'str_pad()           ',
    ),

    1 => array(
        // $input
        array(
            'input' => 'str_pad()',
            'padLegth' => 20,
            'padString' => '-+'
        ),
        // $expected
        'str_pad()-+-+-+-+-+-',
    ),

    2 => array(
        // $input
        array(
            'input' => 'str_pad()',
            'padLegth' => 20,
            'padString' => '-+',
            'padType' => STR_PAD_LEFT
        ),
        // $expected
        '-+-+-+-+-+-str_pad()',
    ),

    3 => array(
        // $input
        array(
            'input' => 'str_pad()',
            'padLegth' => 20,
            'padString' => '-+',
            'padType' => STR_PAD_RIGHT
        ),
        // $expected
        'str_pad()-+-+-+-+-+-',
    ),

    4 => array(
        // $input
        array(
            'input' => 'str_pad()',
            'padLegth' => 20,
            'padString' => '-+',
            'padType' => STR_PAD_BOTH
        ),
        // $expected
        '-+-+-str_pad()-+-+-+',
    ),
);

$inputStrings = array(
    "variation",                                    // normal string
    "", 	                                        // empty string
    NULL,                                           // NULL
    true,                                           // boolean 
    15,                                             // numeric
    15.55,                                          // numeric
    "2990"                                          // numeric string
);

$padLengths = array(
    -PHP_INT_MAX,   // huge negative value
    -1,             // negative value
    0,              // < sizeof(input_string)
    9,              // <= sizeof(input_string)
    10,             // > sizeof(input_string) 
    16,             // > sizeof(input_string)
);

$padString = '=';

$results = array(
    0 =>    'variation',
    1 => 	'variation',
    2 => 	'variation',
    3 => 	'variation',
    4 => 	'variation',

    5 => 	'variation',
    6 => 	'variation',
    7 => 	'variation',
    8 => 	'variation',
    9 => 	'variation',

    10 => 	'variation',
    11 => 	'variation',
    12 => 	'variation',
    13 => 	'variation',
    14 => 	'variation',

    15 => 	'variation',
    16 => 	'variation',
    17 => 	'variation',
    18 => 	'variation',
    19 => 	'variation',

    20 => 	'variation ',
    21 => 	'variation=',
    22 => 	'=variation',
    23 => 	'variation=',
    24 => 	'variation=',

    25 => 	'variation       ',
    26 => 	'variation=======',
    27 => 	'=======variation',
    28 => 	'variation=======',
    29 => 	'===variation====',

    30 => 	'',
    31 => 	'',
    32 => 	'',
    33 => 	'',
    34 => 	'',

    35 => 	'',
    36 => 	'',
    37 => 	'',
    38 => 	'',
    39 => 	'',

    40 => 	'',
    41 => 	'',
    42 => 	'',
    43 => 	'',
    44 => 	'',

    45 => 	'         ',
    46 => 	'=========',
    47 => 	'=========',
    48 => 	'=========',
    49 => 	'=========',

    50 => 	'          ',
    51 => 	'==========',
    52 => 	'==========',
    53 => 	'==========',
    54 => 	'==========',

    55 => 	'                ',
    56 => 	'================',
    57 => 	'================',
    58 => 	'================',
    59 => 	'================',

    60 => 	'',
    61 => 	'',
    62 => 	'',
    63 => 	'',
    64 => 	'',

    65 => 	'',
    66 => 	'',
    67 => 	'',
    68 => 	'',
    69 => 	'',

    70 => 	'',
    71 => 	'',
    72 => 	'',
    73 => 	'',
    74 => 	'',

    75 => 	'         ',
    76 => 	'=========',
    77 => 	'=========',
    78 => 	'=========',
    79 => 	'=========',

    80 => 	'          ',
    81 => 	'==========',
    82 => 	'==========',
    83 => 	'==========',
    84 => 	'==========',

    85 => 	'                ',
    86 => 	'================',
    87 => 	'================',
    88 => 	'================',
    89 => 	'================',

    90 => 	'1',
    91 => 	'1',
    92 => 	'1',
    93 => 	'1',
    94 => 	'1',

    95 => 	'1',
    96 => 	'1',
    97 => 	'1',
    98 => 	'1',
    99 => 	'1',

    100 => 	'1',
    101 => 	'1',
    102 => 	'1',
    103 => 	'1',
    104 => 	'1',

    105 => 	'1        ',
    106 => 	'1========',
    107 => 	'========1',
    108 => 	'1========',
    109 => 	'====1====',

    110 => 	'1         ',
    111 => 	'1=========',
    112 => 	'=========1',
    113 => 	'1=========',
    114 => 	'====1=====',

    115 => 	'1               ',
    116 => 	'1===============',
    117 => 	'===============1',
    118 => 	'1===============',
    119 => 	'=======1========',

    120 => 	'15',
    121 => 	'15',
    122 => 	'15',
    123 => 	'15',
    124 => 	'15',

    125 => 	'15',
    126 => 	'15',
    127 => 	'15',
    128 => 	'15',
    129 => 	'15',

    130 => 	'15',
    131 => 	'15',
    132 => 	'15',
    133 => 	'15',
    134 => 	'15',

    135 => 	'15       ',
    136 => 	'15=======',
    137 => 	'=======15',
    138 => 	'15=======',
    139 => 	'===15====',

    140 => 	'15        ',
    141 => 	'15========',
    142 => 	'========15',
    143 => 	'15========',
    144 => 	'====15====',

    145 => 	'15              ',
    146 => 	'15==============',
    147 => 	'==============15',
    148 => 	'15==============',
    149 => 	'=======15=======',

    150 => 	'15.55',
    151 => 	'15.55',
    152 => 	'15.55',
    153 => 	'15.55',
    154 => 	'15.55',

    155 => 	'15.55',
    156 => 	'15.55',
    157 => 	'15.55',
    158 => 	'15.55',
    159 => 	'15.55',

    160 => 	'15.55',
    161 => 	'15.55',
    162 => 	'15.55',
    163 => 	'15.55',
    164 => 	'15.55',

    165 => 	'15.55    ',
    166 => 	'15.55====',
    167 => 	'====15.55',
    168 => 	'15.55====',
    169 => 	'==15.55==',

    170 => 	'15.55     ',
    171 => 	'15.55=====',
    172 => 	'=====15.55',
    173 => 	'15.55=====',
    174 => 	'==15.55===',

    175 => 	'15.55           ',
    176 => 	'15.55===========',
    177 => 	'===========15.55',
    178 => 	'15.55===========',
    179 => 	'=====15.55======',

    180 => 	'2990',
    181 => 	'2990',
    182 => 	'2990',
    183 => 	'2990',
    184 => 	'2990',

    185 => 	'2990',
    186 => 	'2990',
    187 => 	'2990',
    188 => 	'2990',
    189 => 	'2990',

    190 => 	'2990',
    191 => 	'2990',
    192 => 	'2990',
    193 => 	'2990',
    194 => 	'2990',

    195 => 	'2990     ',
    196 => 	'2990=====',
    197 => 	'=====2990',
    198 => 	'2990=====',
    199 => 	'==2990===',

    200 => 	'2990      ',
    201 => 	'2990======',
    202 => 	'======2990',
    203 => 	'2990======',
    204 => 	'===2990===',

    205 => 	'2990            ',
    206 => 	'2990============',
    207 => 	'============2990',
    208 => 	'2990============',
    209 => 	'======2990======',
);

$i = 0;
foreach ( $inputStrings as $inputString )
{
    foreach ( $padLengths as $padLength )
    {
        $testSets[] = array(
            array(
                'input'     => $inputString,
                'padLength' => $padLength,
            ),
            $results[$i++],
        );
        $testSets[] = array(
            array(
                'input'     => $inputString,
                'padLength' => $padLength,
                'padString' => $padString,
            ),
            $results[$i++],
        );
        $testSets[] = array(
            array(
                'input'     => $inputString,
                'padLength' => $padLength,
                'padString' => $padString,
                'padType'   => STR_PAD_LEFT
            ),
            $results[$i++],
        );
        $testSets[] = array(
            array(
                'input'     => $inputString,
                'padLength' => $padLength,
                'padString' => $padString,
                'padType'   => STR_PAD_RIGHT
            ),
            $results[$i++],
        );
        $testSets[] = array(
            array(
                'input'     => $inputString,
                'padLength' => $padLength,
                'padString' => $padString,
                'padType'   => STR_PAD_BOTH
            ),
            $results[$i++],
        );
    }
}

return $testSets;
?>
