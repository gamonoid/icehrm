<?php
namespace Consolidation\AnnotatedCommand\Hooks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Consolidation\AnnotatedCommand\ExitCodeInterface;
use Consolidation\AnnotatedCommand\OutputDataInterface;
use Consolidation\AnnotatedCommand\AnnotationData;
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\CommandError;

/**
 * Manage named callback hooks
 */
class HookManager implements EventSubscriberInterface
{
    protected $hooks = [];
    /** var CommandInfo[] */
    protected $hookOptions = [];

    const PRE_COMMAND_EVENT = 'pre-command-event';
    const COMMAND_EVENT = 'command-event';
    const POST_COMMAND_EVENT = 'post-command-event';
    const PRE_OPTION_HOOK = 'pre-option';
    const OPTION_HOOK = 'option';
    const POST_OPTION_HOOK = 'post-option';
    const PRE_INITIALIZE = 'pre-init';
    const INITIALIZE = 'init';
    const POST_INITIALIZE = 'post-init';
    const PRE_INTERACT = 'pre-interact';
    const INTERACT = 'interact';
    const POST_INTERACT = 'post-interact';
    const PRE_ARGUMENT_VALIDATOR = 'pre-validate';
    const ARGUMENT_VALIDATOR = 'validate';
    const POST_ARGUMENT_VALIDATOR = 'post-validate';
    const PRE_COMMAND_HOOK = 'pre-command';
    const COMMAND_HOOK = 'command';
    const POST_COMMAND_HOOK = 'post-command';
    const PRE_PROCESS_RESULT = 'pre-process';
    const PROCESS_RESULT = 'process';
    const POST_PROCESS_RESULT = 'post-process';
    const PRE_ALTER_RESULT = 'pre-alter';
    const ALTER_RESULT = 'alter';
    const POST_ALTER_RESULT = 'post-alter';
    const STATUS_DETERMINER = 'status';
    const EXTRACT_OUTPUT = 'extract';

    public function __construct()
    {
    }

    public function getAllHooks()
    {
        return $this->hooks;
    }

    /**
     * Add a hook
     *
     * @param mixed $callback The callback function to call
     * @param string   $hook     The name of the hook to add
     * @param string   $name     The name of the command to hook
     *   ('*' for all)
     */
    public function add(callable $callback, $hook, $name = '*')
    {
        if (empty($name)) {
            $name = static::getClassNameFromCallback($callback);
        }
        $this->hooks[$name][$hook][] = $callback;
        return $this;
    }

    public function recordHookOptions($commandInfo, $name)
    {
        $this->hookOptions[$name][] = $commandInfo;
        return $this;
    }

    public static function getNames($command, $callback)
    {
        return array_filter(
            array_merge(
                static::getNamesUsingCommands($command),
                [static::getClassNameFromCallback($callback)]
            )
        );
    }

    protected static function getNamesUsingCommands($command)
    {
        return array_merge(
            [$command->getName()],
            $command->getAliases()
        );
    }

    /**
     * If a command hook does not specify any particular command
     * name that it should be attached to, then it will be applied
     * to every command that is defined in the same class as the hook.
     * This is controlled by using the namespace + class name of
     * the implementing class of the callback hook.
     */
    protected static function getClassNameFromCallback($callback)
    {
        if (!is_array($callback)) {
            return '';
        }
        $reflectionClass = new \ReflectionClass($callback[0]);
        return $reflectionClass->getName();
    }

