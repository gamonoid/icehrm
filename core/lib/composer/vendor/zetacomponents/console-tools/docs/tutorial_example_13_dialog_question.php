<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$question = new ezcConsoleQuestionDialog( $output );
$question->options->text = "Do you want to proceed?";
$question->options->showResults = true;
$question->options->validator = new ezcConsoleQuestionDialogCollectionValidator(
    array( "y", "n" ),
    "y",
    ezcConsoleQuestionDialogCollectionValidator::CONVERT_LOWER
);

do
{
    echo "\nSome action performed...\n\n";
}
while ( ezcConsoleDialogViewer::displayDialog( $question ) !== "n" );

echo "Goodbye!\n";

?>
