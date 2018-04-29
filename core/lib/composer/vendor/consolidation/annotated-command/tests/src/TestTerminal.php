<?php
namespace Consolidation\TestUtils;

class TestTerminal
{
    protected $width = 0;

    public function __construct($width)
    {
        $this->width = $width;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setWidth($width)
    {
        $this->width = $width;
    }
}