    /**
     * Add an configuration provider hook
     *
     * @param type InitializeHookInterface $provider
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addInitializeHook(InitializeHookInterface $initializeHook, $name = '*')
    {
        $this->hooks[$name][self::INITIALIZE][] = $initializeHook;
        return $this;
    }

    /**
     * Add an option hook
     *
     * @param type ValidatorInterface $validator
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addOptionHook(OptionHookInterface $interactor, $name = '*')
    {
        $this->hooks[$name][self::INTERACT][] = $interactor;
        return $this;
    }

    /**
     * Add an interact hook
     *
     * @param type ValidatorInterface $validator
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addInteractor(InteractorInterface $interactor, $name = '*')
    {
        $this->hooks[$name][self::INTERACT][] = $interactor;
        return $this;
    }

    /**
     * Add a pre-validator hook
     *
     * @param type ValidatorInterface $validator
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addPreValidator(ValidatorInterface $validator, $name = '*')
    {
        $this->hooks[$name][self::PRE_ARGUMENT_VALIDATOR][] = $validator;
        return $this;
    }

    /**
     * Add a validator hook
     *
     * @param type ValidatorInterface $validator
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addValidator(ValidatorInterface $validator, $name = '*')
    {
        $this->hooks[$name][self::ARGUMENT_VALIDATOR][] = $validator;
        return $this;
    }

    /**
     * Add a pre-command hook.  This is the same as a validator hook, except
     * that it will run after all of the post-validator hooks.
     *
     * @param type ValidatorInterface $preCommand
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addPreCommandHook(ValidatorInterface $preCommand, $name = '*')
    {
        $this->hooks[$name][self::PRE_COMMAND_HOOK][] = $preCommand;
        return $this;
    }

    /**
     * Add a post-command hook.  This is the same as a pre-process hook,
     * except that it will run before the first pre-process hook.
     *
     * @param type ProcessResultInterface $postCommand
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addPostCommandHook(ProcessResultInterface $postCommand, $name = '*')
    {
        $this->hooks[$name][self::POST_COMMAND_HOOK][] = $postCommand;
        return $this;
    }

    /**
     * Add a result processor.
     *
     * @param type ProcessResultInterface $resultProcessor
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addResultProcessor(ProcessResultInterface $resultProcessor, $name = '*')
    {
        $this->hooks[$name][self::PROCESS_RESULT][] = $resultProcessor;
        return $this;
    }

    /**
     * Add a result alterer. After a result is processed
     * by a result processor, an alter hook may be used
     * to convert the result from one form to another.
     *
     * @param type AlterResultInterface $resultAlterer
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addAlterResult(AlterResultInterface $resultAlterer, $name = '*')
    {
        $this->hooks[$name][self::ALTER_RESULT][] = $resultAlterer;
        return $this;
    }

    /**
     * Add a status determiner. Usually, a command should return
     * an integer on error, or a result object on success (which
     * implies a status code of zero). If a result contains the
     * status code in some other field, then a status determiner
     * can be used to call the appropriate accessor method to
     * determine the status code.  This is usually not necessary,
     * though; a command that fails may return a CommandError
     * object, which contains a status code and a result message
     * to display.
     * @see CommandError::getExitCode()
     *
     * @param type StatusDeterminerInterface $statusDeterminer
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addStatusDeterminer(StatusDeterminerInterface $statusDeterminer, $name = '*')
    {
        $this->hooks[$name][self::STATUS_DETERMINER][] = $statusDeterminer;
        return $this;
    }

    /**
     * Add an output extractor. If a command returns an object
     * object, by default it is passed directly to the output
     * formatter (if in use) for rendering. If the result object
     * contains more information than just the data to render, though,
     * then an output extractor can be used to call the appopriate
     * accessor method of the result object to get the data to
     * rendered.  This is usually not necessary, though; it is preferable
     * to have complex result objects implement the OutputDataInterface.
     * @see OutputDataInterface::getOutputData()
     *
     * @param type ExtractOutputInterface $outputExtractor
     * @param type $name The name of the command to hook
     *   ('*' for all)
     */
    public function addOutputExtractor(ExtractOutputInterface $outputExtractor, $name = '*')
    {
        $this->hooks[$name][self::EXTRACT_OUTPUT][] = $outputExtractor;
        return $this;
    }

    public function initializeHook(
        InputInterface $input,
        $names,
        AnnotationData $annotationData
    ) {
        $providers = $this->getInitializeHooks($names, $annotationData);
        foreach ($providers as $provider) {
            $this->callInjectConfigurationHook($provider, $input, $annotationData);
        }
    }

    public function optionsHook(
        \Consolidation\AnnotatedCommand\AnnotatedCommand $command,
        $names,
        AnnotationData $annotationData
    ) {
        $optionHooks = $this->getOptionHooks($names, $annotationData);
        foreach ($optionHooks as $optionHook) {
            $this->callOptionHook($optionHook, $command, $annotationData);
        }
        $commandInfoList = $this->getHookOptionsForCommand($command);
        $command->optionsHookForHookAnnotations($commandInfoList);
    }

    public function getHookOptionsForCommand($command)
    {
        $names = $this->addWildcardHooksToNames($command->getNames(), $command->getAnnotationData());
        return $this->getHookOptions($names);
    }

    /**
     * @return CommandInfo[]
     */
    public function getHookOptions($names)
    {
        $result = [];
        foreach ($names as $name) {
            if (isset($this->hookOptions[$name])) {
                $result = array_merge($result, $this->hookOptions[$name]);
            }
        }
        return $result;
    }

    public function interact(
        InputInterface $input,
        OutputInterface $output,
        $names,
        AnnotationData $annotationData
    ) {
        $interactors = $this->getInteractors($names, $annotationData);
        foreach ($interactors as $interactor) {
            $this->callInteractor($interactor, $input, $output, $annotationData);
        }
    }

