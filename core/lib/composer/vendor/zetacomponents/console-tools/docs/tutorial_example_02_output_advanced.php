<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$output->formats->info->color = 'blue';

$output->formats->error->color = 'red';
$output->formats->error->style = array( 'bold' );

$output->formats->fatal->color = 'red';
$output->formats->fatal->style = array( 'bold', 'underlined' );
$output->formats->fatal->bgcolor = 'black';

$output->outputText( 'This is some standard text ' );
$output->outputText( 'including some error', 'error' );
$output->outputText( ' wrapped in standard text.' );
$output->outputText( "\n" );

$output->outputLine( 'This is a fatal error message.', 'fatal' );

$output->outputText( 'Test' );

?>
