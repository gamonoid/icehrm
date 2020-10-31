<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$output->formats->info->color = 'blue';
$output->formats->info->style = array( 'bold' );

$output->setOptions( 
    array( 
        'autobreak'      => 78,
        'verbosityLevel' => 3
    )
);

$output->outputLine( 'This is a very very long info text. It has so much information in '.
                     'it, that it will definitly not fit into 1 line. Therefore, '.
                     'ezcConsoleOutput will automatically wrap the line for us.', 'info' );

$output->outputLine();

$output->outputLine( 'This verbose information will currently not be displayed.', 'info', 10 );

$output->outputLine( 'But this verbose information will be displayed.', 'info', 2 );

?>
