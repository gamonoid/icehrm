<?php
namespace Consolidation\AnnotatedCommand\Parser;

use Symfony\Component\Console\Input\InputOption;
use Consolidation\AnnotatedCommand\Parser\Internal\CommandDocBlockParser;
use Consolidation\AnnotatedCommand\Parser\Internal\CommandDocBlockParserFactory;
use Consolidation\AnnotatedCommand\AnnotationData;

/**
 * Given a class and method name, parse the annotations in the
 * DocBlock comment, and provide accessor methods for all of
 * the elements that are needed to create a Symfony Console Command.
 *
 * Note that the name of this class is now somewhat of a misnomer,
 * as we now use it to hold annotation data for hooks as well as commands.
 * It would probably be better to rename this to MethodInfo at some point.
 */
class CommandInfo
{
    /**
     * @var \ReflectionMethod
     */
    protected $reflection;

    /**
     * @var boolean
     * @var string
    */
    protected $docBlockIsParsed;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $help = '';

    /**
     * @var DefaultsWithDescriptions
     */
    protected $options;

    /**
     * @var DefaultsWithDescriptions
     */
    protected $arguments;

    /**
     * @var array
     */
    protected $exampleUsage = [];

    /**
     * @var AnnotationData
     */
    protected $otherAnnotations;

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @var string
     */
    protected $methodName;

    /**
     * @var string
     */
    protected $returnType;

    /**
     * @var string
     */
    protected $optionParamName;

    /**
     * Create a new CommandInfo class for a particular method of a class.
     *
     * @param string|mixed $classNameOrInstance The name of a class, or an
     *   instance of it.
     * @param string $methodName The name of the method to get info about.
     */
    public function __construct($classNameOrInstance, $methodName)
    {
        $this->reflection = new \ReflectionMethod($classNameOrInstance, $methodName);
        $this->methodName = $methodName;
        $this->otherAnnotations = new AnnotationData();
        // Set up a default name for the command from the method name.
        // This can be overridden via @command or @name annotations.
        $this->name = $this->convertName($this->reflection->name);
        $this->options = new DefaultsWithDescriptions($this->determineOptionsFromParameters(), false);
        $this->arguments = $this->determineAgumentClassifications();
        // Remember the name of the last parameter, if it holds the options.
        // We will use this information to ignore @param annotations for the options.
        if (!empty($this->options)) {
            $this->optionParamName = $this->lastParameterName();
        }
    }

    /**
     * Recover the method name provided to the constructor.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Return the primary name for this command.
     *
     * @return string
     */
    public function getName()
    {
        $this->parseDocBlock();
        return $this->name;
    }

    /**
     * Set the primary name for this command.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getReturnType()
    {
        $this->parseDocBlock();
        return $this->returnType;
    }

    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
        return $this;
    }

    /**
     * Get any annotations included in the docblock comment for the
     * implementation method of this command that are not already
     * handled by the primary methods of this class.
     *
     * @return AnnotationData
     */
    public function getRawAnnotations()
    {
        $this->parseDocBlock();
        return $this->otherAnnotations;
    }

    /**
     * Get any annotations included in the docblock comment,
     * also including default values such as @command.  We add
     * in the default @command annotation late, and only in a
     * copy of the annotation data because we use the existance
     * of a @command to indicate that this CommandInfo is
     * a command, and not a hook or anything else.
     *
     * @return AnnotationData
     */
    public function getAnnotations()
    {
        return new AnnotationData(
            $this->getRawAnnotations()->getArrayCopy() +
            [
                'command' => $this->getName(),
            ]
        );
    }

    /**
     * Return a specific named annotation for this command.
     *
     * @param string $annotation The name of the annotation.
     * @return string
     */
    public function getAnnotation($annotation)
    {
        // hasAnnotation parses the docblock
        if (!$this->hasAnnotation($annotation)) {
            return null;
        }
        return $this->otherAnnotations[$annotation];
    }

    /**
     * Check to see if the specified annotation exists for this command.
     *
     * @param string $annotation The name of the annotation.
     * @return boolean
     */
    public function hasAnnotation($annotation)
    {
        $this->parseDocBlock();
        return isset($this->otherAnnotations[$annotation]);
    }

    /**
     * Save any tag that we do not explicitly recognize in the
     * 'otherAnnotations' map.
     */
    public function addAnnotation($name, $content)
    {
        $this->otherAnnotations[$name] = $content;
    }

