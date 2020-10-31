<?php
/**
 * File containing the ezcConsoleDialogViewer class.
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
 * @filesource
 */

/**
 * Utility class for ezcConsoleDialog implementations.
 * This class contains utility methods for working with {@link
 * ezcConsoleDialog} implementations.
 *
 * To display a dialog in a loop until a valid result is received do:
 * <code>
 * // Instatiate dialog in $dialog ...
 * ezcConsoleDialogViewer::displayDialog( $dialog );
 * </code>
 *
 * For implementing a custom dialog, the method {@link readLine()} method can be
 * used to read a line of input from the user.
 * 
 * @package ConsoleTools
 * @version //autogen//
 */
class ezcConsoleDialogViewer
{
    /**
     * Displays a dialog and returns a valid result from it.
     * This methods displays a dialog in a loop, until it received a valid
     * result from it and returns this result.
     * 
     * @param ezcConsoleDialog $dialog The dialog to display.
     * @return mixed The result from this dialog.
     */
    public static function displayDialog( ezcConsoleDialog $dialog )
    {
        do
        {
            $dialog->display();
        }
        while ( $dialog->hasValidResult() === false );
        return $dialog->getResult();
    }

    /**
     * Returns a line from STDIN.
     * The returned line is fully trimmed.
     * 
     * @return string
     * @throws ezcConsoleDialogAbortException
     *         if the user closes STDIN using <CTRL>-D.
     */
    public static function readLine()
    {
        $res = trim( fgets( STDIN ) );
        if ( feof( STDIN ) )
        {
            throw new ezcConsoleDialogAbortException();
        }
        return $res;
    }
}

?>
