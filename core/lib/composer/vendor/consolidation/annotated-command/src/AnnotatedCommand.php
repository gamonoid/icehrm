<?php
namespace Consolidation\AnnotatedCommand;

use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Consolidation\AnnotatedCommand\Help\HelpDocumentAlter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AnnotatedCommands are created automatically by the
 * AnnotatedCommandFactory.  Each command method in a
 * command file will produce one AnnotatedCommand.  These
 * are then added to your Symfony Console Application object;
 * nothing else is needed.
 *
 * Optionally, though, you may extend AnnotatedCommand directly
 * to make a single command.  The usage pattern is the same
 * as for any other Symfony Console command, except that you may
 * omit the 'Confiure' method, and instead place your annotations
 * on the execute() method.
 *
 * @package Consolidation\AnnotatedCommand
 */
class AnnotatedCommand extends Command implements HelpDocumentAlter
{
    protected $commandCallback;
    protected $commandProcessor;
    protected $annotationData;
    protected $examples = [];
    protected $topics = [];
    protected $usesInputInterface;
    protected $usesOutputInterface;
    protected $returnType;

    public function __construct($name = null)
    {
        $commandInfo = false;

        // If this is a subclass of AnnotatedCommand, check to see
        // if the 'execute' method is annotated.  We could do this
        // unconditionally; it is a performance optimization to skip
        // checking the annotations if $this is an instance of
        // AnnotatedCommand.  Alternately, we break out a new subclass.
        // The command factory instantiates the subclass.
        if (get_class($this) != 'Consolidation\AnnotatedCommand\AnnotatedCommand') {
            $commandInfo = CommandInfo::create($this, 'execute');
            if (!isset($name)) {
                $name = $commandInfo->getName();
            }
        }
        parent::__construct($name);
        if ($commandInfo && $commandInfo->hasAnnotation('command')) {
            $this->setCommandInfo($commandInfo);
            $this->setCommandOptions($commandInfo);
        }
    }

    public function setCommandCallback($commandCallback)
    {
        $this->commandCallback = $commandCallback;
        return $this;
    }

    public function setCommandProcessor($commandProcessor)
    {
        $this->commandProcessor = $commandProcessor;
        return $this;
    }

    public function commandProcessor()
    {
        // If someone is using an AnnotatedCommand, and is NOT getting
        // it from an AnnotatedCommandFactory OR not correctly injecting
        // a command processor via setCommandProcessor() (ideally via the
        // DI container), then we'll just give each annotated command its
        // own command processor. This is not ideal; preferably, there would
        // only be one instance of the command processor in the application.
        if (!isset($this->commandProcessor)) {
            $this->commandProcessor = new CommandProcessor(new HookManager());
        }
        return $this->commandProcessor;
    }

    public function getReturnType()
    {
        return $this->returnType;
    }

    public function setReturnType($returnType)
    {
        $this->returnType = $returnType;
        return $this;
    }

    public function getAnnotationData()
    {
        return $this->annotationData;
    }

    public function setAnnotationData($annotationData)
    {
        $this->annotationData = $annotationData;
        return $this;
    }

    public function getTopics()
    {
        return $this->topics;
    }

    public function setTopics($topics)
    {
        $this->topics = $topics;
        return $this;
    }

    public function setCommandInfo($commandInfo)
    {
        $this->setDescription($commandInfo->getDescription());
        $this->setHelp($commandInfo->getHelp());
        $this->setAliases($commandInfo->getAliases());
        $this->setAnnotationData($commandInfo->getAnnotations());
        $this->setTopics($commandInfo->getTopics());
        foreach ($commandInfo->getExampleUsages() as $usage => $description) {
            $this->addUsageOrExample($usage, $description);
        }
        $this->setCommandArguments($commandInfo);
        $this->setReturnType($commandInfo->getReturnType());
        // Hidden commands available since Symfony 3.2
        // http://symfony.com/doc/current/console/hide_commands.html
        if (method_exists($this, 'setHidden')) {
            $this->setHidden($commandInfo->getHidden());
        }
        return $this;
    }

    public function getExampleUsages()
    {
        return $this->examples;
    }

    protected function addUsageOrExample($usage, $description)
    {
        $this->addUsage($usage);
        if (!empty($description)) {
            $this->examples[$usage] = $description;
        }
    }

    public function helpAlter(\DomDocument $originalDom)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->appendChild($commandXML = $dom->createElement('command'));
        $commandXML->setAttribute('id', $this->getName());
        $commandXML->setAttribute('name', $this->getName());

