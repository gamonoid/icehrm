<?php

require_once "Base/src/base.php";

function __autoload( $className )
{
    ezcBase::autoload( $className );
}

$out = new ezcConsoleOutput();

$opts = new ezcConsoleQuestionDialogOptions();
$opts->text = "Do you want to proceed?";
$opts->showResults = true;
$opts->validator = new ezcConsoleQuestionDialogCollectionValidator(
    array( "y", "n" ),
    "n",
    ezcConsoleQuestionDialogCollectionValidator::CONVERT_LOWER
);

$dialog = new ezcConsoleQuestionDialog( $out, $opts );

echo "The user decided to " . ( ezcConsoleDialogViewer::displayDialog( $dialog ) === "n" ? "not " : "" ) . "proceed.\n";

?>
