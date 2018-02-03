<?php
namespace Consolidation\AnnotatedCommand;

use Consolidation\AnnotatedCommand\Hooks\Dispatchers\ReplaceCommandHookDispatcher;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

use Consolidation\OutputFormatters\FormatterManager;
use Consolidation\OutputFormatters\Options\FormatterOptions;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Consolidation\AnnotatedCommand\Options\PrepareFormatter;

use Consolidation\AnnotatedCommand\Hooks\Dispatchers\InitializeHookDispatcher;
use Consolidation\AnnotatedCommand\Hooks\Dispatchers\OptionsHookDispatcher;
use Consolidation\AnnotatedCommand\Hooks\Dispatchers\InteractHookDispatcher;
use Consolidation\AnnotatedCommand\Hooks\Dispatchers\ValidateHookDispatcher;
use Consolidation\AnnotatedCommand\Hooks\Dispatchers\ProcessResultHookDispatcher;
use Consolidation\AnnotatedCommand\Hooks\Dispatchers\StatusDeterminerHookDispatcher;
use Consolidation\AnnotatedCommand\Hooks\Dispatchers\ExtracterHookDispatcher;

/**
 * Process a command, including hooks and other callbacks.
 * There should only be one command processor per application.
 * Provide your command processor to the AnnotatedCommandFactory
 * via AnnotatedCommandFactory::setCommandProcessor().
 */
