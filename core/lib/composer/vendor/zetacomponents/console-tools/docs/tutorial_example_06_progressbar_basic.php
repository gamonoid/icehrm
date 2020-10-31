<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$bar = new ezcConsoleProgressbar( $output, 15 );

for ( $i = 0; $i < 15; $i++ )
{
    $bar->advance();
    usleep(  mt_rand( 2000, 200000 ) );
}

$bar->finish();

$output->outputLine();

?>