    public function validateArguments($names, CommandData $commandData)
    {
        $validators = $this->getValidators($names, $commandData->annotationData());
        foreach ($validators as $validator) {
            $validated = $this->callValidator($validator, $commandData);
            if ($validated === false) {
                return new CommandError();
            }
            if (is_object($validated)) {
                return $validated;
            }
        }
    }

    /**
     * Process result and decide what to do with it.
     * Allow client to add transformation / interpretation
     * callbacks.
     */
    public function alterResult($names, $result, CommandData $commandData)
    {
        $processors = $this->getProcessResultHooks($names, $commandData->annotationData());
        foreach ($processors as $processor) {
            $result = $this->callProcessor($processor, $result, $commandData);
        }
        $alterers = $this->getAlterResultHooks($names, $commandData->annotationData());
        foreach ($alterers as $alterer) {
            $result = $this->callProcessor($alterer, $result, $commandData);
        }

        return $result;
    }

    /**
     * Call all status determiners, and see if any of them
     * know how to convert to a status code.
     */
    public function determineStatusCode($names, $result)
    {
        // If the result (post-processing) is an object that
        // implements ExitCodeInterface, then we will ask it
        // to give us the status code.
        if ($result instanceof ExitCodeInterface) {
            return $result->getExitCode();
        }

        // If the result does not implement ExitCodeInterface,
        // then we'll see if there is a determiner that can
        // extract a status code from the result.
        $determiners = $this->getStatusDeterminers($names);
        foreach ($determiners as $determiner) {
            $status = $this->callDeterminer($determiner, $result);
            if (isset($status)) {
                return $status;
            }
        }
    }

    /**
     * Convert the result object to printable output in
     * structured form.
     */
    public function extractOutput($names, $result)
    {
        if ($result instanceof OutputDataInterface) {
            return $result->getOutputData();
        }

        $extractors = $this->getOutputExtractors($names);
        foreach ($extractors as $extractor) {
            $structuredOutput = $this->callExtractor($extractor, $result);
            if (isset($structuredOutput)) {
                return $structuredOutput;
            }
        }

        return $result;
    }

    protected function getCommandEventHooks($names)
    {
        return $this->getHooks(
            $names,
            [
                self::PRE_COMMAND_EVENT,
                self::COMMAND_EVENT,
                self::POST_COMMAND_EVENT
            ]
        );
    }

    protected function getInitializeHooks($names, AnnotationData $annotationData)
    {
        return $this->getHooks(
            $names,
            [
                self::PRE_INITIALIZE,
                self::INITIALIZE,
                self::POST_INITIALIZE
            ],
            $annotationData
        );
    }

    protected function getOptionHooks($names, AnnotationData $annotationData)
    {
        return $this->getHooks(
            $names,
            [
                self::PRE_OPTION_HOOK,
                self::OPTION_HOOK,
                self::POST_OPTION_HOOK
            ],
            $annotationData
        );
    }

    protected function getInteractors($names, AnnotationData $annotationData)
    {
        return $this->getHooks(
            $names,
            [
                self::PRE_INTERACT,
                self::INTERACT,
                self::POST_INTERACT
            ],
            $annotationData
        );
    }

    protected function getValidators($names, AnnotationData $annotationData)
    {
        return $this->getHooks(
            $names,
            [
                self::PRE_ARGUMENT_VALIDATOR,
                self::ARGUMENT_VALIDATOR,
                self::POST_ARGUMENT_VALIDATOR,
                self::PRE_COMMAND_HOOK,
                self::COMMAND_HOOK,
            ],
            $annotationData
        );
    }

    protected function getProcessResultHooks($names, AnnotationData $annotationData)
    {
        return $this->getHooks(
            $names,
            [
                self::PRE_PROCESS_RESULT,
                self::PROCESS_RESULT,
                self::POST_PROCESS_RESULT
            ],
            $annotationData
        );
    }

    protected function getAlterResultHooks($names, AnnotationData $annotationData)
    {
        return $this->getHooks(
            $names,
            [
                self::PRE_ALTER_RESULT,
                self::ALTER_RESULT,
                self::POST_ALTER_RESULT,
                self::POST_COMMAND_HOOK,
            ],
            $annotationData
        );
    }

    protected function getStatusDeterminers($names)
    {
        return $this->getHooks(
            $names,
            [
                self::STATUS_DETERMINER,
            ]
        );
    }

