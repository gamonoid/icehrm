<?php
namespace Consolidation\TestUtils;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Consolidation\AnnotatedCommand\CommandError;
use Consolidation\AnnotatedCommand\AnnotationData;

/**
 *
 */
class ExampleHookAllCommandFile
{
    public function doCat($one, $two = '', $options = ['flip' => false])
    {
        if ($options['flip']) {
            return "{$two}{$one}";
        }
        return "{$one}{$two}";
    }

    public function doRepeat($one, $two = '', $options = ['repeat' => 1])
    {
        return str_repeat("{$one}{$two}", $options['repeat']);
    }

    /**
     * This hook function does not specify which command or annotation
     * it is hooking; that makes it apply to every command in the same class.
     *
     * @hook alter
     */
    public function alterAllCommands($result)
    {
        if (is_string($result)) {
            $result = "*** $result ***";
        }
        return $result;
    }
}
