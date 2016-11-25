# PHP-Markdown-Documentation-Generator

Documentation is just as important as the code it's refering to. With this command line tool 
you will be able to write your documentation once, and only once! 

This project will write a single-page markdown-formatted API document based on the DocBlock comments in your source code. The [phpdoc](http://www.phpdoc.org/) standard is used.

![Travis](https://travis-ci.org/victorjonsson/PHP-Markdown-Documentation-Generator.svg)

### Example

Let's say you have your PHP classes in a directory named "src". Each class has its own file that is named after the class.

```
- src/
  - MyObject.php
  - OtherObject.php
```

Write your code documentation following the standard set by [phpdoc](http://www.phpdoc.org/). 

```php
namespace Acme;

/**
 * This is a description of this class
 */
class MyObject {
   
   /**
    * This is a function description
    * @param string $str
    * @param array $arr
    * @return Acme\OtherObject
    */
   function someFunc($str, $arr=array()) {
   
   }
}
```

Then, running `$ phpdocs-md generate src > api.md` will write your API documentation to the file api.md.

[Here you can see a rendered example](https://github.com/victorjonsson/PHP-Markdown-Documentation-Generator/blob/master/api.md)

Only public and protected functions will be a part of the documentation, but you can also add `@ignore` to any function or class to exclude it from the docs. Phpdocs-md will try to guess the return type of functions that don't explicitly declare one. The program uses reflection to get as much information as possible out of the code so that functions that are missing DocBlock comments will still be included in the generated documentation.

### Requirements

- PHP version >= 5.3.2
- Reflection must be enabled in php.ini
- Each class must be defined in its own file with the file name being the same as the class name
- The project should use [Composer](https://getcomposer.org/)

### Installation / Usage

This command line tool can be installed using [composer](https://getcomposer.org/).

From the local working directory of the project that you would like to document, run:
```
$ composer require --dev victorjonsson/markdowndocs
```
This will add victorjonsson/markdowndocs to the `require-dev` section of your project's composer.json file.  The phpdocs-md executable will automatically be copied to your project's `vendor/bin` directory.

##### Generating docs

The `generate` command generates your project's API documentation file. The command line tool needs to know whether you want to generate docs for a certain class, or if it should process every class in a specified directory search path.

```
# Generate docs for a certain class
$ ./vendor/bin/phpdocs-md generate Acme\\NS\\MyClass 

# Generate docs for several classes (comma separated)
$ ./vendor/bin/phpdocs-md generate Acme\\NS\\MyClass,Acme\\OtherNS\\OtherClass 

# Generate docs for all classes in a source directory
$ ./vendor/bin/phpdocs-md generate includes/src

# Generate docs for all classes in a source directory and send output to the file api.md
$ ./vendor/bin/phpdocs-md generate includes/src > api.md
```

* Note that any class to be documented must be loadable using the autoloader provided by composer. *

##### Bootstrapping

If you are not using the composer autoloader, or if there is something else that needs to be done before your classes can be instantiated, then you may request phpdocs-md to load a php bootstrap file prior to generating the docs

`$ ./vendor/bin/phpdocs-md generate --bootstrap=includes/init.php includes/src > api.md`

##### Excluding directories

You can tell the command line tool to ignore certain directories in your class path by using the `--ignore` option.

`$ ./phpdocs-md generate --ignore=test,examples includes/src > api.md`

