<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$menu = new ezcConsoleMenuDialog( $output );
$menu->options = new ezcConsoleMenuDialogOptions();
$menu->options->text = "Please choose a possibility:\n";
$menu->options->validator = new ezcConsoleMenuDialogDefaultValidator(
    array(
        "1" => "Perform some more actions",
        "2" => "Perform another action",
        "0" => "Quit",
    ),
    "0"
);

while ( ( $choice = ezcConsoleDialogViewer::displayDialog( $menu ) ) != 0 )
{
    switch ( $choice )
    {
        case 1:
            echo "Performing some more actions...\n";
            break;
        case 2:
            echo "Performing some other actions!\n";
            break;
    }
}

?>