    /**
     * Remove an annotation that was previoudly set.
     */
    public function removeAnnotation($name)
    {
        unset($this->otherAnnotations[$name]);
    }

    /**
     * Get the synopsis of the command (~first line).
     *
     * @return string
     */
    public function getDescription()
    {
        $this->parseDocBlock();
        return $this->description;
    }

    /**
     * Set the command description.
     *
     * @param string $description The description to set.
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the help text of the command (the description)
     */
    public function getHelp()
    {
        $this->parseDocBlock();
        return $this->help;
    }
    /**
     * Set the help text for this command.
     *
     * @param string $help The help text.
     */
    public function setHelp($help)
    {
        $this->help = $help;
        return $this;
    }

    /**
     * Return the list of aliases for this command.
     * @return string[]
     */
    public function getAliases()
    {
        $this->parseDocBlock();
        return $this->aliases;
    }

    /**
     * Set aliases that can be used in place of the command's primary name.
     *
     * @param string|string[] $aliases
     */
    public function setAliases($aliases)
    {
        if (is_string($aliases)) {
            $aliases = explode(',', static::convertListToCommaSeparated($aliases));
        }
        $this->aliases = array_filter($aliases);
        return $this;
    }

    /**
     * Return the examples for this command. This is @usage instead of
     * @example because the later is defined by the phpdoc standard to
     * be example method calls.
     *
     * @return string[]
     */
    public function getExampleUsages()
    {
        $this->parseDocBlock();
        return $this->exampleUsage;
    }

    /**
     * Add an example usage for this command.
     *
     * @param string $usage An example of the command, including the command
     *   name and all of its example arguments and options.
     * @param string $description An explanation of what the example does.
     */
    public function setExampleUsage($usage, $description)
    {
        $this->exampleUsage[$usage] = $description;
        return $this;
    }

    /**
     * Return the list of refleaction parameters.
     *
     * @return ReflectionParameter[]
     */
    public function getParameters()
    {
        return $this->reflection->getParameters();
    }

    /**
     * Descriptions of commandline arguements for this command.
     *
     * @return DefaultsWithDescriptions
     */
    public function arguments()
    {
        return $this->arguments;
    }

    /**
     * Descriptions of commandline options for this command.
     *
     * @return DefaultsWithDescriptions
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * Return the name of the last parameter if it holds the options.
     */
    public function optionParamName()
    {
        return $this->optionParamName;
    }

    /**
     * Get the inputOptions for the options associated with this CommandInfo
     * object, e.g. via @option annotations, or from
     * $options = ['someoption' => 'defaultvalue'] in the command method
     * parameter list.
     *
     * @return InputOption[]
     */
    public function inputOptions()
    {
        $explicitOptions = [];

        $opts = $this->options()->getValues();
        foreach ($opts as $name => $defaultValue) {
            $description = $this->options()->getDescription($name);

            $fullName = $name;
            $shortcut = '';
            if (strpos($name, '|')) {
                list($fullName, $shortcut) = explode('|', $name, 2);
            }

            if (is_bool($defaultValue)) {
                $explicitOptions[$fullName] = new InputOption($fullName, $shortcut, InputOption::VALUE_NONE, $description);
            } elseif ($defaultValue === InputOption::VALUE_REQUIRED) {
                $explicitOptions[$fullName] = new InputOption($fullName, $shortcut, InputOption::VALUE_REQUIRED, $description);
            } else {
                $explicitOptions[$fullName] = new InputOption($fullName, $shortcut, InputOption::VALUE_OPTIONAL, $description, $defaultValue);
            }
        }

        return $explicitOptions;
    }

    /**
     * An option might have a name such as 'silent|s'. In this
     * instance, we will allow the @option or @default tag to
     * reference the option only by name (e.g. 'silent' or 's'
     * instead of 'silent|s').
     *
     * @param string $optionName
     * @return string
     */
    public function findMatchingOption($optionName)
    {
        // Exit fast if there's an exact match
        if ($this->options->exists($optionName)) {
            return $optionName;
        }
        $existingOptionName = $this->findExistingOption($optionName);
        if (isset($existingOptionName)) {
            return $existingOptionName;
        }
        return $this->findOptionAmongAlternatives($optionName);
    }

