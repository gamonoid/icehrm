<?php
namespace Consolidation\OutputFormatters\Formatters;

use Consolidation\OutputFormatters\Options\FormatterOptions;
use Symfony\Component\Console\Output\OutputInterface;

interface FormatterInterface
{
    /**
     * Given structured data, apply appropriate
     * formatting, and return a printable string.
     *
     * @param mixed $data Structured data to format
     *
     * @return string
     */
    public function write(OutputInterface $output, $data, FormatterOptions $options);
}
