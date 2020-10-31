<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$output->formats->info->color = 'blue';

$output->outputText( 'Test text in color blue', 'info' );

?>