    /**
     * @param string $optionName
     * @return string
     */
    protected function findOptionAmongAlternatives($optionName)
    {
        // Check the other direction: if the annotation contains @silent|s
        // and the options array has 'silent|s'.
        $checkMatching = explode('|', $optionName);
        if (count($checkMatching) > 1) {
            foreach ($checkMatching as $checkName) {
                if ($this->options->exists($checkName)) {
                    $this->options->rename($checkName, $optionName);
                    return $optionName;
                }
            }
        }
        return $optionName;
    }

    /**
     * @param string $optionName
     * @return string|null
     */
    protected function findExistingOption($optionName)
    {
        // Check to see if we can find the option name in an existing option,
        // e.g. if the options array has 'silent|s' => false, and the annotation
        // is @silent.
        foreach ($this->options()->getValues() as $name => $default) {
            if (in_array($optionName, explode('|', $name))) {
                return $name;
            }
        }
    }

    /**
     * Examine the parameters of the method for this command, and
     * build a list of commandline arguements for them.
     *
     * @return array
     */
    protected function determineAgumentClassifications()
    {
        $result = new DefaultsWithDescriptions();
        $params = $this->reflection->getParameters();
        $optionsFromParameters = $this->determineOptionsFromParameters();
        if (!empty($optionsFromParameters)) {
            array_pop($params);
        }
        foreach ($params as $param) {
            $this->addParameterToResult($result, $param);
        }
        return $result;
    }

    /**
     * Examine the provided parameter, and determine whether it
     * is a parameter that will be filled in with a positional
     * commandline argument.
     */
    protected function addParameterToResult($result, $param)
    {
        // Commandline arguments must be strings, so ignore any
        // parameter that is typehinted to any non-primative class.
        if ($param->getClass() != null) {
            return;
        }
        $result->add($param->name);
        if ($param->isDefaultValueAvailable()) {
            $defaultValue = $param->getDefaultValue();
            if (!$this->isAssoc($defaultValue)) {
                $result->setDefaultValue($param->name, $defaultValue);
            }
        } elseif ($param->isArray()) {
            $result->setDefaultValue($param->name, []);
        }
    }

    /**
     * Examine the parameters of the method for this command, and determine
     * the disposition of the options from them.
     *
     * @return array
     */
    protected function determineOptionsFromParameters()
    {
        $params = $this->reflection->getParameters();
        if (empty($params)) {
            return [];
        }
        $param = end($params);
        if (!$param->isDefaultValueAvailable()) {
            return [];
        }
        if (!$this->isAssoc($param->getDefaultValue())) {
            return [];
        }
        return $param->getDefaultValue();
    }

    protected function lastParameterName()
    {
        $params = $this->reflection->getParameters();
        $param = end($params);
        if (!$param) {
            return '';
        }
        return $param->name;
    }

    /**
     * Helper; determine if an array is associative or not. An array
     * is not associative if its keys are numeric, and numbered sequentially
     * from zero. All other arrays are considered to be associative.
     *
     * @param arrau $arr The array
     * @return boolean
     */
    protected function isAssoc($arr)
    {
        if (!is_array($arr)) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Convert from a method name to the corresponding command name. A
     * method 'fooBar' will become 'foo:bar', and 'fooBarBazBoz' will
     * become 'foo:bar-baz-boz'.
     *
     * @param string $camel method name.
     * @return string
     */
    protected function convertName($camel)
    {
        $splitter="-";
        $camel=preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace('/(?!^)[[:upper:]]+/', $splitter.'$0', $camel));
        $camel = preg_replace("/$splitter/", ':', $camel, 1);
        return strtolower($camel);
    }

    /**
     * Parse the docBlock comment for this command, and set the
     * fields of this class with the data thereby obtained.
     */
    protected function parseDocBlock()
    {
        if (!$this->docBlockIsParsed) {
            // The parse function will insert data from the provided method
            // into this object, using our accessors.
            CommandDocBlockParserFactory::parse($this, $this->reflection);
            $this->docBlockIsParsed = true;
        }
    }

    /**
     * Given a list that might be 'a b c' or 'a, b, c' or 'a,b,c',
     * convert the data into the last of these forms.
     */
    protected static function convertListToCommaSeparated($text)
    {
        return preg_replace('#[ \t\n\r,]+#', ',', $text);
    }
}
