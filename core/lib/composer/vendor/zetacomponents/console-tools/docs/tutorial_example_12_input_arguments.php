<?php

require_once 'tutorial_autoload.php';

$input = new ezcConsoleInput();

$helpOption = $input->registerOption( new ezcConsoleOption( 'h', 'help' ) );
$helpOption->isHelpOption = true;

$input->argumentDefinition = new ezcConsoleArguments();

$input->argumentDefinition[0] = new ezcConsoleArgument( "source" );
$input->argumentDefinition[0]->shorthelp = "The source directory.";

$input->argumentDefinition[1] = new ezcConsoleArgument( "destination" );
$input->argumentDefinition[1]->mandatory = false;
$input->argumentDefinition[1]->default   = './';

$input->argumentDefinition[2] = new ezcConsoleArgument( "iterations" );
$input->argumentDefinition[2]->type = ezcConsoleInput::TYPE_INT;
$input->argumentDefinition[2]->shorthelp = "Number of iterations.";
$input->argumentDefinition[2]->longhelp  = "The number of iterations to perform.";


try
{
    $input->process();
}
catch ( ezcConsoleException $e )
{
    die( $e->getMessage() );
}

if ( $helpOption->value === true )
{
    echo $input->getHelpText( "A simple text program" );
}
else
{
    echo "Source:      {$input->argumentDefinition["source"]->value}\n";
    echo "Destination: {$input->argumentDefinition["destination"]->value}\n";
    echo "Iterations:  " . ( $input->argumentDefinition["iterations"]->value === null
                             ? "not set"
                             : $input->argumentDefinition["iterations"]->value
                           );
}

?>
