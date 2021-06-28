<?php
namespace Classes\Pdf;

use Classes\SettingsManager;

class BasePdfTemplate extends \FPDF
{
    protected $title;
    protected $date;

    const WIDTH = 210;

    public function initialize($title)
    {
        $this->AliasNbPages();
        $this->AddPage();
        $this->SetTitle($title);
        $this->date = $date = date('c');
    }

    // @codingStandardsIgnoreStart
    function Header()
    {
        // Logo
        try {
            $this->Image(\Classes\UIManager::getInstance()->getCompanyLogoUrl(), 10, 10, 0, 10);
        } catch (\Exception $e) {
        }

        PdfColour::setTextColor($this, PdfColour::GREY_100);
        // Arial bold 15
        $this->SetFont('Arial', '', 9);
        // Company name
        $companyName = SettingsManager::getInstance()->getSetting('Company: Name');
        $this->Cell(30, 23, $companyName, 0, 0, 'L');
        PdfColour::setDrawColor($this, PdfColour::GREY_100);
        $this->Line(10, 26, self::WIDTH - 10, 26);
        // Line break
        $this->Ln(20);
    }

    function Footer()
    {
        PdfColour::setTextColor($this, PdfColour::GREY_100);
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->PageNo().'/{nb}, Date:'.$this->date, 0, 0, 'C');
    }
    // @codingStandardsIgnoreEnd

    public function addH1($title, $style = 'B', $textAlignment = 'C')
    {
        $this->addH($title, $style, $textAlignment, 21);
    }

    public function addH2($title, $style = 'B', $textAlignment = 'C')
    {
        $this->addH($title, $style, $textAlignment, 17);
    }

    public function addH3($title, $style = 'B', $textAlignment = 'C')
    {
        $this->addH($title, $style, $textAlignment, 14);
    }

    public function addH($title, $style = 'B', $textAlignment = 'C', $fontSize = 16)
    {
        PdfColour::setTextColor($this, PdfColour::BLACK_LIGHT);
        $this->SetFont('Arial', $style, $fontSize);
        $this->Cell(0, 10, $title, 0, 1, $textAlignment);
    }

    public function addBorderedText($text)
    {
        $this->addText($text, '', 'L', 10, PdfColour::BLACK_LIGHT, 1, PdfColour::GREY_DARK);
    }

    public function addText(
        $text,
        $style = '',
        $textAlignment = 'L',
        $fontSize = 10,
        $color = PdfColour::BLACK_LIGHT,
        $cellBorder = 0,
        $cellBorderColor = PdfColour::GREY_DARK
    ) {
        PdfColour::setTextColor($this, $color);
        $this->SetFont('Arial', $style, $fontSize);
        if ($cellBorder === 1) {
            PdfColour::setDrawColor($this, $cellBorderColor);
        }
        PdfColour::setFillColor($this, PdfColour::WHITE);
        $this->MultiCell(0, 5, $text, $cellBorder, 1, $textAlignment);
    }

    public function addKeyValue($key, $value, $type = 'text')
    {
        PdfColour::setDrawColor($this, PdfColour::GREY_DARK);
        PdfColour::setFillColor($this, PdfColour::GREY);


        if ($type === 'textarea') {
            $this->Cell(0, 10, $key, 1, 1, 'L', true);
        } else {
            $this->Cell(80, 10, $key, 1, 0, 'L', true);
        }

        PdfColour::setFillColor($this, PdfColour::WHITE);

        if ($type === 'textarea') {
            $this->MultiCell(0, 5, $value, 1, 1, 'L');
        } elseif ($type === 'date') {
            $value = date('Y-m-d', strtotime($value));
            $this->Cell(110, 10, $value, 1, 1, 'L', true);
        } elseif ($type === 'select2multi') {
            $value = json_decode($value, true);
            $value = !empty($value) ? join(',', $value) : '';
            $this->Cell(110, 10, $value, 1, 1, 'L', true);
        } else {
            $this->Cell(110, 10, $value, 1, 1, 'L', true);
        }
    }

    public function addKeyValueObject($key, $value)
    {
        PdfColour::setDrawColor($this, PdfColour::GREY_DARK);
        PdfColour::setFillColor($this, PdfColour::GREY);
        $this->Cell(80, 10, $key, 1, 0, 'L', true);

        PdfColour::setFillColor($this, PdfColour::WHITE);
        $this->MultiCell(110, 10, $value, 1, 1, true);
    }

    public function addHR($width = 0)
    {
        $this->Ln(2);
        PdfColour::setDrawColor($this, PdfColour::GREY_DARK);
        PdfColour::setFillColor($this, PdfColour::GREY_DARK);
        $this->Cell($width, 0.2, '', 0, 0, '', true);
        // Line break
        $this->Ln(3);
    }

    public function getImageFromDataURI($dataURI)
    {
        $img = explode(',', $dataURI, 2)[1];
        return 'data://text/plain;base64,'. $img;
    }

    public function addSignature($name, $data)
    {
        $image = $this->getImageFromDataURI($data);
        $this->Ln(10);
        $this->Image($image, $this->GetX(), $this->GetY(), 30, 0, 'png');
        $this->Ln(30);
        $this->addHR(30);
        $this->addText($name);
    }
}