        // Get the original <command> element and its top-level elements.
        $originalCommandXML = $this->getSingleElementByTagName($dom, $originalDom, 'command');
        $originalUsagesXML = $this->getSingleElementByTagName($dom, $originalCommandXML, 'usages');
        $originalDescriptionXML = $this->getSingleElementByTagName($dom, $originalCommandXML, 'description');
        $originalHelpXML = $this->getSingleElementByTagName($dom, $originalCommandXML, 'help');
        $originalArgumentsXML = $this->getSingleElementByTagName($dom, $originalCommandXML, 'arguments');
        $originalOptionsXML = $this->getSingleElementByTagName($dom, $originalCommandXML, 'options');

        // Keep only the first of the <usage> elements
        $newUsagesXML = $dom->createElement('usages');
        $firstUsageXML = $this->getSingleElementByTagName($dom, $originalUsagesXML, 'usage');
        $newUsagesXML->appendChild($firstUsageXML);

        // Create our own <example> elements
        $newExamplesXML = $dom->createElement('examples');
        foreach ($this->examples as $usage => $description) {
            $newExamplesXML->appendChild($exampleXML = $dom->createElement('example'));
            $exampleXML->appendChild($usageXML = $dom->createElement('usage', $usage));
            $exampleXML->appendChild($descriptionXML = $dom->createElement('description', $description));
        }

        // Create our own <alias> elements
        $newAliasesXML = $dom->createElement('aliases');
        foreach ($this->getAliases() as $alias) {
            $newAliasesXML->appendChild($dom->createElement('alias', $alias));
        }

        // Create our own <topic> elements
        $newTopicsXML = $dom->createElement('topics');
        foreach ($this->getTopics() as $topic) {
            $newTopicsXML->appendChild($topicXML = $dom->createElement('topic', $topic));
        }

        // Place the different elements into the <command> element in the desired order
        $commandXML->appendChild($newUsagesXML);
        $commandXML->appendChild($newExamplesXML);
        $commandXML->appendChild($originalDescriptionXML);
        $commandXML->appendChild($originalArgumentsXML);
        $commandXML->appendChild($originalOptionsXML);
        $commandXML->appendChild($originalHelpXML);
        $commandXML->appendChild($newAliasesXML);
        $commandXML->appendChild($newTopicsXML);

