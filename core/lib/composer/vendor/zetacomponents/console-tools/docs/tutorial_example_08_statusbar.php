<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$output->formats->success->color = 'green';
$output->formats->failure->color = 'red';

$options = array( 
    'successChar' => $output->formatText( '+', 'success' ),
    'failureChar' => $output->formatText( '-', 'failure' ),
);

$status = new ezcConsoleStatusbar( $output, $options );

for ( $i = 0; $i < 120; $i++ )
{
    $nextStatus = ( bool )mt_rand( 0,1 );
    $status->add( $nextStatus );
    usleep(  mt_rand( 200, 2000 ) );
}

$output->outputLine();
$output->outputLine( 'Successes: ' . $status->getSuccessCount() . ', Failures: ' . $status->getFailureCount() );

?>
