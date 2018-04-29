# Consolidation\OutputFormatters

Apply transformations to structured data to write output in different formats.

[![Travis CI](https://travis-ci.org/consolidation/output-formatters.svg?branch=master)](https://travis-ci.org/consolidation/output-formatters)
[![Windows CI](https://ci.appveyor.com/api/projects/status/umyfuujca6d2g2k6?svg=true)](https://ci.appveyor.com/project/greg-1-anderson/output-formatters)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/consolidation/output-formatters/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/consolidation/output-formatters/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/consolidation/output-formatters/badge.svg?branch=master)](https://coveralls.io/github/consolidation/output-formatters?branch=master)
[![License](https://poser.pugx.org/consolidation/output-formatters/license)](https://packagist.org/packages/consolidation/output-formatters)

## Component Status

Currently in use in [Robo](https://github.com/consolidation/Robo) (1.x+), [Drush](https://github.com/drush-ops/drush) (9.x+) and [Terminus](https://github.com/pantheon-systems/terminus) (1.x+).

## Motivation

Formatters are used to allow simple commandline tool commands to be implemented in a manner that is completely independent from the Symfony Console output interfaces.  A command receives its input via its method parameters, and returns its result as structured data (e.g. a php standard object or array).  The structured data is then formatted by a formatter, and the result is printed.

This process is managed by the [Consolidation/AnnotationCommand](https://github.com/consolidation/annotation-command) project.

## Library Usage

This is a library intended to be used in some other project.  Require from your composer.json file:
```
    "require": {
        "consolidation/output-formatters": "~3"
    },
```
If you require the feature that allows a data table to be automatically reduced to a single element when the `string` format is selected, then you should require version "~2" instead. In most other respects, the 1.x and 2.x versions are compatible. See the [CHANGELOG](CHANGELOG.md) for details.

## Example Formatter

Simple formatters are very easy to write.
```php
class YamlFormatter implements FormatterInterface
{
    public function write(OutputInterface $output, $data, FormatterOptions $options)
    {
        $dumper = new Dumper();
        $output->writeln($dumper->dump($data));
    }
}
```
The formatter is passed the set of `$options` that the user provided on the command line. These may optionally be examined to alter the behavior of the formatter, if needed.

Formatters may also implement different interfaces to alter the behavior of the rendering engine.

- `ValidationInterface`: A formatter should implement this interface to test to see if the provided data type can be processed. Any formatter that does **not** implement this interface is presumed to operate exclusively on php arrays. The formatter manager will always convert any provided data into an array before passing it to a formatter that does not implement ValidationInterface. These formatters will not be made available when the returned data type cannot be converted into an array.
- `OverrideRestructureInterface`: A formatter that implements this interface will be given the option to act on the provided structured data object before it restructures itself. See the section below on structured data for details on data restructuring.

## Structured Data

Most formatters will operate on any array or ArrayObject data. Some formatters require that specific data types be used. The following data types, all of which are subclasses of ArrayObject, are available for use:

- `RowsOfFields`: Each row contains an associative array of field:value pairs. It is also assumed that the fields of each row are the same for every row. This format is ideal for displaying in a table, with labels in the top row.
- `PropertyList`: Each row contains a field:value pair. Each field is unique. This format is ideal for displaying in a table, with labels in the first column and values in the second common.
- `ListDataFromKeys`: The result may be structured or unstructured data. When formatted with the --format=list formatter, the result will come from the array keys instead of the array values.
- `DOMDocument`: The standard PHP DOM document class may be used by functions that need to be able to presicely specify the exact attributes and children when the XML output format is used.

Commands that return table structured data with fields can be filtered and/or re-ordered by using the --fields option. These structured data types can also be formatted into a more generic type such as yaml or json, even after being filtered. This capabilities are not available if the data is returned in a bare php array.

The formatter manager will do its best to convert from an array to a DOMDocument, or from a DOMDocument to an array. It is important to note that a DOMDocument does not have a 1-to-1 mapping with a PHP array.  DOM elements contain both attributes and elements; a simple string property 'foo' may be represented either as <element foo="value"/> or <element><foo>value</foo></element>. Also, there may be multiple XML elements with the same name, whereas php associative arrays must always have unique keys. When converting from an array to a DOM document, the XML formatter will default to representing the string properties of an array as attributes of the element. Sets of elements with the same name may be used only if they are wrapped in a containing parent element--e.g. <element><foos><foo>one</foo><foo>two</foo></foos></element>. The XMLSchema class may be used to provide control over whether a property is rendered as an attribute or an element; however, in instances where the schema of the XML output is important, it is best for a function to return its result as a DOMDocument rather than an array.

A function may also define its own structured data type to return, usually by extending one of the types mentioned above.  If a custom structured data class implements an appropriate interface, then it can provide its own conversion function to one of the other data types:

- `DomDataInterface`: The data object may produce a DOMDocument via its `getDomData()` method, which will be called in any instance where a DOM document is needed--typically with the xml formatter.
- `ListDataInterface`: Any structured data object that implements this interface may use the `getListData()` method to produce the data set that will be used with the list formatter.
- `TableDataInterface`: Any structured data object that implements this interface may use the `getTableData()` method to produce the data set that will be used with the table formatter.
- `RenderCellInterface`: Structured data can also provide fine-grain control over how each cell in a table is rendered by implementing the RenderCellInterface.  See the section below for information on how this is done.
- `RestructureInterface`: The restructure interface can be implemented by a structured data object to restructure the data in response to options provided by the user. For example, the RowsOfFields and PropertyList data types use this interface to select and reorder the fields that were selected to appear in the output. Custom data types usually will not need to implement this interface, as they can inherit this behavior by extending RowsOfFields or PropertyList.

Additionally, structured data may be simplified to arrays via an array simplification object. To provide an array simplifier, implement `SimplifyToArrayInterface`, and register the simplifier via `FormatterManager::addSimplifier()`.

## Rendering Table Cells

By default, both the RowsOfFields and PropertyList data types presume that the contents of each cell is a simple string. To render more complicated cell contents, create a custom structured data class by extending either RowsOfFields or PropertyList, as desired, and implement RenderCellInterface.  The `renderCell()` method of your class will then be called for each cell, and you may act on it as appropriate.
```php
public function renderCell($key, $cellData, FormatterOptions $options, $rowData)
{
    // 'my-field' is always an array; convert it to a comma-separated list.
    if ($key == 'my-field') {
        return implode(',', $cellData);
    }
    // MyStructuredCellType has its own render function
    if ($cellData instanceof MyStructuredCellType) {
        return $cellData->myRenderfunction();
    }
    // If we do not recognize the cell data, return it unchnaged.
    return $cellData;
}
```
Note that if your data structure is printed with a formatter other than one such as the table formatter, it will still be reordered per the selected fields, but cell rendering will **not** be done.

## API Usage

It is recommended to use [Consolidation/AnnotationCommand](https://github.com/consolidation/annotation-command) to manage commands and formatters.  See the [AnnotationCommand API Usage](https://github.com/consolidation/annotation-command#api-usage) for details.

The FormatterManager may also be used directly, if desired:
```php
/**
 * @param OutputInterface $output Output stream to write to
 * @param string $format Data format to output in
 * @param mixed $structuredOutput Data to output
 * @param FormatterOptions $options Configuration informatin and User options
 */
function doFormat(
    OutputInterface $output,
    string $format, 
    array $data,
    FormatterOptions $options) 
{
    $formatterManager = new FormatterManager();
    $formatterManager->write(output, $format, $data, $options);
}
```
The FormatterOptions class is used to hold the configuration for the command output--things such as the default field list for tabular output, and so on--and also the current user-selected options to use during rendering, which may be provided using a Symfony InputInterface object:
```
public function execute(InputInterface $input, OutputInterface $output)
{
    $options = new FormatterOptions();
    $options
      ->setInput($input)
      ->setFieldLabels(['id' => 'ID', 'one' => 'First', 'two' => 'Second'])
      ->setDefaultStringField('id');

    $data = new RowsOfFields($this->getSomeData($input));
    return $this->doFormat($output, $options->getFormat(), $data, $options);
}
```
## Comparison to Existing Solutions

Formatters have been in use in Drush since version 5. Drush allows formatters to be defined using simple classes, some of which may be configured using metadata. Furthermore, nested formatters are also allowed; for example, a list formatter may be given another formatter to use to format each of its rows. Nested formatters also require nested metadata, causing the code that constructed formatters to become very complicated and unweildy.

Consolidation/OutputFormatters maintains the simplicity of use provided by Drush formatters, but abandons nested metadata configuration in favor of using code in the formatter to configure itself, in order to keep the code simpler.

