<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$output->formats->error->color = 'red';
$output->formats->error->style = array( 'bold' );
$output->formats->error->target = ezcConsoleOutput::TARGET_STDERR;

$output->outputLine( 'Unable to connect to database', 'error' );

?>
