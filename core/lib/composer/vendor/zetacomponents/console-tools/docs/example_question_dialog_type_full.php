<?php

require_once "Base/src/base.php";

function __autoload( $className )
{
    ezcBase::autoload( $className );
}

$out = new ezcConsoleOutput();

$opts = new ezcConsoleQuestionDialogOptions();
$opts->text = "How old are you?";
$opts->showResults = true;
$opts->validator = new ezcConsoleQuestionDialogTypeValidator(
    ezcConsoleQuestionDialogTypeValidator::TYPE_INT
);

$dialog = new ezcConsoleQuestionDialog( $out, $opts );

if ( ( $res = ezcConsoleDialogViewer::displayDialog( $dialog ) ) < 8 )
{
    echo "Sorry, I can not believe that you are $res years old!\n";
}
else
{
    echo "Hey, you're still young! :)\n";
}

?>
