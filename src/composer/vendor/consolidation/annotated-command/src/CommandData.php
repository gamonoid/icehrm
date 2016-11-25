<?php
namespace Consolidation\AnnotatedCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandData
{
    /** var AnnotationData */
    protected $annotationData;
    /** var InputInterface */
    protected $input;
    /** var OutputInterface */
    protected $output;
    /** var boolean */
    protected $usesInputInterface;
    /** var boolean */
    protected $usesOutputInterface;
    /** var boolean */
    protected $includeOptionsInArgs;

    public function __construct(
        AnnotationData $annotationData,
        InputInterface $input,
        OutputInterface $output,
        $usesInputInterface = false,
        $usesOutputInterface = false
    ) {
        $this->annotationData = $annotationData;
        $this->input = $input;
        $this->output = $output;
        $this->usesInputInterface = false;
        $this->usesOutputInterface = false;
        $this->includeOptionsInArgs = true;
    }

    /**
     * For internal use only; indicates that the function to be called
     * should be passed an InputInterface &/or an OutputInterface.
     * @param booean $usesInputInterface
     * @param boolean $usesOutputInterface
     * @return self
     */
    public function setUseIOInterfaces($usesInputInterface, $usesOutputInterface)
    {
        $this->usesInputInterface = $usesInputInterface;
        $this->usesOutputInterface = $usesOutputInterface;
        return $this;
    }

    /**
     * For backwards-compatibility mode only: disable addition of
     * options on the end of the arguments list.
     */
    public function setIncludeOptionsInArgs($includeOptionsInArgs)
    {
        $this->includeOptionsInArgs = $includeOptionsInArgs;
        return $this;
    }

    public function annotationData()
    {
        return $this->annotationData;
    }

    public function input()
    {
        return $this->input;
    }

    public function output()
    {
        return $this->output;
    }

    public function arguments()
    {
        return $this->input->getArguments();
    }

    public function options()
    {
        return $this->input->getOptions();
    }

    public function getArgsWithoutAppName()
    {
        $args = $this->arguments();

        // When called via the Application, the first argument
        // will be the command name. The Application alters the
        // input definition to match, adding a 'command' argument
        // to the beginning.
        array_shift($args);

        if ($this->usesInputInterface) {
            array_unshift($args, $this->input());
        }

        if ($this->usesOutputInterface) {
            array_unshift($args, $this->output());
        }

        return $args;
    }

    public function getArgsAndOptions()
    {
        // Get passthrough args, and add the options on the end.
        $args = $this->getArgsWithoutAppName();
        if ($this->includeOptionsInArgs) {
            $args['options'] = $this->options();
        }
        return $args;
    }
}
