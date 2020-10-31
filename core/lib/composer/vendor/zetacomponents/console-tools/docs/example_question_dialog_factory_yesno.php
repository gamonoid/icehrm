<?php

require_once "Base/src/base.php";

function __autoload( $className )
{
    ezcBase::autoload( $className );
}

$out = new ezcConsoleOutput();

$dialog = ezcConsoleQuestionDialog::YesNoQuestion( $out, "Is the answer to everything 42?", "y" );

if ( ( $res = ezcConsoleDialogViewer::displayDialog( $dialog ) ) === "y" )
{
    echo "You are so right! Don't forget your towel! :)\n";
}
else
{
    echo "You should better read some Douglas Adams!\n";
}



?>
