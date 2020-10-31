<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$output->formats->bar->color = 'blue';
$output->formats->bar->style = array( 'bold' );

$options = array( 
    'emptyChar'       => ' ',
    'barChar'         => '-',
    'formatString'    => '%fraction%% <' . $output->formatText( '%bar%', 'bar' ) . '> Uploaded %act% / %max% kb',
    'redrawFrequency' => 50,
);

$bar = new ezcConsoleProgressbar( $output, 1024, $options );

for ( $i = 0; $i < 1024; $i++ )
{
    $bar->advance();
    usleep(  mt_rand( 200, 2000 ) );
}

$bar->finish();

$output->outputLine();

?>
