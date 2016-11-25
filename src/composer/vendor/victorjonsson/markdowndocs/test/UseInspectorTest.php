<?php

class UseInspectorTest extends PHPUnit_Framework_TestCase {

    function testInspection()
    {
        $code = '
        Abra

        use apa\\sten\\groda;
        use  apa\\sten\\BjornGroda;
        use     apa\\sten\\groda;
        use \\apa\\sten\\groda;
        use apa

        use  \\apa  ;
        use \\apa\\Sten
        ;

        Kadabra
        use \apa ;

        useBala;
        ';

        $expected = array(
            '\\apa\\sten\\groda',
            '\\apa\\sten\\BjornGroda',
            '\\apa\\sten\\groda',
            '\\apa\\sten\\groda',
            '\\apa',
            '\\apa',
            '\\apa\\Sten',
            '\\apa'
        );

        $inspector = new \PHPDocsMD\UseInspector();
        $this->assertEquals($expected, $inspector->getUseStatementsInString($code));
    }


}