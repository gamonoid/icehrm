<?php
namespace Consolidation\AnnotatedCommand\Parser\Internal;

use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Consolidation\AnnotatedCommand\Parser\DefaultsWithDescriptions;

/**
 * Given a class and method name, parse the annotations in the
 * DocBlock comment, and provide accessor methods for all of
 * the elements that are needed to create an annotated Command.
 */
abstract class AbstractCommandDocBlockParser
{
    /**
     * @var CommandInfo
     */
    protected $commandInfo;

    /**
     * @var \ReflectionMethod
     */
    protected $reflection;

    /**
     * @var array
     */
    protected $tagProcessors = [
        'command' => 'processCommandTag',
        'name' => 'processCommandTag',
        'arg' => 'processArgumentTag',
        'param' => 'processParamTag',
        'return' => 'processReturnTag',
        'option' => 'processOptionTag',
        'default' => 'processDefaultTag',
        'aliases' => 'processAliases',
        'usage' => 'processUsageTag',
        'description' => 'processAlternateDescriptionTag',
        'desc' => 'processAlternateDescriptionTag',
    ];

    public function __construct(CommandInfo $commandInfo, \ReflectionMethod $reflection)
    {
        $this->commandInfo = $commandInfo;
        $this->reflection = $reflection;
    }

    protected function processAllTags($phpdoc)
    {
        // Iterate over all of the tags, and process them as necessary.
        foreach ($phpdoc->getTags() as $tag) {
            $processFn = [$this, 'processGenericTag'];
            if (array_key_exists($tag->getName(), $this->tagProcessors)) {
                $processFn = [$this, $this->tagProcessors[$tag->getName()]];
            }
            $processFn($tag);
        }
    }

    abstract protected function getTagContents($tag);

    /**
     * Parse the docBlock comment for this command, and set the
     * fields of this class with the data thereby obtained.
     */
    abstract public function parse();

    /**
     * Save any tag that we do not explicitly recognize in the
     * 'otherAnnotations' map.
     */
    protected function processGenericTag($tag)
    {
        $this->commandInfo->addAnnotation($tag->getName(), $this->getTagContents($tag));
    }

    /**
     * Set the name of the command from a @command or @name annotation.
     */
    protected function processCommandTag($tag)
    {
        $commandName = $this->getTagContents($tag);
        $this->commandInfo->setName($commandName);
        // We also store the name in the 'other annotations' so that is is
        // possible to determine if the method had a @command annotation.
        $this->commandInfo->addAnnotation($tag->getName(), $commandName);
    }

    /**
     * The @description and @desc annotations may be used in
     * place of the synopsis (which we call 'description').
     * This is discouraged.
     *
     * @deprecated
     */
    protected function processAlternateDescriptionTag($tag)
    {
        $this->commandInfo->setDescription($this->getTagContents($tag));
    }

    /**
     * Store the data from a @arg annotation in our argument descriptions.
     */
    protected function processArgumentTag($tag)
    {
        if (!$this->pregMatchNameAndDescription((string)$tag->getDescription(), $match)) {
            return;
        }
        $this->addOptionOrArgumentTag($tag, $this->commandInfo->arguments(), $match);
    }

    /**
     * Store the data from an @option annotation in our option descriptions.
     */
    protected function processOptionTag($tag)
    {
        if (!$this->pregMatchOptionNameAndDescription((string)$tag->getDescription(), $match)) {
            return;
        }
        $this->addOptionOrArgumentTag($tag, $this->commandInfo->options(), $match);
    }

    protected function addOptionOrArgumentTag($tag, DefaultsWithDescriptions $set, $nameAndDescription)
    {
        $variableName = $this->commandInfo->findMatchingOption($nameAndDescription['name']);
        $desc = $nameAndDescription['description'];
        $description = static::removeLineBreaks($desc);
        $set->add($variableName, $description);
    }

    /**
     * Store the data from a @default annotation in our argument or option store,
     * as appropriate.
     */
    protected function processDefaultTag($tag)
    {
        if (!$this->pregMatchNameAndDescription((string)$tag->getDescription(), $match)) {
            return;
        }
        $variableName = $match['name'];
        $defaultValue = $this->interpretDefaultValue($match['description']);
        if ($this->commandInfo->arguments()->exists($variableName)) {
            $this->commandInfo->arguments()->setDefaultValue($variableName, $defaultValue);
            return;
        }
        $variableName = $this->commandInfo->findMatchingOption($variableName);
        if ($this->commandInfo->options()->exists($variableName)) {
            $this->commandInfo->options()->setDefaultValue($variableName, $defaultValue);
        }
    }

    /**
     * Store the data from a @usage annotation in our example usage list.
     */
    protected function processUsageTag($tag)
    {
        $lines = explode("\n", $this->getTagContents($tag));
        $usage = array_shift($lines);
        $description = static::removeLineBreaks(implode("\n", $lines));

        $this->commandInfo->setExampleUsage($usage, $description);
    }

    /**
     * Process the comma-separated list of aliases
     */
    protected function processAliases($tag)
    {
        $this->commandInfo->setAliases((string)$tag->getDescription());
    }

    /**
     * Store the data from a @param annotation in our argument descriptions.
     */
    protected function processParamTag($tag)
    {
        $variableName = $tag->getVariableName();
        $variableName = str_replace('$', '', $variableName);
        $description = static::removeLineBreaks((string)$tag->getDescription());
        if ($variableName == $this->commandInfo->optionParamName()) {
            return;
        }
        $this->commandInfo->arguments()->add($variableName, $description);
    }

    /**
     * Store the data from a @return annotation in our argument descriptions.
     */
    abstract protected function processReturnTag($tag);

    protected function interpretDefaultValue($defaultValue)
    {
        $defaults = [
            'null' => null,
            'true' => true,
            'false' => false,
            "''" => '',
            '[]' => [],
        ];
        foreach ($defaults as $defaultName => $defaultTypedValue) {
            if ($defaultValue == $defaultName) {
                return $defaultTypedValue;
            }
        }
        return $defaultValue;
    }

    /**
     * Given a docblock description in the form "$variable description",
     * return the variable name and description via the 'match' parameter.
     */
    protected function pregMatchNameAndDescription($source, &$match)
    {
        $nameRegEx = '\\$(?P<name>[^ \t]+)[ \t]+';
        $descriptionRegEx = '(?P<description>.*)';
        $optionRegEx = "/{$nameRegEx}{$descriptionRegEx}/s";

        return preg_match($optionRegEx, $source, $match);
    }

    /**
     * Given a docblock description in the form "$variable description",
     * return the variable name and description via the 'match' parameter.
     */
    protected function pregMatchOptionNameAndDescription($source, &$match)
    {
        // Strip type and $ from the text before the @option name, if present.
        $source = preg_replace('/^[a-zA-Z]* ?\\$/', '', $source);
        $nameRegEx = '(?P<name>[^ \t]+)[ \t]+';
        $descriptionRegEx = '(?P<description>.*)';
        $optionRegEx = "/{$nameRegEx}{$descriptionRegEx}/s";

        return preg_match($optionRegEx, $source, $match);
    }

    /**
     * Given a list that might be 'a b c' or 'a, b, c' or 'a,b,c',
     * convert the data into the last of these forms.
     */
    protected static function convertListToCommaSeparated($text)
    {
        return preg_replace('#[ \t\n\r,]+#', ',', $text);
    }

    /**
     * Take a multiline description and convert it into a single
     * long unbroken line.
     */
    protected static function removeLineBreaks($text)
    {
        return trim(preg_replace('#[ \t\n\r]+#', ' ', $text));
    }
}
