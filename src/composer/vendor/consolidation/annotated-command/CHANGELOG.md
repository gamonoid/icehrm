# Change Log


### 2.0.1 ~ 2.0.3 - 14 November 2016

- Fix version requirement for output-formatters in composer.json
- Use output-formatters ~3
- Move php_codesniffer back to require-dev (moved to require by mistake)


### 2.0.0 - 30 September 2016

- **Breaking** Hooks with no command name now apply to all commands defined in the same class. This is a change of behavior from the 1.x branch, where hooks with no command name applied to a command with the same method name in a *different* class.
- **Breaking** The interfaces ValidatorInterface, ProcessResultInterface and AlterResultInterface have been updated to be passed a CommandData object, which contains an Input and Output object, plus the AnnotationData.
- **Breaking** The Symfony Command Event hook has been renamed to COMMAND_EVENT.  There is a new COMMAND hook that behaves like the existing Drush command hook (i.e. the post-command event is called after the primary command method runs).
- Add an accessor function AnnotatedCommandFactory::setIncludeAllPublicMethods() to control whether all public methods of a command class, or only those with a @command annotation will be treated as commands. Default remains to treat all public methods as commands. The parameters to AnnotatedCommandFactory::createCommandsFromClass() and AnnotatedCommandFactory::createCommandsFromClassInfo() still behave the same way, but are deprecated. If omitted, the value set by the accessor will be used.
- @option and @usage annotations provided with @hook methods will be added to the help text of the command they hook.  This should be done if a hook needs to add a new option, e.g. to control the behavior of the hook.
- @option annotations can now be either `@option type $name description`, or just `@option name description`.
- `@hook option` can be used to programatically add options to a command.
- A CommandInfoAltererInterface can be added via AnnotatedCommandFactory::addCommandInfoAlterer(); it will be given the opportunity to adjust every CommandInfo object parsed from a command file prior to the creation of commands.
- AnnotatedCommandFactory::setIncludeAllPublicMethods(false) may be used to require methods to be annotated with @commnad in order to be considered commands. This is in preference to the existing parameters of various command-creation methods of AnnotatedCommandFactory, which are now all deprecated in favor of this setter function.
- If a --field option is given, it will also force the output format to 'string'.
- Setter methods more consistently return $this.
- Removed PassThroughArgsInput. This class was unnecessary.


### 1.4.0 - 13 September 2016

- Add basic annotation hook capability, to allow hook functions to be attached to commands with arbitrary annotations.


### 1.3.0 - 8 September 2016

- Add ComandFileDiscovery::setSearchDepth(). The search depth applies to each search location, unless there are no search locations, in which case it applies to the base directory.


### 1.2.0 - 2 August 2016

- Support both the 2.x and 3.x versions of phpdocumentor/reflection-docblock.
- Support php 5.4.
- **Bug** Do not allow an @param docblock comment for the options to override the meaning of the options.


### 1.1.0 - 6 July 2016

- Introduce AnnotatedCommandFactory::createSelectedCommandsFromClassInfo() method.


### 1.0.0 - 20 May 2016

- First stable release.
