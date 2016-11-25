<?php
namespace Consolidation\AnnotatedCommand;

use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Consolidation\AnnotatedCommand\Options\AutomaticOptionsProviderInterface;
use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Consolidation\OutputFormatters\Options\FormatterOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The AnnotatedCommandFactory creates commands for your application.
 * Use with a Dependency Injection Container and the CommandFactory.
 * Alternately, use the CommandFileDiscovery to find commandfiles, and
 * then use AnnotatedCommandFactory::createCommandsFromClass() to create
 * commands.  See the README for more information.
 *
 * @package Consolidation\AnnotatedCommand
 */
class AnnotatedCommandFactory implements AutomaticOptionsProviderInterface
{
    /** var CommandProcessor */
    protected $commandProcessor;

    /** var CommandCreationListenerInterface[] */
    protected $listeners = [];

    /** var AutomaticOptionsProvider[] */

    protected $automaticOptionsProviderList = [];

    /** var boolean */
    protected $includeAllPublicMethods = true;

    /** var CommandInfoAltererInterface */
    protected $commandInfoAlterers = [];

    public function __construct()
    {
        $this->commandProcessor = new CommandProcessor(new HookManager());
        $this->addAutomaticOptionProvider($this);
    }

    public function setCommandProcessor(CommandProcessor $commandProcessor)
    {
        $this->commandProcessor = $commandProcessor;
        return $this;
    }

    /**
     * @return CommandProcessor
     */
    public function commandProcessor()
    {
        return $this->commandProcessor;
    }

    /**
     * Set the 'include all public methods flag'. If true (the default), then
     * every public method of each commandFile will be used to create commands.
     * If it is false, then only those public methods annotated with @command
     * or @name (deprecated) will be used to create commands.
     */
    public function setIncludeAllPublicMethods($includeAllPublicMethods)
    {
        $this->includeAllPublicMethods = $includeAllPublicMethods;
        return $this;
    }

    public function getIncludeAllPublicMethods()
    {
        return $this->includeAllPublicMethods;
    }

    /**
     * @return HookManager
     */
    public function hookManager()
    {
        return $this->commandProcessor()->hookManager();
    }

