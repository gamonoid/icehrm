# Changelog

### 1.2.1 2019-03-03

* Add ConfigRuntimeInterface, and implement it in ConfigOverlay (#27)
* Add ConfigOverlay::exportAll()

### 1.2.0 2019-02-15

* Add ConfigAwareInterface / ConfigAwareTrait

### 1.1.1 2018-10-24

* Add interpolation to Config objects (#23)

### 1.1.0 2018-08-07

* Selective distinct/combine merge strategy. (#22)

### 1.0.11 2018-05-26

* BUGFIX: Ensure that duplicate keys added to different contexts in a config overlay only appear once in the final export. (#21)

### 1.0.10 2018-05-25

* Rename g-1-a/composer-test-scenarios to g1a/composer-test-scenarios (#20)

### 1.0.9 2017-12-22

* Make yaml component optional. (#17)

### 1.0.8 2017-12-16

* Use test scenarios to test multiple versions of Symfony. (#14) & (#15)
* Fix defaults to work with DotAccessData by thomscode (#13)

### 1.0.7 2017-10-24

* Deprecate Config::import(); recommand Config::replace() instead.

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

