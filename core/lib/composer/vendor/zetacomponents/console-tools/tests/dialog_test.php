<?php
/**
 * ezcConsoleDialogTest class.
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
 * @subpackage Tests
 * @version //autogentag//
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Generic test case for ezcConsoleDialog implementations.
 * 
 * @package ConsoleTools
 * @subpackage Tests
 */
abstract class ezcConsoleDialogTest extends ezcTestCase
{
    const PIPE_READ_SLEEP = 5000;

    protected $dataDir;

    protected $phpPath;

    protected $output;

    protected $proc;

    protected $pipes = array();

    protected $res = array();

    protected function setUp()
    {
        $this->dataDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR
            . ( ezcBaseFeatures::os() === 'Windows' ? "windows" : "posix" );
        $this->determinePhpPath();
        $this->output  = new ezcConsoleOutput();
        $this->output->formats->test->color = "blue";
    }

    protected function determinePhpPath()
    {
        if ( isset( $_SERVER["_"] ) )
        {
            $this->phpPath = $_SERVER["_"];
        }
        else if ( ezcBaseFeatures::os() === 'Windows' )
        {
            $this->phpPath = 'php.exe';
        }
        else
        {
            $this->phpPath = '/usr/bin/env php';
        }
    }

    protected function tearDown()
    {
        unset( $this->output );
    }

    protected function runDialog( $methodName )
    {
        $methodName = strtr(
            $methodName,
            array(
                ":" => "_",
            )
        );
        $scriptFile = $this->dataDir . DIRECTORY_SEPARATOR . $methodName . '.php';
        $resFile    = $this->dataDir . DIRECTORY_SEPARATOR . $methodName . '_res.php';
        if ( !file_exists( $scriptFile ) )
        {
            throw new RuntimeException( "Missing script file '$scriptFile'!" );
        }

        $desc = array(
            0 => array( "pipe", "r" ),  // stdin
            1 => array( "pipe", "w" ),  // stdout
            2 => array( "pipe", "w" )   // stderr
        );
        $this->proc = proc_open("{$this->phpPath} '{$scriptFile}'", $desc, $this->pipes );
        $this->res  = ( file_exists( $resFile ) ? require( $resFile ) : false );
    }

    protected function closeDialog()
    {
        proc_close( $this->proc );
        unset( $this->pipes, $this->res );
    }

    protected function saveDialogResult( $methodName, $res )
    {
        $methodName = strtr(
            $methodName,
            array(
                ":" => "_",
            )
        );
        $resFile    = "{$this->dataDir}/{$methodName}_res.php";
        file_put_contents(
            $resFile,
            "<?php\n\nreturn " . var_export( $res, true ) . ";\n\n?>"
        );
    }

    protected function readPipe( $pipe )
    {
        usleep( self::PIPE_READ_SLEEP );
        return fread( $pipe, 1024 );
    }
}

?>
