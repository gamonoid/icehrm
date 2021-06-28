<?php

namespace Classes\Pdf;

class PdfColour
{
    const GREY = [224, 224, 224];
    const GREY_DARK = [160, 160, 160];
    const WHITE = [255, 255, 255];
    const BLACK_LIGHT = [84, 84, 84];
    const GREY_100 = [100, 100, 100];

    public static function setFillColor(\FPDF $pdf, $color)
    {
        $pdf->SetFillColor($color[0], $color[1], $color[2]);
    }

    public static function setTextColor(\FPDF $pdf, $color)
    {
        $pdf->SetTextColor($color[0], $color[1], $color[2]);
    }

    public static function setDrawColor(\FPDF $pdf, $color)
    {
        $pdf->SetDrawColor($color[0], $color[1], $color[2]);
    }
}
