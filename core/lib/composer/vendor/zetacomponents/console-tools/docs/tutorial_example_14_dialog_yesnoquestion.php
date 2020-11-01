<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$question = ezcConsoleQuestionDialog::YesNoQuestion(
    $output,
    "Do you want to proceed?",
    "y"
);

do
{
    echo "\nSome action performed...\n\n";
}
while ( ezcConsoleDialogViewer::displayDialog( $question ) !== "n" );

echo "Goodbye!\n";

?>
