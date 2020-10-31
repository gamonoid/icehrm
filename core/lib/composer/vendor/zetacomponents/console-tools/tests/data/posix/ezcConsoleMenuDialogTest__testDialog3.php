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

require_once dirname(__FILE__) . "/../../../vendor/autoload.php";

$out = new ezcConsoleOutput();

$opts = new ezcConsoleMenuDialogOptions();
$opts->text = "Please choose a possibility:\n";
$opts->validator = new ezcConsoleMenuDialogDefaultValidator(
    array(
        "A" => "Selection A",
        "B" => "Selection B",
        "C" => "Selection C",
        "D" => "Selection D",
        "Z" => "Selection Z",
    ),
    "Z",
    ezcConsoleMenuDialogDefaultValidator::CONVERT_UPPER
);

$dialog = new ezcConsoleMenuDialog( $out, $opts );

while ( ( $res = ezcConsoleDialogViewer::displayDialog( $dialog ) ) !== 'Z' )
{
    echo "User seletced $res\n";
}

echo "User quitted\n";

?>