        return $dom;
    }

    protected function getSingleElementByTagName($dom, $parent, $tagName)
    {
        // There should always be exactly one '<command>' element.
        $elements = $parent->getElementsByTagName($tagName);
        $result = $elements->item(0);

        $result = $dom->importNode($result, true);

        return $result;
    }

    protected function setCommandArguments($commandInfo)
    {
        $this->setUsesInputInterface($commandInfo);
        $this->setUsesOutputInterface($commandInfo);
        $this->setCommandArgumentsFromParameters($commandInfo);
        return $this;
    }

    /**
     * Check whether the first parameter is an InputInterface.
     */
    protected function checkUsesInputInterface($params)
    {
        /** @var \ReflectionParameter $firstParam */
        $firstParam = reset($params);
        return $firstParam && $firstParam->getClass() && $firstParam->getClass()->implementsInterface(
            '\\Symfony\\Component\\Console\\Input\\InputInterface'
        );
    }

    /**
     * Determine whether this command wants to get its inputs
     * via an InputInterface or via its command parameters
     */
    protected function setUsesInputInterface($commandInfo)
    {
        $params = $commandInfo->getParameters();
        $this->usesInputInterface = $this->checkUsesInputInterface($params);
        return $this;
    }

    /**
     * Determine whether this command wants to send its output directly
     * to the provided OutputInterface, or whether it will returned
     * structured output to be processed by the command processor.
     */
    protected function setUsesOutputInterface($commandInfo)
    {
        $params = $commandInfo->getParameters();
        $index = $this->checkUsesInputInterface($params) ? 1 : 0;
        $this->usesOutputInterface =
            (count($params) > $index) &&
            $params[$index]->getClass() &&
            $params[$index]->getClass()->implementsInterface(
                '\\Symfony\\Component\\Console\\Output\\OutputInterface'
            )
        ;
        return $this;
    }

    protected function setCommandArgumentsFromParameters($commandInfo)
    {
        $args = $commandInfo->arguments()->getValues();
        foreach ($args as $name => $defaultValue) {
            $description = $commandInfo->arguments()->getDescription($name);
            $hasDefault = $commandInfo->arguments()->hasDefault($name);
            $parameterMode = $this->getCommandArgumentMode($hasDefault, $defaultValue);
            $this->addArgument($name, $parameterMode, $description, $defaultValue);
        }
        return $this;
    }

    protected function getCommandArgumentMode($hasDefault, $defaultValue)
    {
        if (!$hasDefault) {
            return InputArgument::REQUIRED;
        }
        if (is_array($defaultValue)) {
            return InputArgument::IS_ARRAY;
        }
        return InputArgument::OPTIONAL;
    }

    public function setCommandOptions($commandInfo, $automaticOptions = [])
    {
        $inputOptions = $commandInfo->inputOptions();

        $this->addOptions($inputOptions + $automaticOptions, $automaticOptions);
        return $this;
    }

    public function addOptions($inputOptions, $automaticOptions = [])
    {
        foreach ($inputOptions as $name => $inputOption) {
            $description = $inputOption->getDescription();

            if (empty($description) && isset($automaticOptions[$name])) {
                $description = $automaticOptions[$name]->getDescription();
                $inputOption = static::inputOptionSetDescription($inputOption, $description);
            }
            $this->getDefinition()->addOption($inputOption);
        }
    }

    protected static function inputOptionSetDescription($inputOption, $description)
    {
        // Recover the 'mode' value, because Symfony is stubborn
        $mode = 0;
        if ($inputOption->isValueRequired()) {
            $mode |= InputOption::VALUE_REQUIRED;
        }
        if ($inputOption->isValueOptional()) {
            $mode |= InputOption::VALUE_OPTIONAL;
        }
        if ($inputOption->isArray()) {
            $mode |= InputOption::VALUE_IS_ARRAY;
        }
        if (!$mode) {
            $mode = InputOption::VALUE_NONE;
        }

        $inputOption = new InputOption(
            $inputOption->getName(),
            $inputOption->getShortcut(),
            $mode,
            $description,
            $inputOption->getDefault()
        );
        return $inputOption;
    }

    /**
     * Returns all of the hook names that may be called for this command.
     *
     * @return array
     */
    public function getNames()
    {
        return HookManager::getNames($this, $this->commandCallback);
    }

    /**
     * Add any options to this command that are defined by hook implementations
     */
    public function optionsHook()
    {
        $this->commandProcessor()->optionsHook(
            $this,
            $this->getNames(),
            $this->annotationData
        );
    }

    public function optionsHookForHookAnnotations($commandInfoList)
    {
        foreach ($commandInfoList as $commandInfo) {
            $inputOptions = $commandInfo->inputOptions();
            $this->addOptions($inputOptions);
            foreach ($commandInfo->getExampleUsages() as $usage => $description) {
                if (!in_array($usage, $this->getUsages())) {
                    $this->addUsageOrExample($usage, $description);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->commandProcessor()->interact(
            $input,
            $output,
            $this->getNames(),
            $this->annotationData
        );
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Allow the hook manager a chance to provide configuration values,
        // if there are any registered hooks to do that.
        $this->commandProcessor()->initializeHook($input, $this->getNames(), $this->annotationData);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Validate, run, process, alter, handle results.
        return $this->commandProcessor()->process(
            $output,
            $this->getNames(),
            $this->commandCallback,
            $this->createCommandData($input, $output)
        );
    }

    /**
     * This function is available for use by a class that may
     * wish to extend this class rather than use annotations to
     * define commands. Using this technique does allow for the
     * use of annotations to define hooks.
     */
    public function processResults(InputInterface $input, OutputInterface $output, $results)
    {
        $commandData = $this->createCommandData($input, $output);
        $commandProcessor = $this->commandProcessor();
        $names = $this->getNames();
        $results = $commandProcessor->processResults(
            $names,
            $results,
            $commandData
        );
        return $commandProcessor->handleResults(
            $output,
            $names,
            $results,
            $commandData
        );
    }

    protected function createCommandData(InputInterface $input, OutputInterface $output)
    {
        $commandData = new CommandData(
            $this->annotationData,
            $input,
            $output
        );

        $commandData->setUseIOInterfaces(
            $this->usesInputInterface,
            $this->usesOutputInterface
        );

        // Allow the commandData to cache the list of options with
        // special default values ('null' and 'true'), as these will
        // need special handling. @see CommandData::options().
        $commandData->cacheSpecialDefaults($this->getDefinition());

        return $commandData;
    }
}
