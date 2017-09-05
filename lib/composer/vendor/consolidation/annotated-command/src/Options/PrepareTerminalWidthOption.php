<?php
namespace Consolidation\AnnotatedCommand\Options;

use Symfony\Component\Console\Application;
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\OutputFormatters\Options\FormatterOptions;

class PrepareTerminalWidthOption implements PrepareFormatter
{
    /** var Application */
    protected $application;

    /** var int */
    protected $defaultWidth;

    /** var int */
    protected $maxWidth = PHP_INT_MAX;

    /** var int */
    protected $minWidth = 0;

    public function __construct($defaultWidth = 0)
    {
        $this->defaultWidth = $defaultWidth;
    }

    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    public function prepare(CommandData $commandData, FormatterOptions $options)
    {
        $width = $this->getTerminalWidth();
        if (!$width) {
            $width = $this->defaultWidth;
        }

        // Enforce minimum and maximum widths
        $width = min($width, $this->getMaxWidth($commandData));
        $width = max($width, $this->getMinWidth($commandData));

        $options->setWidth($width);
    }

    protected function getTerminalWidth()
    {
        if (!$this->application) {
            return 0;
        }

        $dimensions = $this->application->getTerminalDimensions();
        if ($dimensions[0] == null) {
            return 0;
        }

        return $dimensions[0];
    }

    protected function getMaxWidth(CommandData $commandData)
    {
        return $this->maxWidth;
    }

    protected function getMinWidth(CommandData $commandData)
    {
        return $this->minWidth;
    }
}
