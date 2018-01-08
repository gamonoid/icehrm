# Change Log

### 2.8.2 - 29 Nov 2017

- Allow Symfony 4 components.

### 2.8.1 - 16 Oct 2017

- Add hook methods to allow Symfony command events to be added directly to the hook manager, givig better control of hook order. (#131)

### 2.8.0 - 13 Oct 2017

- Remove phpdocumentor/reflection-docblock in favor of using a bespoke parser (#130)

### 2.7.0 - 18 Sept 2017

- Add support for options with a default value of 'true' (#119)
- BUGFIX: Improve handling of options with optional values, which previously was not working correctly. (#118)

### 2.6.1 - 18 Sep 2017

- Reverts to contents of the 2.4.13 release.

### 2.5.0 & 2.5.1 - 17 Sep 2017

- BACKED OUT. These releases accidentally introduced breaking changes.

### 2.4.13 - 28 Aug 2017

- Add a followLinks() method (#108)

### 2.4.12 - 24 Aug 2017

- BUGFIX: Allow annotated commands to directly use InputInterface and OutputInterface (#106)

### 2.4.11 - 27 July 2017

- Back out #102: do not change behavior of word wrap based on STDOUT redirection.

### 2.4.10 - 21 July 2017

- Add a method CommandProcessor::setPassExceptions() to allow applicationsto prevent the command processor from catching exceptions thrown by command methods and hooks. (#103)

### 2.4.9 - 20 Jul 2017

- Automatically disable wordwrap when the terminal is not connected to STDOUT (#102)

### 2.4.8 - 3 Apr 2017

- Allow multiple annotations with the same key. These are returned as a csv, or, alternately, can be accessed as an array via the new accessor.
- Unprotect two methods for benefit of Drush help. (#99)
- BUGFIX: Remove symfony/console pin (#100)

### 2.4.7 & 2.4.6 - 17 Mar 2017

- Avoid wrapping help text (#93)
- Pin symfony/console to version < 3.2.5 (#94)
- Add getExampleUsages() to AnnotatedCommand. (#92)

### 2.4.5 - 28 Feb 2017

- Ensure that placeholder entries are written into the commandfile cache. (#86)

### 2.4.4 - 27 Feb 2017

- BUGFIX: Avoid rewriting the command cache unless something has changed.
- BUGFIX: Ensure that the default value of options are correctly cached.

### 2.4.2 - 24 Feb 2017

- Add SimpleCacheInterface as a documentation interface (not enforced).

### 2.4.1 - 20 Feb 2017

- Support array options: multiple options on the commandline may be passed in to options array as an array of values.
- Add php 7.1 to the test matrix.

### 2.4.0 - 3 Feb 2017

- Automatically rebuild cached commandfile data when commandfile changes.
- Provide path to command file in AnnotationData objects.
- Bugfix: Add dynamic options when user runs '--help my:command' (previously, only 'help my:command' worked).
- Bugfix: Include description of last parameter in help (was omitted if no options present)
- Add Windows testing with Appveyor


### 2.3.0 - 19 Jan 2017

- Add a command info cache to improve performance of applications with many commands
- Bugfix: Allow trailing backslashes in namespaces in CommandFileDiscovery
- Bugfix: Rename @topic to @topics


### 2.2.0 - 23 November 2016

- Support custom events
- Add xml and json output for replacement help command. Text / html format for replacement help command not available yet.


### 2.1.0 - 14 November 2016

- Add support for output formatter wordwrapping 
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
