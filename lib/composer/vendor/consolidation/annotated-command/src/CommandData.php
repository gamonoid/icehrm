<?php
namespace Consolidation\AnnotatedCommand;

use Symfony\Component\Console\Input\ArgvInput;
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
    /** var array */
    protected $specialDefaults = [];

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
        // We cannot tell the difference between '--foo' (an option without
        // a value) and the absence of '--foo' when the option has an optional
        // value, and the current vallue of the option is 'null' using only
        // the public methods of InputInterface. We'll try to figure out
        // which is which by other means here.
        $options = $this->getAdjustedOptions();

        // Make two conversions here:
        // --foo=0 wil convert $value from '0' to 'false' for binary options.
        // --foo with $value of 'true' will be forced to 'false' if --no-foo exists.
        foreach ($options as $option => $value) {
            if ($this->shouldConvertOptionToFalse($options, $option, $value)) {
                $options[$option] = false;
            }
        }

        return $options;
    }

    /**
     * Use 'hasParameterOption()' to attempt to disambiguate option states.
     */
    protected function getAdjustedOptions()
    {
        $options = $this->input->getOptions();

        // If Input isn't an ArgvInput, then return the options as-is.
        if (!$this->input instanceof ArgvInput) {
            return $options;
        }

        // If we have an ArgvInput, then we can determine if options
        // are missing from the command line. If the option value is
        // missing from $input, then we will keep the value `null`.
        // If it is present, but has no explicit value, then change it its
        // value to `true`.
        foreach ($options as $option => $value) {
            if (($value === null) && ($this->input->hasParameterOption("--$option"))) {
                $options[$option] = true;
            }
        }

        return $options;
    }

    protected function shouldConvertOptionToFalse($options, $option, $value)
    {
        // If the value is 'true' (e.g. the option is '--foo'), then convert
        // it to false if there is also an option '--no-foo'. n.b. if the
        // commandline has '--foo=bar' then $value will not be 'true', and
        // --no-foo will be ignored.
        if ($value === true) {
            // Check if the --no-* option exists. Note that none of the other
            // alteration apply in the $value == true case, so we can exit early here.
            $negation_key = 'no-' . $option;
            return array_key_exists($negation_key, $options) && $options[$negation_key];
        }

        // If the option is '--foo=0', convert the '0' to 'false' when appropriate.
        if ($value !== '0') {
            return false;
        }

        // The '--foo=0' convertion is only applicable when the default value
        // is not in the special defaults list. i.e. you get a literal '0'
        // when your default is a string.
        return in_array($option, $this->specialDefaults);
    }

    public function cacheSpecialDefaults($definition)
    {
        foreach ($definition->getOptions() as $option => $inputOption) {
            $defaultValue = $inputOption->getDefault();
            if (($defaultValue === null) || ($defaultValue === true)) {
                $this->specialDefaults[] = $option;
            }
        }
    }

    public function getArgsWithoutAppName()
    {
        $args = $this->arguments();

        // When called via the Application, the first argument
        // will be the command name. The Application alters the
        // input definition to match, adding a 'command' argument
        // to the beginning.
        array_shift($args);

        if ($this->usesOutputInterface) {
            array_unshift($args, $this->output());
        }

        if ($this->usesInputInterface) {
            array_unshift($args, $this->input());
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
