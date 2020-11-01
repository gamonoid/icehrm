<?php

require_once 'tutorial_autoload.php';

$output = new ezcConsoleOutput();

$output->formats->blue->color  = 'blue';
$output->formats->blue->style = array(  'bold' );
$output->formats->red->color   = 'red';
$output->formats->red->style = array(  'bold' );
$output->formats->green->color = 'green';
$output->formats->green->style = array(  'bold' );

$colors = array( 'red', 'blue', 'green' );
$aligns = array( ezcConsoleTable::ALIGN_LEFT, ezcConsoleTable::ALIGN_CENTER, ezcConsoleTable::ALIGN_RIGHT );

$table = new ezcConsoleTable( $output, 78 );

$table->options->corner = ' ';
$table->options->lineHorizontal = ' ';
$table->options->lineVertical = ' ';
$table->options->widthType = ezcConsoleTable::WIDTH_FIXED;

for ( $i = 0; $i < 10; $i++ )
{
    for ( $j = 0; $j < 10; $j++ )
    {
        $table[$i][$j]->content = '*';
        $table[$i][$j]->format  = $colors[array_rand( $colors )];
        $table[$i][$j]->align   = $aligns[array_rand( $aligns )];
    }
}

$table->outputTable();
$output->outputLine();


?>
