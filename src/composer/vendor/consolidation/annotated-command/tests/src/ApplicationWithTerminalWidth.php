<?php
namespace Consolidation\TestUtils;

use Symfony\Component\Console\Application;

class ApplicationWithTerminalWidth extends Application
{
    protected $width = 0;
    protected $height = 0;

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
    }

    public function setWidthAndHeight($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function getTerminalDimensions()
    {
        return [ $this->width, $this->height ];
    }
}
