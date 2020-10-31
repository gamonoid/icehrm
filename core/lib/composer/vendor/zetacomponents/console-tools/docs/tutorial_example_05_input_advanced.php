<?php

require_once 'tutorial_autoload.php';

$input = new ezcConsoleInput();

$helpOption = $input->registerOption( new ezcConsoleOption( 'h', 'help' ) );

$inputOption = $input->registerOption( 
    new ezcConsoleOption( 
        'i',
        'input',
        ezcConsoleInput::TYPE_STRING
    )
);

$outputOption = $input->registerOption(
    new ezcConsoleOption( 
        'o',
        'output'
    )
);
$outputOption->type = ezcConsoleInput::TYPE_STRING;

$inputOption->addDependency( 
    new ezcConsoleOptionRule( $outputOption )
);
$outputOption->addDependency( 
    new ezcConsoleOptionRule( $inputOption )
);

try
{
    $input->process();
}
catch ( ezcConsoleOptionException $e )
{
    die( $e->getMessage() );
}

if ( $helpOption->value === true )
{
    echo $input->getSynopsis() . "\n";
    foreach ( $input->getOptions() as $option )
    {
        echo "-{$option->short}/{$option->long}: {$option->shorthelp}\n";
    }
}
elseif ( $outputOption->value !== false )
{
    echo "Input: {$inputOption->value}, Output: {$outputOption->value}\n";
    echo "Arguments: " . implode( ", ", $input->getArguments() ) . "\n";
}

?>