class CommandProcessor implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** var HookManager */
    protected $hookManager;
    /** var FormatterManager */
    protected $formatterManager;
    /** var callable */
    protected $displayErrorFunction;
    /** var PrepareFormatterOptions[] */
    protected $prepareOptionsList = [];
    /** var boolean */
    protected $passExceptions;

    public function __construct(HookManager $hookManager)
    {
        $this->hookManager = $hookManager;
    }

    /**
     * Return the hook manager
     * @return HookManager
     */
    public function hookManager()
    {
        return $this->hookManager;
    }

    public function addPrepareFormatter(PrepareFormatter $preparer)
    {
        $this->prepareOptionsList[] = $preparer;
    }

    public function setFormatterManager(FormatterManager $formatterManager)
    {
        $this->formatterManager = $formatterManager;
        return $this;
    }

    public function setDisplayErrorFunction(callable $fn)
    {
        $this->displayErrorFunction = $fn;
        return $this;
    }

    /**
     * Set a mode to make the annotated command library re-throw
     * any exception that it catches while processing a command.
     *
     * The default behavior in the current (2.x) branch is to catch
     * the exception and replace it with a CommandError object that
     * may be processed by the normal output processing passthrough.
     *
     * In the 3.x branch, exceptions will never be caught; they will
     * be passed through, as if setPassExceptions(true) were called.
     * This is the recommended behavior.
     */
    public function setPassExceptions($passExceptions)
    {
        $this->passExceptions = $passExceptions;
        return $this;
    }

    public function commandErrorForException(\Exception $e)
    {
        if ($this->passExceptions) {
            throw $e;
        }
        return new CommandError($e->getMessage(), $e->getCode());
    }

    /**
     * Return the formatter manager
     * @return FormatterManager
     */
    public function formatterManager()
    {
        return $this->formatterManager;
    }

    public function initializeHook(
        InputInterface $input,
        $names,
        AnnotationData $annotationData
    ) {
        $initializeDispatcher = new InitializeHookDispatcher($this->hookManager(), $names);
        return $initializeDispatcher->initialize($input, $annotationData);
    }

    public function optionsHook(
        AnnotatedCommand $command,
        $names,
        AnnotationData $annotationData
    ) {
        $optionsDispatcher = new OptionsHookDispatcher($this->hookManager(), $names);
        $optionsDispatcher->getOptions($command, $annotationData);
    }

    public function interact(
        InputInterface $input,
        OutputInterface $output,
        $names,
        AnnotationData $annotationData
    ) {
        $interactDispatcher = new InteractHookDispatcher($this->hookManager(), $names);
        return $interactDispatcher->interact($input, $output, $annotationData);
    }

    public function process(
        OutputInterface $output,
        $names,
        $commandCallback,
        CommandData $commandData
    ) {
        $result = [];
        try {
            $result = $this->validateRunAndAlter(
                $names,
                $commandCallback,
                $commandData
            );
            return $this->handleResults($output, $names, $result, $commandData);
        } catch (\Exception $e) {
            $result = $this->commandErrorForException($e);
            return $this->handleResults($output, $names, $result, $commandData);
        }
    }

    public function validateRunAndAlter(
        $names,
        $commandCallback,
        CommandData $commandData
    ) {
        // Validators return any object to signal a validation error;
        // if the return an array, it replaces the arguments.
        $validateDispatcher = new ValidateHookDispatcher($this->hookManager(), $names);
        $validated = $validateDispatcher->validate($commandData);
        if (is_object($validated)) {
            return $validated;
        }

        $replaceDispatcher = new ReplaceCommandHookDispatcher($this->hookManager(), $names);
        if ($this->logger) {
            $replaceDispatcher->setLogger($this->logger);
        }
        if ($replaceDispatcher->hasReplaceCommandHook()) {
            $commandCallback = $replaceDispatcher->getReplacementCommand($commandData);
        }

        // Run the command, alter the results, and then handle output and status
        $result = $this->runCommandCallback($commandCallback, $commandData);
        return $this->processResults($names, $result, $commandData);
    }

    public function processResults($names, $result, CommandData $commandData)
    {
        $processDispatcher = new ProcessResultHookDispatcher($this->hookManager(), $names);
        return $processDispatcher->process($result, $commandData);
    }

    /**
     * Handle the result output and status code calculation.
     */
    public function handleResults(OutputInterface $output, $names, $result, CommandData $commandData)
    {
        $statusCodeDispatcher = new StatusDeterminerHookDispatcher($this->hookManager(), $names);
        $status = $statusCodeDispatcher->determineStatusCode($result);
        // If the result is an integer and no separate status code was provided, then use the result as the status and do no output.
        if (is_integer($result) && !isset($status)) {
            return $result;
        }
        $status = $this->interpretStatusCode($status);

        // Get the structured output, the output stream and the formatter
        $extractDispatcher = new ExtracterHookDispatcher($this->hookManager(), $names);
        $structuredOutput = $extractDispatcher->extractOutput($result);
        $output = $this->chooseOutputStream($output, $status);
        if ($status != 0) {
            return $this->writeErrorMessage($output, $status, $structuredOutput, $result);
        }
        if ($this->dataCanBeFormatted($structuredOutput) && isset($this->formatterManager)) {
            return $this->writeUsingFormatter($output, $structuredOutput, $commandData);
        }
        return $this->writeCommandOutput($output, $structuredOutput);
    }

    protected function dataCanBeFormatted($structuredOutput)
    {
        if (!isset($this->formatterManager)) {
            return false;
        }
        return
            is_object($structuredOutput) ||
            is_array($structuredOutput);
    }

    /**
     * Run the main command callback
     */
    protected function runCommandCallback($commandCallback, CommandData $commandData)
    {
        $result = false;
        try {
            $args = $commandData->getArgsAndOptions();
            $result = call_user_func_array($commandCallback, $args);
        } catch (\Exception $e) {
            $result = $this->commandErrorForException($e);
        }
        return $result;
    }

    /**
     * Determine the formatter that should be used to render
     * output.
     *
     * If the user specified a format via the --format option,
     * then always return that.  Otherwise, return the default
     * format, unless --pipe was specified, in which case
     * return the default pipe format, format-pipe.
     *
     * n.b. --pipe is a handy option introduced in Drush 2
     * (or perhaps even Drush 1) that indicates that the command
     * should select the output format that is most appropriate
     * for use in scripts (e.g. to pipe to another command).
     *
     * @return string
     */
    protected function getFormat(FormatterOptions $options)
    {
        // In Symfony Console, there is no way for us to differentiate
        // between the user specifying '--format=table', and the user
        // not specifying --format when the default value is 'table'.
        // Therefore, we must make --field always override --format; it
        // cannot become the default value for --format.
        if ($options->get('field')) {
            return 'string';
        }
        $defaults = [];
        if ($options->get('pipe')) {
            return $options->get('pipe-format', [], 'tsv');
        }
        return $options->getFormat($defaults);
    }

    /**
     * Determine whether we should use stdout or stderr.
     */
    protected function chooseOutputStream(OutputInterface $output, $status)
    {
        // If the status code indicates an error, then print the
        // result to stderr rather than stdout
        if ($status && ($output instanceof ConsoleOutputInterface)) {
            return $output->getErrorOutput();
        }
        return $output;
    }

    /**
     * Call the formatter to output the provided data.
     */
    protected function writeUsingFormatter(OutputInterface $output, $structuredOutput, CommandData $commandData)
    {
        $formatterOptions = $this->createFormatterOptions($commandData);
        $format = $this->getFormat($formatterOptions);
        $this->formatterManager->write(
            $output,
            $format,
            $structuredOutput,
            $formatterOptions
        );
        return 0;
    }

    /**
     * Create a FormatterOptions object for use in writing the formatted output.
     * @param CommandData $commandData
     * @return FormatterOptions
     */
    protected function createFormatterOptions($commandData)
    {
        $options = $commandData->input()->getOptions();
        $formatterOptions = new FormatterOptions($commandData->annotationData()->getArrayCopy(), $options);
        foreach ($this->prepareOptionsList as $preparer) {
            $preparer->prepare($commandData, $formatterOptions);
        }
        return $formatterOptions;
    }

    /**
     * Description
     * @param OutputInterface $output
     * @param int $status
     * @param string $structuredOutput
     * @param mixed $originalResult
     * @return type
     */
    protected function writeErrorMessage($output, $status, $structuredOutput, $originalResult)
    {
        if (isset($this->displayErrorFunction)) {
            call_user_func($this->displayErrorFunction, $output, $structuredOutput, $status, $originalResult);
        } else {
            $this->writeCommandOutput($output, $structuredOutput);
        }
        return $status;
    }

    /**
     * If the result object is a string, then print it.
     */
    protected function writeCommandOutput(
        OutputInterface $output,
        $structuredOutput
    ) {
        // If there is no formatter, we will print strings,
        // but can do no more than that.
        if (is_string($structuredOutput)) {
            $output->writeln($structuredOutput);
        }
        return 0;
    }

    /**
     * If a status code was set, then return it; otherwise,
     * presume success.
     */
    protected function interpretStatusCode($status)
    {
        if (isset($status)) {
            return $status;
        }
        return 0;
    }
}
