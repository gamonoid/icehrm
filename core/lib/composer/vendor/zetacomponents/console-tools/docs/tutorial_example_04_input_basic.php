<?php

require_once 'tutorial_autoload.php';

$input = new ezcConsoleInput();

$helpOption = $input->registerOption( 
    new ezcConsoleOption( 
        'h',
        'help'
    )
);

try
{
    $input->process();
}
catch ( ezcConsoleOptionException $e )
{
    die( $e->getMessage() );
}

if ( $helpOption->value !== false )
{
    echo "Help requested.";
}
else
{
    echo "No help requested.";
}

?>