    /**
     * Add a listener that is notified immediately before the command
     * factory creates commands from a commandFile instance.  This
     * listener can use this opportunity to do more setup for the commandFile,
     * and so on.
     *
     * @param CommandCreationListenerInterface $listener
     */
    public function addListener(CommandCreationListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * Call all command creation listeners
     *
     * @param object $commandFileInstance
     */
    protected function notify($commandFileInstance)
    {
        foreach ($this->listeners as $listener) {
            $listener->notifyCommandFileAdded($commandFileInstance);
        }
    }

    public function addAutomaticOptionProvider(AutomaticOptionsProviderInterface $optionsProvider)
    {
        $this->automaticOptionsProviderList[] = $optionsProvider;
    }

    public function addCommandInfoAlterer(CommandInfoAltererInterface $alterer)
    {
        $this->commandInfoAlterers[] = $alterer;
    }

    /**
     * n.b. This registers all hooks from the commandfile instance as a side-effect.
     */
    public function createCommandsFromClass($commandFileInstance, $includeAllPublicMethods = null)
    {
        // Deprecated: avoid using the $includeAllPublicMethods in favor of the setIncludeAllPublicMethods() accessor.
        if (!isset($includeAllPublicMethods)) {
            $includeAllPublicMethods = $this->getIncludeAllPublicMethods();
        }
        $this->notify($commandFileInstance);
        $commandInfoList = $this->getCommandInfoListFromClass($commandFileInstance);
        $this->registerCommandHooksFromClassInfo($commandInfoList, $commandFileInstance);
        return $this->createCommandsFromClassInfo($commandInfoList, $commandFileInstance, $includeAllPublicMethods);
    }

    public function getCommandInfoListFromClass($classNameOrInstance)
    {
        $commandInfoList = [];

        // Ignore special functions, such as __construct and __call, which
        // can never be commands.
        $commandMethodNames = array_filter(
            get_class_methods($classNameOrInstance) ?: [],
            function ($m) {
                return !preg_match('#^_#', $m);
            }
        );

        foreach ($commandMethodNames as $commandMethodName) {
            $commandInfoList[] = new CommandInfo($classNameOrInstance, $commandMethodName);
        }

        return $commandInfoList;
    }

    public function createCommandInfo($classNameOrInstance, $commandMethodName)
    {
        return new CommandInfo($classNameOrInstance, $commandMethodName);
    }

    public function createCommandsFromClassInfo($commandInfoList, $commandFileInstance, $includeAllPublicMethods = null)
    {
        // Deprecated: avoid using the $includeAllPublicMethods in favor of the setIncludeAllPublicMethods() accessor.
        if (!isset($includeAllPublicMethods)) {
            $includeAllPublicMethods = $this->getIncludeAllPublicMethods();
        }
        return $this->createSelectedCommandsFromClassInfo(
            $commandInfoList,
            $commandFileInstance,
            function ($commandInfo) use ($includeAllPublicMethods) {
                return static::isCommandMethod($commandInfo, $includeAllPublicMethods);
            }
        );
    }

    public function createSelectedCommandsFromClassInfo($commandInfoList, $commandFileInstance, callable $commandSelector)
    {
        $commandList = [];

        foreach ($commandInfoList as $commandInfo) {
            if ($commandSelector($commandInfo)) {
                $command = $this->createCommand($commandInfo, $commandFileInstance);
                $commandList[] = $command;
            }
        }

        return $commandList;
    }

    public static function isCommandMethod($commandInfo, $includeAllPublicMethods)
    {
        // Ignore everything labeled @hook
        if ($commandInfo->hasAnnotation('hook')) {
            return false;
        }
        // Include everything labeled @command
        if ($commandInfo->hasAnnotation('command')) {
            return true;
        }
        // Skip anything named like an accessor ('get' or 'set')
        if (preg_match('#^(get[A-Z]|set[A-Z])#', $commandInfo->getMethodName())) {
            return false;
        }

        // Default to the setting of 'include all public methods'.
        return $includeAllPublicMethods;
    }

    public function registerCommandHooksFromClassInfo($commandInfoList, $commandFileInstance)
    {
        foreach ($commandInfoList as $commandInfo) {
            if ($commandInfo->hasAnnotation('hook')) {
                $this->registerCommandHook($commandInfo, $commandFileInstance);
            }
        }
    }

    /**
     * Register a command hook given the CommandInfo for a method.
     *
     * The hook format is:
     *
     *   @hook type name type
     *
     * For example, the pre-validate hook for the core:init command is:
     *
     *   @hook pre-validate core:init
     *
     * If no command name is provided, then this hook will affect every
     * command that is defined in the same file.
     *
     * If no hook is provided, then we will presume that ALTER_RESULT
     * is intended.
     *
     * @param CommandInfo $commandInfo Information about the command hook method.
     * @param object $commandFileInstance An instance of the CommandFile class.
     */
    public function registerCommandHook(CommandInfo $commandInfo, $commandFileInstance)
    {
        // Ignore if the command info has no @hook
        if (!$commandInfo->hasAnnotation('hook')) {
            return;
        }
        $hookData = $commandInfo->getAnnotation('hook');
        $hook = $this->getNthWord($hookData, 0, HookManager::ALTER_RESULT);
        $commandName = $this->getNthWord($hookData, 1);

        // Register the hook
        $callback = [$commandFileInstance, $commandInfo->getMethodName()];
        $this->commandProcessor()->hookManager()->add($callback, $hook, $commandName);

        // If the hook has options, then also register the commandInfo
        // with the hook manager, so that we can add options and such to
        // the commands they hook.
        if (!$commandInfo->options()->isEmpty()) {
            $this->commandProcessor()->hookManager()->recordHookOptions($commandInfo, $commandName);
        }
    }

    protected function getNthWord($string, $n, $default = '', $delimiter = ' ')
    {
        $words = explode($delimiter, $string);
        if (!empty($words[$n])) {
            return $words[$n];
        }
        return $default;
    }

    public function createCommand(CommandInfo $commandInfo, $commandFileInstance)
    {
        $this->alterCommandInfo($commandInfo, $commandFileInstance);
        $command = new AnnotatedCommand($commandInfo->getName());
        $commandCallback = [$commandFileInstance, $commandInfo->getMethodName()];
        $command->setCommandCallback($commandCallback);
        $command->setCommandProcessor($this->commandProcessor);
        $command->setCommandInfo($commandInfo);
        $automaticOptions = $this->callAutomaticOptionsProviders($commandInfo);
        $command->setCommandOptions($commandInfo, $automaticOptions);
        // Annotation commands are never bootstrap-aware, but for completeness
        // we will notify on every created command, as some clients may wish to
        // use this notification for some other purpose.
        $this->notify($command);
        return $command;
    }

    /**
     * Give plugins an opportunity to update the commandInfo
     */
    public function alterCommandInfo(CommandInfo $commandInfo, $commandFileInstance)
    {
        foreach ($this->commandInfoAlterers as $alterer) {
            $alterer->alterCommandInfo($commandInfo, $commandFileInstance);
        }
    }

    /**
     * Get the options that are implied by annotations, e.g. @fields implies
     * that there should be a --fields and a --format option.
     *
     * @return InputOption[]
     */
    public function callAutomaticOptionsProviders(CommandInfo $commandInfo)
    {
        $automaticOptions = [];
        foreach ($this->automaticOptionsProviderList as $automaticOptionsProvider) {
            $automaticOptions += $automaticOptionsProvider->automaticOptions($commandInfo);
        }
        return $automaticOptions;
    }

    /**
     * Get the options that are implied by annotations, e.g. @fields implies
     * that there should be a --fields and a --format option.
     *
     * @return InputOption[]
     */
    public function automaticOptions(CommandInfo $commandInfo)
    {
        $automaticOptions = [];
        $formatManager = $this->commandProcessor()->formatterManager();
        if ($formatManager) {
            $annotationData = $commandInfo->getAnnotations()->getArrayCopy();
            $formatterOptions = new FormatterOptions($annotationData);
            $dataType = $commandInfo->getReturnType();
            $automaticOptions = $formatManager->automaticOptions($formatterOptions, $dataType);
        }
        return $automaticOptions;
    }
}
