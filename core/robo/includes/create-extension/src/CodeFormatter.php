<?php
namespace CreateExtension;

class CodeFormatter
{
    public function addTabs(array $lines) {
        return array_map(function ($line) {
            return $this->addTab($line);
        }, $lines);
    }

    public function addTab(string $line) {
        return sprintf("\t%s", $line);
    }

    public function addNewLineBefore(string $line) {
        return sprintf("%s%s", PHP_EOL, $line);
    }

    public function addNewLineAfter(string $line) {
        return sprintf("%s%s", $line, PHP_EOL);
    }
}