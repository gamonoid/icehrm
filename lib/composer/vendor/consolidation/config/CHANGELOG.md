# Changelog

### 1.0.6 10/17/2017

* Add a 'Config::combine()' method for importing without overwriting.
* Factor out ArrayUtil as a reusable utility class.

### 1.0.4 10/16/2017

* BUGFIX: Go back to injecting boolean options only if their value is 'true'.

### 1.0.3 10/04/2017

* Add an EnvConfig utility class.
* BUGFIX: Fix bug in envKey calculation: it was missing its prefix.
* BUGFIX: Export must always return something.
* BUGFIX: Pass reference array through to Expander class.

### 1.0.2 09/16/2017

* BUGFIX: Allow global boolean options to have either `true` or `false` initial values.

### 1.0.1 07/28/2017

* Inject default values into InputOption objects when 'help' command executed.

### 1.0.0 06/28/2017

* Initial release