    protected function getOutputExtractors($names)
    {
        return $this->getHooks(
            $names,
            [
                self::EXTRACT_OUTPUT,
            ]
        );
    }

    /**
     * Get a set of hooks with the provided name(s). Include the
     * pre- and post- hooks, and also include the global hooks ('*')
     * in addition to the named hooks provided.
     *
     * @param string|array $names The name of the function being hooked.
     * @param string[] $hooks A list of hooks (e.g. [HookManager::ALTER_RESULT])
     *
     * @return callable[]
     */
    public function getHooks($names, $hooks, $annotationData = null)
    {
        return $this->get($this->addWildcardHooksToNames($names, $annotationData), $hooks);
    }

    protected function addWildcardHooksToNames($names, $annotationData = null)
    {
        $names = array_merge(
            (array)$names,
            ($annotationData == null) ? [] : array_map(function ($item) {
                return "@$item";
            }, $annotationData->keys())
        );
        $names[] = '*';
        return array_unique($names);
    }

    /**
     * Get a set of hooks with the provided name(s).
     *
     * @param string|array $names The name of the function being hooked.
     * @param string[] $hooks The list of hook names (e.g. [HookManager::ALTER_RESULT])
     *
     * @return callable[]
     */
    public function get($names, $hooks)
    {
        $result = [];
        foreach ((array)$hooks as $hook) {
            foreach ((array)$names as $name) {
                $result = array_merge($result, $this->getHook($name, $hook));
            }
        }
        return $result;
    }

    /**
     * Get a single named hook.
     *
     * @param string $name The name of the hooked method
     * @param string $hook The specific hook name (e.g. alter)
     *
     * @return callable[]
     */
    protected function getHook($name, $hook)
    {
        if (isset($this->hooks[$name][$hook])) {
            return $this->hooks[$name][$hook];
        }
        return [];
    }

    protected function callInjectConfigurationHook($provider, $input, AnnotationData $annotationData)
    {
        if ($provider instanceof InitializeHookInterface) {
            return $provider->applyConfiguration($input, $annotationData);
        }
        if (is_callable($provider)) {
            return $provider($input, $annotationData);
        }
    }

    protected function callOptionHook($optionHook, $command, AnnotationData $annotationData)
    {
        if ($optionHook instanceof OptionHookInterface) {
            return $optionHook->getOptions($command, $annotationData);
        }
        if (is_callable($optionHook)) {
            return $optionHook($command, $annotationData);
        }
    }

    protected function callInteractor($interactor, $input, $output, AnnotationData $annotationData)
    {
        if ($interactor instanceof InteractorInterface) {
            return $interactor->interact($input, $output, $annotationData);
        }
        if (is_callable($interactor)) {
            return $interactor($input, $output, $annotationData);
        }
    }

    protected function callValidator($validator, CommandData $commandData)
    {
        if ($validator instanceof ValidatorInterface) {
            return $validator->validate($commandData);
        }
        if (is_callable($validator)) {
            return $validator($commandData);
        }
    }

    protected function callProcessor($processor, $result, CommandData $commandData)
    {
        $processed = null;
        if ($processor instanceof ProcessResultInterface) {
            $processed = $processor->process($result, $commandData);
        }
        if (is_callable($processor)) {
            $processed = $processor($result, $commandData);
        }
        if (isset($processed)) {
            return $processed;
        }
        return $result;
    }

    protected function callDeterminer($determiner, $result)
    {
        if ($determiner instanceof StatusDeterminerInterface) {
            return $determiner->determineStatusCode($result);
        }
        if (is_callable($determiner)) {
            return $determiner($result);
        }
    }

    protected function callExtractor($extractor, $result)
    {
        if ($extractor instanceof ExtractOutputInterface) {
            return $extractor->extractOutput($result);
        }
        if (is_callable($extractor)) {
            return $extractor($result);
        }
    }

    /**
     * @param ConsoleCommandEvent $event
     */
    public function callCommandEventHooks(ConsoleCommandEvent $event)
    {
        /* @var Command $command */
        $command = $event->getCommand();
        $names = [$command->getName()];
        $commandEventHooks = $this->getCommandEventHooks($names);
        foreach ($commandEventHooks as $commandEvent) {
            if (is_callable($commandEvent)) {
                $commandEvent($event);
            }
        }
    }

    public function findAndAddHookOptions($command)
    {
        if (!$command instanceof \Consolidation\AnnotatedCommand\AnnotatedCommand) {
            return;
        }
        $command->optionsHook();
    }

    /**
     * @{@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [ConsoleEvents::COMMAND => 'callCommandEventHooks'];
    }
}
