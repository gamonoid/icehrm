## Table of contents

- [\Consolidation\OutputFormatters\FormatterManager](#class-consolidationoutputformattersformattermanager)
- [\Consolidation\OutputFormatters\Exception\UnknownFormatException](#class-consolidationoutputformattersexceptionunknownformatexception)
- [\Consolidation\OutputFormatters\Exception\AbstractDataFormatException (abstract)](#class-consolidationoutputformattersexceptionabstractdataformatexception-abstract)
- [\Consolidation\OutputFormatters\Exception\IncompatibleDataException](#class-consolidationoutputformattersexceptionincompatibledataexception)
- [\Consolidation\OutputFormatters\Exception\InvalidFormatException](#class-consolidationoutputformattersexceptioninvalidformatexception)
- [\Consolidation\OutputFormatters\Exception\UnknownFieldException](#class-consolidationoutputformattersexceptionunknownfieldexception)
- [\Consolidation\OutputFormatters\Formatters\ListFormatter](#class-consolidationoutputformattersformatterslistformatter)
- [\Consolidation\OutputFormatters\Formatters\SectionsFormatter](#class-consolidationoutputformattersformatterssectionsformatter)
- [\Consolidation\OutputFormatters\Formatters\JsonFormatter](#class-consolidationoutputformattersformattersjsonformatter)
- [\Consolidation\OutputFormatters\Formatters\FormatterInterface (interface)](#interface-consolidationoutputformattersformattersformatterinterface)
- [\Consolidation\OutputFormatters\Formatters\CsvFormatter](#class-consolidationoutputformattersformatterscsvformatter)
- [\Consolidation\OutputFormatters\Formatters\SerializeFormatter](#class-consolidationoutputformattersformattersserializeformatter)
- [\Consolidation\OutputFormatters\Formatters\StringFormatter](#class-consolidationoutputformattersformattersstringformatter)
- [\Consolidation\OutputFormatters\Formatters\VarExportFormatter](#class-consolidationoutputformattersformattersvarexportformatter)
- [\Consolidation\OutputFormatters\Formatters\YamlFormatter](#class-consolidationoutputformattersformattersyamlformatter)
- [\Consolidation\OutputFormatters\Formatters\TableFormatter](#class-consolidationoutputformattersformatterstableformatter)
- [\Consolidation\OutputFormatters\Formatters\XmlFormatter](#class-consolidationoutputformattersformattersxmlformatter)
- [\Consolidation\OutputFormatters\Formatters\PrintRFormatter](#class-consolidationoutputformattersformattersprintrformatter)
- [\Consolidation\OutputFormatters\Formatters\RenderDataInterface (interface)](#interface-consolidationoutputformattersformattersrenderdatainterface)
- [\Consolidation\OutputFormatters\Formatters\TsvFormatter](#class-consolidationoutputformattersformatterstsvformatter)
- [\Consolidation\OutputFormatters\Options\OverrideOptionsInterface (interface)](#interface-consolidationoutputformattersoptionsoverrideoptionsinterface)
- [\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)
- [\Consolidation\OutputFormatters\StructuredData\ListDataInterface (interface)](#interface-consolidationoutputformattersstructureddatalistdatainterface)
- [\Consolidation\OutputFormatters\StructuredData\TableDataInterface (interface)](#interface-consolidationoutputformattersstructureddatatabledatainterface)
- [\Consolidation\OutputFormatters\StructuredData\HelpDocument](#class-consolidationoutputformattersstructureddatahelpdocument)
- [\Consolidation\OutputFormatters\StructuredData\OriginalDataInterface (interface)](#interface-consolidationoutputformattersstructureddataoriginaldatainterface)
- [\Consolidation\OutputFormatters\StructuredData\RowsOfFields](#class-consolidationoutputformattersstructureddatarowsoffields)
- [\Consolidation\OutputFormatters\StructuredData\RestructureInterface (interface)](#interface-consolidationoutputformattersstructureddatarestructureinterface)
- [\Consolidation\OutputFormatters\StructuredData\AbstractStructuredList (abstract)](#class-consolidationoutputformattersstructureddataabstractstructuredlist-abstract)
- [\Consolidation\OutputFormatters\StructuredData\ListDataFromKeys](#class-consolidationoutputformattersstructureddatalistdatafromkeys)
- [\Consolidation\OutputFormatters\StructuredData\PropertyList](#class-consolidationoutputformattersstructureddatapropertylist)
- [\Consolidation\OutputFormatters\StructuredData\RenderCellInterface (interface)](#interface-consolidationoutputformattersstructureddatarendercellinterface)
- [\Consolidation\OutputFormatters\StructuredData\CallableRenderer](#class-consolidationoutputformattersstructureddatacallablerenderer)
- [\Consolidation\OutputFormatters\StructuredData\RenderCellCollectionInterface (interface)](#interface-consolidationoutputformattersstructureddatarendercellcollectioninterface)
- [\Consolidation\OutputFormatters\StructuredData\AssociativeList](#class-consolidationoutputformattersstructureddataassociativelist)
- [\Consolidation\OutputFormatters\StructuredData\Xml\XmlSchemaInterface (interface)](#interface-consolidationoutputformattersstructureddataxmlxmlschemainterface)
- [\Consolidation\OutputFormatters\StructuredData\Xml\DomDataInterface (interface)](#interface-consolidationoutputformattersstructureddataxmldomdatainterface)
- [\Consolidation\OutputFormatters\StructuredData\Xml\XmlSchema](#class-consolidationoutputformattersstructureddataxmlxmlschema)
- [\Consolidation\OutputFormatters\Transformations\PropertyParser](#class-consolidationoutputformatterstransformationspropertyparser)
- [\Consolidation\OutputFormatters\Transformations\PropertyListTableTransformation](#class-consolidationoutputformatterstransformationspropertylisttabletransformation)
- [\Consolidation\OutputFormatters\Transformations\TableTransformation](#class-consolidationoutputformatterstransformationstabletransformation)
- [\Consolidation\OutputFormatters\Transformations\ReorderFields](#class-consolidationoutputformatterstransformationsreorderfields)
- [\Consolidation\OutputFormatters\Transformations\DomToArraySimplifier](#class-consolidationoutputformatterstransformationsdomtoarraysimplifier)
- [\Consolidation\OutputFormatters\Transformations\WordWrapper](#class-consolidationoutputformatterstransformationswordwrapper)
- [\Consolidation\OutputFormatters\Transformations\OverrideRestructureInterface (interface)](#interface-consolidationoutputformatterstransformationsoverriderestructureinterface)
- [\Consolidation\OutputFormatters\Transformations\SimplifyToArrayInterface (interface)](#interface-consolidationoutputformatterstransformationssimplifytoarrayinterface)
- [\Consolidation\OutputFormatters\Transformations\Wrap\CalculateWidths](#class-consolidationoutputformatterstransformationswrapcalculatewidths)
- [\Consolidation\OutputFormatters\Transformations\Wrap\ColumnWidths](#class-consolidationoutputformatterstransformationswrapcolumnwidths)
- [\Consolidation\OutputFormatters\Validate\ValidationInterface (interface)](#interface-consolidationoutputformattersvalidatevalidationinterface)
- [\Consolidation\OutputFormatters\Validate\ValidDataTypesInterface (interface)](#interface-consolidationoutputformattersvalidatevaliddatatypesinterface)

<hr />

### Class: \Consolidation\OutputFormatters\FormatterManager

> Manage a collection of formatters; return one on request.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct()</strong> : <em>void</em> |
| public | <strong>addDefaultFormatters()</strong> : <em>void</em> |
| public | <strong>addDefaultSimplifiers()</strong> : <em>void</em> |
| public | <strong>addFormatter(</strong><em>string</em> <strong>$key</strong>, <em>string/[\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)</em> <strong>$formatter</strong>)</strong> : <em>[\Consolidation\OutputFormatters\FormatterManager](#class-consolidationoutputformattersformattermanager)</em><br /><em>Add a formatter</em> |
| public | <strong>addSimplifier(</strong><em>[\Consolidation\OutputFormatters\Transformations\SimplifyToArrayInterface](#interface-consolidationoutputformatterstransformationssimplifytoarrayinterface)</em> <strong>$simplifier</strong>)</strong> : <em>[\Consolidation\OutputFormatters\FormatterManager](#class-consolidationoutputformattersformattermanager)</em><br /><em>Add a simplifier</em> |
| public | <strong>automaticOptions(</strong><em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>, <em>mixed</em> <strong>$dataType</strong>)</strong> : <em>\Symfony\Component\Console\Input\InputOption[]</em><br /><em>Return a set of InputOption based on the annotations of a command.</em> |
| public | <strong>getFormatter(</strong><em>string</em> <strong>$format</strong>)</strong> : <em>[\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)</em><br /><em>Fetch the requested formatter.</em> |
| public | <strong>hasFormatter(</strong><em>mixed</em> <strong>$format</strong>)</strong> : <em>bool</em><br /><em>Test to see if the stipulated format exists</em> |
| public | <strong>isValidDataType(</strong><em>[\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)</em> <strong>$formatter</strong>, <em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$dataType</strong>)</strong> : <em>bool</em> |
| public | <strong>isValidFormat(</strong><em>[\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)</em> <strong>$formatter</strong>, <em>mixed</em> <strong>$dataType</strong>)</strong> : <em>bool</em> |
| public | <strong>overrideOptions(</strong><em>[\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)</em> <strong>$formatter</strong>, <em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em><br /><em>Allow the formatter to mess with the configuration options before any transformations et. al. get underway.</em> |
| public | <strong>overrideRestructure(</strong><em>[\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)</em> <strong>$formatter</strong>, <em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Allow the formatter access to the raw structured data prior to restructuring.  For example, the 'list' formatter may wish to display the row keys when provided table output.  If this function returns a result that does not evaluate to 'false', then that result will be used as-is, and restructuring and validation will not occur.</em> |
| public | <strong>renderData(</strong><em>[\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)</em> <strong>$formatter</strong>, <em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Render the data as necessary (e.g. to select or reorder fields).</em> |
| public | <strong>restructureData(</strong><em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Restructure the data as necessary (e.g. to select or reorder fields).</em> |
| public | <strong>validFormats(</strong><em>mixed</em> <strong>$dataType</strong>)</strong> : <em>array</em><br /><em>Return the identifiers for all valid data types that have been registered.</em> |
| public | <strong>validateData(</strong><em>[\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)</em> <strong>$formatter</strong>, <em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Determine if the provided data is compatible with the formatter being used.</em> |
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>string</em> <strong>$format</strong>, <em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em><br /><em>Format and write output</em> |
| protected | <strong>availableFieldsList(</strong><em>mixed</em> <strong>$availableFields</strong>)</strong> : <em>string[]</em><br /><em>Given a list of available fields, return a list of field descriptions.</em> |
| protected | <strong>canSimplifyToArray(</strong><em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$structuredOutput</strong>)</strong> : <em>bool</em> |
| protected | <strong>simplifyToArray(</strong><em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em> |
| protected | <strong>validateAndRestructure(</strong><em>[\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)</em> <strong>$formatter</strong>, <em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em> |

<hr />

### Class: \Consolidation\OutputFormatters\Exception\UnknownFormatException

> Indicates that the requested format does not exist.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>mixed</em> <strong>$format</strong>)</strong> : <em>void</em> |

*This class extends \Exception*

*This class implements \Throwable*

<hr />

### Class: \Consolidation\OutputFormatters\Exception\AbstractDataFormatException (abstract)

> Contains some helper functions used by exceptions in this project.

| Visibility | Function |
|:-----------|:---------|
| protected static | <strong>describeAllowedTypes(</strong><em>mixed</em> <strong>$allowedTypes</strong>)</strong> : <em>void</em> |
| protected static | <strong>describeDataType(</strong><em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$data</strong>)</strong> : <em>string</em><br /><em>Return a description of the data type represented by the provided parameter. \ArrayObject is used as a proxy to mean an array primitive (or an ArrayObject).</em> |
| protected static | <strong>describeListOfAllowedTypes(</strong><em>mixed</em> <strong>$allowedTypes</strong>)</strong> : <em>void</em> |

*This class extends \Exception*

*This class implements \Throwable*

<hr />

### Class: \Consolidation\OutputFormatters\Exception\IncompatibleDataException

> Represents an incompatibility between the output data and selected formatter.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>[\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)</em> <strong>$formatter</strong>, <em>mixed</em> <strong>$data</strong>, <em>mixed</em> <strong>$allowedTypes</strong>)</strong> : <em>void</em> |

*This class extends [\Consolidation\OutputFormatters\Exception\AbstractDataFormatException](#class-consolidationoutputformattersexceptionabstractdataformatexception-abstract)*

*This class implements \Throwable*

<hr />

### Class: \Consolidation\OutputFormatters\Exception\InvalidFormatException

> Represents an incompatibility between the output data and selected formatter.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>mixed</em> <strong>$format</strong>, <em>mixed</em> <strong>$data</strong>, <em>mixed</em> <strong>$validFormats</strong>)</strong> : <em>void</em> |

*This class extends [\Consolidation\OutputFormatters\Exception\AbstractDataFormatException](#class-consolidationoutputformattersexceptionabstractdataformatexception-abstract)*

*This class implements \Throwable*

<hr />

### Class: \Consolidation\OutputFormatters\Exception\UnknownFieldException

> Indicates that the requested format does not exist.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>mixed</em> <strong>$field</strong>)</strong> : <em>void</em> |

*This class extends \Exception*

*This class implements \Throwable*

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\ListFormatter

> Display the data in a simple list. This formatter prints a plain, unadorned list of data, with each data item appearing on a separate line.  If you wish your list to contain headers, then use the table formatter, and wrap your data in an PropertyList.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>overrideRestructure(</strong><em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Select data to use directly from the structured output, before the restructure operation has been executed.</em> |
| public | <strong>renderData(</strong><em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Convert the contents of the output data just before it is to be printed, prior to output but after restructuring and validation.</em> |
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |
| protected | <strong>renderEachCell(</strong><em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface), [\Consolidation\OutputFormatters\Transformations\OverrideRestructureInterface](#interface-consolidationoutputformatterstransformationsoverriderestructureinterface), [\Consolidation\OutputFormatters\Formatters\RenderDataInterface](#interface-consolidationoutputformattersformattersrenderdatainterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\SectionsFormatter

> Display sections of data. This formatter takes data in the RowsOfFields data type. Each row represents one section; the data in each section is rendered in two columns, with the key in the first column and the value in the second column.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>isValidDataType(</strong><em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$dataType</strong>)</strong> : <em>bool</em><br /><em>Return the list of data types acceptable to this formatter</em> |
| public | <strong>renderData(</strong><em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Convert the contents of the output data just before it is to be printed, prior to output but after restructuring and validation.</em> |
| public | <strong>validDataTypes()</strong> : <em>void</em> |
| public | <strong>validate(</strong><em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>mixed</em><br /><em>Throw an IncompatibleDataException if the provided data cannot be processed by this formatter.  Return the source data if it is valid. The data may be encapsulated or converted if necessary.</em> |
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |
| protected | <strong>renderEachCell(</strong><em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface), [\Consolidation\OutputFormatters\Validate\ValidDataTypesInterface](#interface-consolidationoutputformattersvalidatevaliddatatypesinterface), [\Consolidation\OutputFormatters\Validate\ValidationInterface](#interface-consolidationoutputformattersvalidatevalidationinterface), [\Consolidation\OutputFormatters\Formatters\RenderDataInterface](#interface-consolidationoutputformattersformattersrenderdatainterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\JsonFormatter

> Json formatter Convert an array or ArrayObject into Json.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)*

<hr />

### Interface: \Consolidation\OutputFormatters\Formatters\FormatterInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\CsvFormatter

> Comma-separated value formatters Display the provided structured data in a comma-separated list. If there are multiple records provided, then they will be printed one per line.  The primary data types accepted are RowsOfFields and PropertyList. The later behaves exactly like the former, save for the fact that it contains but a single row. This formmatter can also accept a PHP array; this is also interpreted as a single-row of data with no header.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>isValidDataType(</strong><em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$dataType</strong>)</strong> : <em>bool</em><br /><em>Return the list of data types acceptable to this formatter</em> |
| public | <strong>renderData(</strong><em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Convert the contents of the output data just before it is to be printed, prior to output but after restructuring and validation.</em> |
| public | <strong>validDataTypes()</strong> : <em>void</em> |
| public | <strong>validate(</strong><em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>void</em> |
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |
| protected | <strong>csvEscape(</strong><em>mixed</em> <strong>$data</strong>, <em>string</em> <strong>$delimiter=`','`</strong>)</strong> : <em>void</em> |
| protected | <strong>getDefaultFormatterOptions()</strong> : <em>array</em><br /><em>Return default values for formatter options</em> |
| protected | <strong>renderEachCell(</strong><em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em> |
| protected | <strong>writeOneLine(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>mixed</em> <strong>$options</strong>)</strong> : <em>void</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface), [\Consolidation\OutputFormatters\Validate\ValidDataTypesInterface](#interface-consolidationoutputformattersvalidatevaliddatatypesinterface), [\Consolidation\OutputFormatters\Validate\ValidationInterface](#interface-consolidationoutputformattersvalidatevalidationinterface), [\Consolidation\OutputFormatters\Formatters\RenderDataInterface](#interface-consolidationoutputformattersformattersrenderdatainterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\SerializeFormatter

> Serialize formatter Run provided date thruogh serialize.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\StringFormatter

> String formatter This formatter is used as the default action when no particular formatter is requested.  It will print the provided data only if it is a string; if any other type is given, then nothing is printed.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>isValidDataType(</strong><em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$dataType</strong>)</strong> : <em>bool</em><br /><em>All data types are acceptable.</em> |
| public | <strong>overrideOptions(</strong><em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em><br /><em>Allow the formatter to mess with the configuration options before any transformations et. al. get underway.</em> |
| public | <strong>validate(</strong><em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>void</em><br /><em>Always validate any data, though. This format will never cause an error if it is selected for an incompatible data type; at worse, it simply does not print any data.</em> |
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |
| protected | <strong>reduceToSigleFieldAndWrite(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em><br /><em>If the data provided to a 'string' formatter is a table, then try to emit it as a TSV value.</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface), [\Consolidation\OutputFormatters\Validate\ValidationInterface](#interface-consolidationoutputformattersvalidatevalidationinterface), [\Consolidation\OutputFormatters\Options\OverrideOptionsInterface](#interface-consolidationoutputformattersoptionsoverrideoptionsinterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\VarExportFormatter

> Var_export formatter Run provided date thruogh var_export.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\YamlFormatter

> Yaml formatter Convert an array or ArrayObject into Yaml.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\TableFormatter

> Display a table of data with the Symfony Table class. This formatter takes data of either the RowsOfFields or PropertyList data type.  Tables can be rendered with the rows running either vertically (the normal orientation) or horizontally.  By default, associative lists will be displayed as two columns, with the key in the first column and the value in the second column.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct()</strong> : <em>void</em> |
| public | <strong>isValidDataType(</strong><em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$dataType</strong>)</strong> : <em>bool</em><br /><em>Return the list of data types acceptable to this formatter</em> |
| public | <strong>renderData(</strong><em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Convert the contents of the output data just before it is to be printed, prior to output but after restructuring and validation.</em> |
| public | <strong>validDataTypes()</strong> : <em>void</em> |
| public | <strong>validate(</strong><em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>mixed</em><br /><em>Throw an IncompatibleDataException if the provided data cannot be processed by this formatter.  Return the source data if it is valid. The data may be encapsulated or converted if necessary.</em> |
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |
| protected static | <strong>addCustomTableStyles(</strong><em>mixed</em> <strong>$table</strong>)</strong> : <em>void</em><br /><em>Add our custom table style(s) to the table.</em> |
| protected | <strong>renderEachCell(</strong><em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em> |
| protected | <strong>wrap(</strong><em>mixed</em> <strong>$headers</strong>, <em>array</em> <strong>$data</strong>, <em>\Symfony\Component\Console\Helper\TableStyle</em> <strong>$tableStyle</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>array</em><br /><em>Wrap the table data</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface), [\Consolidation\OutputFormatters\Validate\ValidDataTypesInterface](#interface-consolidationoutputformattersvalidatevaliddatatypesinterface), [\Consolidation\OutputFormatters\Validate\ValidationInterface](#interface-consolidationoutputformattersvalidatevalidationinterface), [\Consolidation\OutputFormatters\Formatters\RenderDataInterface](#interface-consolidationoutputformattersformattersrenderdatainterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\XmlFormatter

> Display a table of data with the Symfony Table class. This formatter takes data of either the RowsOfFields or PropertyList data type.  Tables can be rendered with the rows running either vertically (the normal orientation) or horizontally.  By default, associative lists will be displayed as two columns, with the key in the first column and the value in the second column.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct()</strong> : <em>void</em> |
| public | <strong>isValidDataType(</strong><em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$dataType</strong>)</strong> : <em>bool</em><br /><em>Return the list of data types acceptable to this formatter</em> |
| public | <strong>validDataTypes()</strong> : <em>void</em> |
| public | <strong>validate(</strong><em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>mixed</em><br /><em>Throw an IncompatibleDataException if the provided data cannot be processed by this formatter.  Return the source data if it is valid. The data may be encapsulated or converted if necessary.</em> |
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface), [\Consolidation\OutputFormatters\Validate\ValidDataTypesInterface](#interface-consolidationoutputformattersvalidatevaliddatatypesinterface), [\Consolidation\OutputFormatters\Validate\ValidationInterface](#interface-consolidationoutputformattersvalidatevalidationinterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\PrintRFormatter

> Print_r formatter Run provided date thruogh print_r.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |

*This class implements [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)*

<hr />

### Interface: \Consolidation\OutputFormatters\Formatters\RenderDataInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>renderData(</strong><em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Convert the contents of the output data just before it is to be printed, prior to output but after restructuring and validation.</em> |

<hr />

### Class: \Consolidation\OutputFormatters\Formatters\TsvFormatter

> Tab-separated value formatters Display the provided structured data in a tab-separated list.  Output escaping is much lighter, since there is no allowance for altering the delimiter.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>renderData(</strong><em>mixed</em> <strong>$originalData</strong>, <em>mixed</em> <strong>$restructuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Convert the contents of the output data just before it is to be printed, prior to output but after restructuring and validation.</em> |
| public | <strong>write(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>string</em><br /><em>Given structured data, apply appropriate formatting, and return a printable string.</em> |
| protected | <strong>getDefaultFormatterOptions()</strong> : <em>mixed</em> |
| protected | <strong>tsvEscape(</strong><em>mixed</em> <strong>$data</strong>)</strong> : <em>void</em> |
| protected | <strong>writeOneLine(</strong><em>\Symfony\Component\Console\Output\OutputInterface</em> <strong>$output</strong>, <em>mixed</em> <strong>$data</strong>, <em>mixed</em> <strong>$options</strong>)</strong> : <em>void</em> |

*This class extends [\Consolidation\OutputFormatters\Formatters\CsvFormatter](#class-consolidationoutputformattersformatterscsvformatter)*

*This class implements [\Consolidation\OutputFormatters\Formatters\RenderDataInterface](#interface-consolidationoutputformattersformattersrenderdatainterface), [\Consolidation\OutputFormatters\Validate\ValidationInterface](#interface-consolidationoutputformattersvalidatevalidationinterface), [\Consolidation\OutputFormatters\Validate\ValidDataTypesInterface](#interface-consolidationoutputformattersvalidatevaliddatatypesinterface), [\Consolidation\OutputFormatters\Formatters\FormatterInterface](#interface-consolidationoutputformattersformattersformatterinterface)*

<hr />

### Interface: \Consolidation\OutputFormatters\Options\OverrideOptionsInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>overrideOptions(</strong><em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em><br /><em>Allow the formatter to mess with the configuration options before any transformations et. al. get underway.</em> |

<hr />

### Class: \Consolidation\OutputFormatters\Options\FormatterOptions

> FormetterOptions holds information that affects the way a formatter renders its output. There are three places where a formatter might get options from: 1. Configuration associated with the command that produced the output. This is passed in to FormatterManager::write() along with the data to format.  It might originally come from annotations on the command, or it might come from another source.  Examples include the field labels for a table, or the default list of fields to display. 2. Options specified by the user, e.g. by commandline options. 3. Default values associated with the formatter itself. This class caches configuration from sources (1) and (2), and expects to be provided the defaults, (3), whenever a value is requested.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>array</em> <strong>$configurationData=array()</strong>, <em>array</em> <strong>$options=array()</strong>)</strong> : <em>void</em><br /><em>Create a new FormatterOptions with the configuration data and the user-specified options for this request.</em> |
| public | <strong>get(</strong><em>string</em> <strong>$key</strong>, <em>array</em> <strong>$defaults=array()</strong>, <em>bool/mixed</em> <strong>$default=false</strong>)</strong> : <em>mixed</em><br /><em>Get a formatter option</em> |
| public | <strong>getConfigurationData()</strong> : <em>array</em><br /><em>Return a reference to the configuration data for this object.</em> |
| public | <strong>getFormat(</strong><em>array</em> <strong>$defaults=array()</strong>)</strong> : <em>string</em><br /><em>Determine the format that was requested by the caller.</em> |
| public | <strong>getInputOptions(</strong><em>array</em> <strong>$defaults</strong>)</strong> : <em>array</em><br /><em>Return all of the options from the provided $defaults array that exist in our InputInterface object.</em> |
| public | <strong>getOptions()</strong> : <em>array</em><br /><em>Return a reference to the user-specified options for this request.</em> |
| public | <strong>getXmlSchema()</strong> : <em>[\Consolidation\OutputFormatters\StructuredData\Xml\XmlSchema](#class-consolidationoutputformattersstructureddataxmlxmlschema)</em><br /><em>Return the XmlSchema to use with --format=xml for data types that support that.  This is used when an array needs to be converted into xml.</em> |
| public | <strong>override(</strong><em>array</em> <strong>$configurationData</strong>)</strong> : <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em><br /><em>Create a new FormatterOptions object with new configuration data (provided), and the same options data as this instance.</em> |
| public | <strong>parsePropertyList(</strong><em>string</em> <strong>$value</strong>)</strong> : <em>array</em><br /><em>Convert from a textual list to an array</em> |
| public | <strong>setConfigurationData(</strong><em>array</em> <strong>$configurationData</strong>)</strong> : <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em><br /><em>Change the configuration data for this formatter options object.</em> |
| public | <strong>setConfigurationDefault(</strong><em>string</em> <strong>$key</strong>, <em>mixed</em> <strong>$value</strong>)</strong> : <em>\Consolidation\OutputFormatters\Options\FormetterOptions</em><br /><em>Change one configuration value for this formatter option, but only if it does not already have a value set.</em> |
| public | <strong>setDefaultFields(</strong><em>mixed</em> <strong>$fields</strong>)</strong> : <em>void</em> |
| public | <strong>setDefaultStringField(</strong><em>mixed</em> <strong>$defaultStringField</strong>)</strong> : <em>void</em> |
| public | <strong>setDelimiter(</strong><em>mixed</em> <strong>$delimiter</strong>)</strong> : <em>void</em> |
| public | <strong>setFieldLabels(</strong><em>mixed</em> <strong>$fieldLabels</strong>)</strong> : <em>void</em> |
| public | <strong>setIncludeFieldLables(</strong><em>mixed</em> <strong>$includFieldLables</strong>)</strong> : <em>void</em> |
| public | <strong>setInput(</strong><em>\Symfony\Component\Console\Input\InputInterface</em> <strong>$input</strong>)</strong> : <em>\Consolidation\OutputFormatters\Options\type</em><br /><em>Provide a Symfony Console InputInterface containing the user-specified options for this request.</em> |
| public | <strong>setListDelimiter(</strong><em>mixed</em> <strong>$listDelimiter</strong>)</strong> : <em>void</em> |
| public | <strong>setListOrientation(</strong><em>mixed</em> <strong>$listOrientation</strong>)</strong> : <em>void</em> |
| public | <strong>setOption(</strong><em>string</em> <strong>$key</strong>, <em>mixed</em> <strong>$value</strong>)</strong> : <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em><br /><em>Change one option value specified by the user for this request.</em> |
| public | <strong>setOptions(</strong><em>array</em> <strong>$options</strong>)</strong> : <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em><br /><em>Set all of the options that were specified by the user for this request.</em> |
| public | <strong>setRowLabels(</strong><em>mixed</em> <strong>$rowLabels</strong>)</strong> : <em>void</em> |
| public | <strong>setTableStyle(</strong><em>mixed</em> <strong>$style</strong>)</strong> : <em>void</em> |
| public | <strong>setWidth(</strong><em>mixed</em> <strong>$width</strong>)</strong> : <em>void</em> |
| protected | <strong>defaultsForKey(</strong><em>string</em> <strong>$key</strong>, <em>array</em> <strong>$defaults</strong>, <em>bool</em> <strong>$default=false</strong>)</strong> : <em>array</em><br /><em>Reduce provided defaults to the single item identified by '$key', if it exists, or an empty array otherwise.</em> |
| protected | <strong>fetch(</strong><em>string</em> <strong>$key</strong>, <em>array</em> <strong>$defaults=array()</strong>, <em>bool/mixed</em> <strong>$default=false</strong>)</strong> : <em>mixed</em><br /><em>Look up a key, and return its raw value.</em> |
| protected | <strong>fetchRawValues(</strong><em>array</em> <strong>$defaults=array()</strong>)</strong> : <em>array</em><br /><em>Look up all of the items associated with the provided defaults.</em> |
| protected | <strong>getOptionFormat(</strong><em>string</em> <strong>$key</strong>)</strong> : <em>string</em><br /><em>Given a specific key, return the class method name of the parsing method for data stored under this key.</em> |
| protected | <strong>parse(</strong><em>string</em> <strong>$key</strong>, <em>mixed</em> <strong>$value</strong>)</strong> : <em>mixed</em><br /><em>Given the raw value for a specific key, do any type conversion (e.g. from a textual list to an array) needed for the data.</em> |
| protected | <strong>setConfigurationValue(</strong><em>string</em> <strong>$key</strong>, <em>mixed</em> <strong>$value</strong>)</strong> : <em>\Consolidation\OutputFormatters\Options\FormetterOptions</em><br /><em>Change one configuration value for this formatter option.</em> |

<hr />

### Interface: \Consolidation\OutputFormatters\StructuredData\ListDataInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getListData(</strong><em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>array</em><br /><em>Convert data to a format suitable for use in a list. By default, the array values will be used.  Implement ListDataInterface to use some other criteria (e.g. array keys).</em> |

<hr />

### Interface: \Consolidation\OutputFormatters\StructuredData\TableDataInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getOriginalData()</strong> : <em>mixed</em><br /><em>Return the original data for this table.  Used by any formatter that is -not- a table.</em> |
| public | <strong>getTableData(</strong><em>bool/boolean</em> <strong>$includeRowKey=false</strong>)</strong> : <em>array</em><br /><em>Convert structured data into a form suitable for use by the table formatter. key from each row.</em> |

<hr />

### Class: \Consolidation\OutputFormatters\StructuredData\HelpDocument

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getDomData()</strong> : <em>[\DomDocument](http://php.net/manual/en/class.domdocument.php)</em><br /><em>Convert data into a \DomDocument.</em> |

*This class implements [\Consolidation\OutputFormatters\StructuredData\Xml\DomDataInterface](#interface-consolidationoutputformattersstructureddataxmldomdatainterface)*

<hr />

### Interface: \Consolidation\OutputFormatters\StructuredData\OriginalDataInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getOriginalData()</strong> : <em>mixed</em><br /><em>Return the original data for this table.  Used by any formatter that expects an array.</em> |

<hr />

### Class: \Consolidation\OutputFormatters\StructuredData\RowsOfFields

> Holds an array where each element of the array is one row, and each row contains an associative array where the keys are the field names, and the values are the field data. It is presumed that every row contains the same keys.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getListData(</strong><em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em> |
| public | <strong>restructure(</strong><em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>\Consolidation\OutputFormatters\StructuredData\Consolidation\OutputFormatters\Transformations\TableTransformation</em><br /><em>Restructure this data for output by converting it into a table transformation object.</em> |
| protected | <strong>defaultOptions()</strong> : <em>void</em> |

*This class extends [\Consolidation\OutputFormatters\StructuredData\AbstractStructuredList](#class-consolidationoutputformattersstructureddataabstractstructuredlist-abstract)*

*This class implements [\Consolidation\OutputFormatters\StructuredData\RenderCellInterface](#interface-consolidationoutputformattersstructureddatarendercellinterface), [\Consolidation\OutputFormatters\StructuredData\RenderCellCollectionInterface](#interface-consolidationoutputformattersstructureddatarendercellcollectioninterface), [\Consolidation\OutputFormatters\StructuredData\RestructureInterface](#interface-consolidationoutputformattersstructureddatarestructureinterface), \Countable, \Serializable, \ArrayAccess, \Traversable, \IteratorAggregate, [\Consolidation\OutputFormatters\StructuredData\ListDataInterface](#interface-consolidationoutputformattersstructureddatalistdatainterface)*

<hr />

### Interface: \Consolidation\OutputFormatters\StructuredData\RestructureInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>restructure(</strong><em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em><br /><em>Allow structured data to be restructured -- i.e. to select fields to show, reorder fields, etc.</em> |

<hr />

### Class: \Consolidation\OutputFormatters\StructuredData\AbstractStructuredList (abstract)

> Holds an array where each element of the array is one row, and each row contains an associative array where the keys are the field names, and the values are the field data. It is presumed that every row contains the same keys.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>mixed</em> <strong>$data</strong>)</strong> : <em>void</em> |
| public | <strong>addRenderer(</strong><em>[\Consolidation\OutputFormatters\StructuredData\RenderCellInterface](#interface-consolidationoutputformattersstructureddatarendercellinterface)</em> <strong>$renderer</strong>, <em>string</em> <strong>$priority=`'normal'`</strong>)</strong> : <em>\Consolidation\OutputFormatters\StructuredData\$this</em><br /><em>Add a renderer</em> |
| public | <strong>addRendererFunction(</strong><em>\callable</em> <strong>$rendererFn</strong>, <em>string</em> <strong>$priority=`'normal'`</strong>)</strong> : <em>\Consolidation\OutputFormatters\StructuredData\$this</em><br /><em>Add a callable as a renderer</em> |
| public | <strong>renderCell(</strong><em>mixed</em> <strong>$key</strong>, <em>mixed</em> <strong>$cellData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>, <em>mixed</em> <strong>$rowData</strong>)</strong> : <em>void</em> |
| public | <strong>abstract restructure(</strong><em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em> |
| protected | <strong>createTableTransformation(</strong><em>mixed</em> <strong>$data</strong>, <em>mixed</em> <strong>$options</strong>)</strong> : <em>mixed</em> |
| protected | <strong>defaultOptions()</strong> : <em>array</em><br /><em>A structured list may provide its own set of default options. These will be used in place of the command's default options (from the annotations) in instances where the user does not provide the options explicitly (on the commandline) or implicitly (via a configuration file).</em> |
| protected | <strong>getFields(</strong><em>mixed</em> <strong>$options</strong>, <em>mixed</em> <strong>$defaults</strong>)</strong> : <em>mixed</em> |
| protected | <strong>getReorderedFieldLabels(</strong><em>mixed</em> <strong>$data</strong>, <em>mixed</em> <strong>$options</strong>, <em>mixed</em> <strong>$defaults</strong>)</strong> : <em>mixed</em> |
| protected | <strong>instantiateTableTransformation(</strong><em>mixed</em> <strong>$data</strong>, <em>mixed</em> <strong>$fieldLabels</strong>, <em>mixed</em> <strong>$rowLabels</strong>)</strong> : <em>void</em> |

*This class extends [\Consolidation\OutputFormatters\StructuredData\ListDataFromKeys](#class-consolidationoutputformattersstructureddatalistdatafromkeys)*

*This class implements [\Consolidation\OutputFormatters\StructuredData\ListDataInterface](#interface-consolidationoutputformattersstructureddatalistdatainterface), \IteratorAggregate, \Traversable, \ArrayAccess, \Serializable, \Countable, [\Consolidation\OutputFormatters\StructuredData\RestructureInterface](#interface-consolidationoutputformattersstructureddatarestructureinterface), [\Consolidation\OutputFormatters\StructuredData\RenderCellCollectionInterface](#interface-consolidationoutputformattersstructureddatarendercellcollectioninterface), [\Consolidation\OutputFormatters\StructuredData\RenderCellInterface](#interface-consolidationoutputformattersstructureddatarendercellinterface)*

<hr />

### Class: \Consolidation\OutputFormatters\StructuredData\ListDataFromKeys

> Represents aribtrary array data (structured or unstructured) where the data to display in --list format comes from the array keys.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>mixed</em> <strong>$data</strong>)</strong> : <em>void</em> |
| public | <strong>getListData(</strong><em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em> |

*This class extends \ArrayObject*

*This class implements \Countable, \Serializable, \ArrayAccess, \Traversable, \IteratorAggregate, [\Consolidation\OutputFormatters\StructuredData\ListDataInterface](#interface-consolidationoutputformattersstructureddatalistdatainterface)*

<hr />

### Class: \Consolidation\OutputFormatters\StructuredData\PropertyList

> Holds an array where each element of the array is one key : value pair.  The keys must be unique, as is typically the case for associative arrays.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getListData(</strong><em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em> |
| public | <strong>restructure(</strong><em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>\Consolidation\OutputFormatters\StructuredData\Consolidation\OutputFormatters\Transformations\TableTransformation</em><br /><em>Restructure this data for output by converting it into a table transformation object.</em> |
| protected | <strong>defaultOptions()</strong> : <em>void</em> |
| protected | <strong>instantiateTableTransformation(</strong><em>mixed</em> <strong>$data</strong>, <em>mixed</em> <strong>$fieldLabels</strong>, <em>mixed</em> <strong>$rowLabels</strong>)</strong> : <em>void</em> |

*This class extends [\Consolidation\OutputFormatters\StructuredData\AbstractStructuredList](#class-consolidationoutputformattersstructureddataabstractstructuredlist-abstract)*

*This class implements [\Consolidation\OutputFormatters\StructuredData\RenderCellInterface](#interface-consolidationoutputformattersstructureddatarendercellinterface), [\Consolidation\OutputFormatters\StructuredData\RenderCellCollectionInterface](#interface-consolidationoutputformattersstructureddatarendercellcollectioninterface), [\Consolidation\OutputFormatters\StructuredData\RestructureInterface](#interface-consolidationoutputformattersstructureddatarestructureinterface), \Countable, \Serializable, \ArrayAccess, \Traversable, \IteratorAggregate, [\Consolidation\OutputFormatters\StructuredData\ListDataInterface](#interface-consolidationoutputformattersstructureddatalistdatainterface)*

<hr />

### Interface: \Consolidation\OutputFormatters\StructuredData\RenderCellInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>renderCell(</strong><em>string</em> <strong>$key</strong>, <em>mixed</em> <strong>$cellData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>, <em>array</em> <strong>$rowData</strong>)</strong> : <em>mixed</em><br /><em>Convert the contents of one table cell into a string, so that it may be placed in the table.  Renderer should return the $cellData passed to it if it does not wish to process it.</em> |

<hr />

### Class: \Consolidation\OutputFormatters\StructuredData\CallableRenderer

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>\callable</em> <strong>$renderFunction</strong>)</strong> : <em>void</em> |
| public | <strong>renderCell(</strong><em>mixed</em> <strong>$key</strong>, <em>mixed</em> <strong>$cellData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>, <em>mixed</em> <strong>$rowData</strong>)</strong> : <em>void</em> |

*This class implements [\Consolidation\OutputFormatters\StructuredData\RenderCellInterface](#interface-consolidationoutputformattersstructureddatarendercellinterface)*

<hr />

### Interface: \Consolidation\OutputFormatters\StructuredData\RenderCellCollectionInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>addRenderer(</strong><em>[\Consolidation\OutputFormatters\StructuredData\RenderCellInterface](#interface-consolidationoutputformattersstructureddatarendercellinterface)</em> <strong>$renderer</strong>)</strong> : <em>\Consolidation\OutputFormatters\StructuredData\$this</em><br /><em>Add a renderer</em> |

*This class implements [\Consolidation\OutputFormatters\StructuredData\RenderCellInterface](#interface-consolidationoutputformattersstructureddatarendercellinterface)*

<hr />

### Class: \Consolidation\OutputFormatters\StructuredData\AssociativeList

> Old name for PropertyList class.

| Visibility | Function |
|:-----------|:---------|

*This class extends [\Consolidation\OutputFormatters\StructuredData\PropertyList](#class-consolidationoutputformattersstructureddatapropertylist)*

*This class implements [\Consolidation\OutputFormatters\StructuredData\ListDataInterface](#interface-consolidationoutputformattersstructureddatalistdatainterface), \IteratorAggregate, \Traversable, \ArrayAccess, \Serializable, \Countable, [\Consolidation\OutputFormatters\StructuredData\RestructureInterface](#interface-consolidationoutputformattersstructureddatarestructureinterface), [\Consolidation\OutputFormatters\StructuredData\RenderCellCollectionInterface](#interface-consolidationoutputformattersstructureddatarendercellcollectioninterface), [\Consolidation\OutputFormatters\StructuredData\RenderCellInterface](#interface-consolidationoutputformattersstructureddatarendercellinterface)*

<hr />

### Interface: \Consolidation\OutputFormatters\StructuredData\Xml\XmlSchemaInterface

> When using arrays, we could represent XML data in a number of different ways. For example, given the following XML data strucutre: <document id="1" name="doc"> <foobars> <foobar id="123"> <name>blah</name> <widgets> <widget> <foo>a</foo> <bar>b</bar> <baz>c</baz> </widget> </widgets> </foobar> </foobars> </document> This could be: [ 'id' => 1, 'name'  => 'doc', 'foobars' => [ [ 'id' => '123', 'name' => 'blah', 'widgets' => [ [ 'foo' => 'a', 'bar' => 'b', 'baz' => 'c', ] ], ], ] ] The challenge is more in going from an array back to the more structured xml format.  Note that any given key => string mapping could represent either an attribute, or a simple XML element containing only a string value. In general, we do *not* want to add extra layers of nesting in the data structure to disambiguate between these kinds of data, as we want the source data to render cleanly into other formats, e.g. yaml, json, et. al., and we do not want to force every data provider to have to consider the optimal xml schema for their data. Our strategy, therefore, is to expect clients that wish to provide a very specific xml representation to return a DOMDocument, and, for other data structures where xml is a secondary concern, then we will use some default heuristics to convert from arrays to xml.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>arrayToXml(</strong><em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>[\DOMDocument](http://php.net/manual/en/class.domdocument.php)</em><br /><em>Convert data to a format suitable for use in a list. By default, the array values will be used.  Implement ListDataInterface to use some other criteria (e.g. array keys).</em> |

<hr />

### Interface: \Consolidation\OutputFormatters\StructuredData\Xml\DomDataInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getDomData()</strong> : <em>[\DomDocument](http://php.net/manual/en/class.domdocument.php)</em><br /><em>Convert data into a \DomDocument.</em> |

<hr />

### Class: \Consolidation\OutputFormatters\StructuredData\Xml\XmlSchema

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>array</em> <strong>$elementList=array()</strong>)</strong> : <em>void</em> |
| public | <strong>arrayToXML(</strong><em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>void</em> |
| protected | <strong>addXmlChildren(</strong><em>[\DOMDocument](http://php.net/manual/en/class.domdocument.php)</em> <strong>$dom</strong>, <em>mixed</em> <strong>$xmlParent</strong>, <em>mixed</em> <strong>$elementName</strong>, <em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>void</em> |
| protected | <strong>addXmlData(</strong><em>[\DOMDocument](http://php.net/manual/en/class.domdocument.php)</em> <strong>$dom</strong>, <em>mixed</em> <strong>$xmlParent</strong>, <em>mixed</em> <strong>$elementName</strong>, <em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>void</em> |
| protected | <strong>addXmlDataOrAttribute(</strong><em>[\DOMDocument](http://php.net/manual/en/class.domdocument.php)</em> <strong>$dom</strong>, <em>mixed</em> <strong>$xmlParent</strong>, <em>mixed</em> <strong>$elementName</strong>, <em>mixed</em> <strong>$key</strong>, <em>mixed</em> <strong>$value</strong>)</strong> : <em>void</em> |
| protected | <strong>determineElementName(</strong><em>mixed</em> <strong>$key</strong>, <em>mixed</em> <strong>$childElementName</strong>, <em>mixed</em> <strong>$value</strong>)</strong> : <em>void</em> |
| protected | <strong>getDefaultElementName(</strong><em>mixed</em> <strong>$parentElementName</strong>)</strong> : <em>mixed</em> |
| protected | <strong>getTopLevelElementName(</strong><em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>mixed</em> |
| protected | <strong>inElementList(</strong><em>mixed</em> <strong>$parentElementName</strong>, <em>mixed</em> <strong>$elementName</strong>)</strong> : <em>void</em> |
| protected | <strong>isAssoc(</strong><em>mixed</em> <strong>$data</strong>)</strong> : <em>bool</em> |
| protected | <strong>isAttribute(</strong><em>mixed</em> <strong>$parentElementName</strong>, <em>mixed</em> <strong>$elementName</strong>, <em>mixed</em> <strong>$value</strong>)</strong> : <em>bool</em> |
| protected | <strong>singularForm(</strong><em>mixed</em> <strong>$name</strong>)</strong> : <em>void</em> |

*This class implements [\Consolidation\OutputFormatters\StructuredData\Xml\XmlSchemaInterface](#interface-consolidationoutputformattersstructureddataxmlxmlschemainterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Transformations\PropertyParser

> Transform a string of properties into a PHP associative array. Input: one: red two: white three: blue Output: [ 'one' => 'red', 'two' => 'white', 'three' => 'blue', ]

| Visibility | Function |
|:-----------|:---------|
| public static | <strong>parse(</strong><em>mixed</em> <strong>$data</strong>)</strong> : <em>void</em> |

<hr />

### Class: \Consolidation\OutputFormatters\Transformations\PropertyListTableTransformation

| Visibility | Function |
|:-----------|:---------|
| public | <strong>getOriginalData()</strong> : <em>mixed</em> |

*This class extends [\Consolidation\OutputFormatters\Transformations\TableTransformation](#class-consolidationoutputformatterstransformationstabletransformation)*

*This class implements [\Consolidation\OutputFormatters\StructuredData\OriginalDataInterface](#interface-consolidationoutputformattersstructureddataoriginaldatainterface), [\Consolidation\OutputFormatters\StructuredData\TableDataInterface](#interface-consolidationoutputformattersstructureddatatabledatainterface), \IteratorAggregate, \Traversable, \ArrayAccess, \Serializable, \Countable*

<hr />

### Class: \Consolidation\OutputFormatters\Transformations\TableTransformation

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>mixed</em> <strong>$data</strong>, <em>mixed</em> <strong>$fieldLabels</strong>, <em>array</em> <strong>$rowLabels=array()</strong>)</strong> : <em>void</em> |
| public | <strong>getHeader(</strong><em>mixed</em> <strong>$key</strong>)</strong> : <em>mixed</em> |
| public | <strong>getHeaders()</strong> : <em>mixed</em> |
| public | <strong>getLayout()</strong> : <em>mixed</em> |
| public | <strong>getOriginalData()</strong> : <em>mixed</em> |
| public | <strong>getRowLabel(</strong><em>mixed</em> <strong>$rowid</strong>)</strong> : <em>mixed</em> |
| public | <strong>getRowLabels()</strong> : <em>mixed</em> |
| public | <strong>getTableData(</strong><em>bool</em> <strong>$includeRowKey=false</strong>)</strong> : <em>mixed</em> |
| public | <strong>isList()</strong> : <em>bool</em> |
| public | <strong>setLayout(</strong><em>mixed</em> <strong>$layout</strong>)</strong> : <em>void</em> |
| protected | <strong>convertTableToList()</strong> : <em>void</em> |
| protected | <strong>getRowDataWithKey(</strong><em>mixed</em> <strong>$data</strong>)</strong> : <em>mixed</em> |
| protected static | <strong>transformRow(</strong><em>mixed</em> <strong>$row</strong>, <em>mixed</em> <strong>$fieldLabels</strong>)</strong> : <em>void</em> |
| protected static | <strong>transformRows(</strong><em>mixed</em> <strong>$data</strong>, <em>mixed</em> <strong>$fieldLabels</strong>)</strong> : <em>void</em> |

*This class extends \ArrayObject*

*This class implements \Countable, \Serializable, \ArrayAccess, \Traversable, \IteratorAggregate, [\Consolidation\OutputFormatters\StructuredData\TableDataInterface](#interface-consolidationoutputformattersstructureddatatabledatainterface), [\Consolidation\OutputFormatters\StructuredData\OriginalDataInterface](#interface-consolidationoutputformattersstructureddataoriginaldatainterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Transformations\ReorderFields

> Reorder the field labels based on the user-selected fields to display.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>reorder(</strong><em>string/array</em> <strong>$fields</strong>, <em>array</em> <strong>$fieldLabels</strong>, <em>array</em> <strong>$data</strong>)</strong> : <em>array</em><br /><em>Given a simple list of user-supplied field keys or field labels, return a reordered version of the field labels matching the user selection. key to the field label</em> |
| protected | <strong>convertToRegex(</strong><em>mixed</em> <strong>$str</strong>)</strong> : <em>void</em><br /><em>Convert the provided string into a regex suitable for use in preg_match. Matching occurs in the same way as the Symfony Finder component: http://symfony.com/doc/current/components/finder.html#file-name</em> |
| protected | <strong>getSelectedFieldKeys(</strong><em>mixed</em> <strong>$fields</strong>, <em>mixed</em> <strong>$fieldLabels</strong>)</strong> : <em>mixed</em> |
| protected | <strong>isRegex(</strong><em>string</em> <strong>$str</strong>)</strong> : <em>bool Whether the given string is a regex</em><br /><em>Checks whether the string is a regex.  This function is copied from MultiplePcreFilterIterator in the Symfony Finder component.</em> |
| protected | <strong>matchFieldInLabelMap(</strong><em>mixed</em> <strong>$field</strong>, <em>mixed</em> <strong>$fieldLabels</strong>)</strong> : <em>void</em> |
| protected | <strong>reorderFieldLabels(</strong><em>mixed</em> <strong>$fields</strong>, <em>mixed</em> <strong>$fieldLabels</strong>, <em>mixed</em> <strong>$data</strong>)</strong> : <em>void</em> |

<hr />

### Class: \Consolidation\OutputFormatters\Transformations\DomToArraySimplifier

> Simplify a DOMDocument to an array.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct()</strong> : <em>void</em> |
| public | <strong>canSimplify(</strong><em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$dataType</strong>)</strong> : <em>bool</em> |
| public | <strong>simplifyToArray(</strong><em>mixed</em> <strong>$structuredData</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>void</em> |
| protected | <strong>elementToArray(</strong><em>[\DOMNode](http://php.net/manual/en/class.domnode.php)</em> <strong>$element</strong>)</strong> : <em>array</em><br /><em>Recursively convert the provided DOM element into a php array.</em> |
| protected | <strong>getIdOfValue(</strong><em>mixed</em> <strong>$value</strong>)</strong> : <em>string</em><br /><em>If the object has an 'id' or 'name' element, then use that as the array key when storing this value in its parent.</em> |
| protected | <strong>getNodeAttributes(</strong><em>[\DOMNode](http://php.net/manual/en/class.domnode.php)</em> <strong>$element</strong>)</strong> : <em>array</em><br /><em>Get all of the attributes of the provided element.</em> |
| protected | <strong>getNodeChildren(</strong><em>[\DOMNode](http://php.net/manual/en/class.domnode.php)</em> <strong>$element</strong>)</strong> : <em>array</em><br /><em>Get all of the children of the provided element, with simplification.</em> |
| protected | <strong>getNodeChildrenData(</strong><em>[\DOMNode](http://php.net/manual/en/class.domnode.php)</em> <strong>$element</strong>)</strong> : <em>array</em><br /><em>Get the data from the children of the provided node in preliminary form.</em> |
| protected | <strong>getUniformChildren(</strong><em>string</em> <strong>$parentKey</strong>, <em>[\DOMNode](http://php.net/manual/en/class.domnode.php)</em> <strong>$element</strong>)</strong> : <em>array</em><br /><em>Convert the children of the provided DOM element into an array. Here, 'uniform' means that all of the element names of the children are identical, and further, the element name of the parent is the plural form of the child names.  When the children are uniform in this way, then the parent element name will be used as the key to store the children in, and the child list will be returned as a simple list with their (duplicate) element names omitted.</em> |
| protected | <strong>getUniqueChildren(</strong><em>string</em> <strong>$parentKey</strong>, <em>[\DOMNode](http://php.net/manual/en/class.domnode.php)</em> <strong>$element</strong>)</strong> : <em>array</em><br /><em>Convert the children of the provided DOM element into an array. Here, 'unique' means that all of the element names of the children are different.  Since the element names will become the key of the associative array that is returned, so duplicates are not supported. If there are any duplicates, then an exception will be thrown.</em> |
| protected | <strong>hasUniformChildren(</strong><em>[\DOMNode](http://php.net/manual/en/class.domnode.php)</em> <strong>$element</strong>)</strong> : <em>boolean</em><br /><em>Determine whether the children of the provided element are uniform.</em> |
| protected | <strong>valueCanBeSimplified(</strong><em>[\DOMNode](http://php.net/manual/en/class.domnode.php)</em> <strong>$value</strong>)</strong> : <em>boolean</em><br /><em>Determine whether the provided value has additional unnecessary nesting.  {"color": "red"} is converted to "red". No other simplification is done.</em> |

*This class implements [\Consolidation\OutputFormatters\Transformations\SimplifyToArrayInterface](#interface-consolidationoutputformatterstransformationssimplifytoarrayinterface)*

<hr />

### Class: \Consolidation\OutputFormatters\Transformations\WordWrapper

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>mixed</em> <strong>$width</strong>)</strong> : <em>void</em> |
| public | <strong>minimumWidth(</strong><em>mixed</em> <strong>$colkey</strong>, <em>mixed</em> <strong>$width</strong>)</strong> : <em>void</em><br /><em>Set the minimum width of just one column</em> |
| public | <strong>setMinimumWidths(</strong><em>array</em> <strong>$minimumWidths</strong>)</strong> : <em>void</em><br /><em>If columns have minimum widths, then set them here.</em> |
| public | <strong>setPaddingFromStyle(</strong><em>\Symfony\Component\Console\Helper\TableStyle</em> <strong>$style</strong>)</strong> : <em>void</em><br /><em>Calculate our padding widths from the specified table style.</em> |
| public | <strong>wrap(</strong><em>array</em> <strong>$rows</strong>, <em>array</em> <strong>$widths=array()</strong>)</strong> : <em>array</em><br /><em>Wrap the cells in each part of the provided data table</em> |
| protected | <strong>calculateWidths(</strong><em>mixed</em> <strong>$rows</strong>, <em>array</em> <strong>$widths=array()</strong>)</strong> : <em>void</em><br /><em>Determine what widths we'll use for wrapping.</em> |
| protected | <strong>wrapCell(</strong><em>mixed</em> <strong>$cell</strong>, <em>string</em> <strong>$cellWidth</strong>)</strong> : <em>mixed</em><br /><em>Wrap one cell.  Guard against modifying non-strings and then call through to wordwrap().</em> |

<hr />

### Interface: \Consolidation\OutputFormatters\Transformations\OverrideRestructureInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>overrideRestructure(</strong><em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>mixed</em><br /><em>Select data to use directly from the structured output, before the restructure operation has been executed.</em> |

<hr />

### Interface: \Consolidation\OutputFormatters\Transformations\SimplifyToArrayInterface

| Visibility | Function |
|:-----------|:---------|
| public | <strong>canSimplify(</strong><em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$structuredOutput</strong>)</strong> : <em>bool</em><br /><em>Indicate whether or not the given data type can be simplified to an array</em> |
| public | <strong>simplifyToArray(</strong><em>mixed</em> <strong>$structuredOutput</strong>, <em>[\Consolidation\OutputFormatters\Options\FormatterOptions](#class-consolidationoutputformattersoptionsformatteroptions)</em> <strong>$options</strong>)</strong> : <em>array</em><br /><em>Convert structured data into a generic array, usable by generic array-based formatters.  Objects that implement this interface may be attached to the FormatterManager, and will be used on any data structure that needs to be simplified into an array.  An array simplifier should take no action other than to return its input data if it cannot simplify the provided data into an array.</em> |

<hr />

### Class: \Consolidation\OutputFormatters\Transformations\Wrap\CalculateWidths

> Calculate column widths for table cells. Influenced by Drush and webmozart/console.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct()</strong> : <em>void</em> |
| public | <strong>calculate(</strong><em>mixed</em> <strong>$availableWidth</strong>, <em>[\Consolidation\OutputFormatters\Transformations\Wrap\ColumnWidths](#class-consolidationoutputformatterstransformationswrapcolumnwidths)</em> <strong>$dataWidths</strong>, <em>[\Consolidation\OutputFormatters\Transformations\Wrap\ColumnWidths](#class-consolidationoutputformatterstransformationswrapcolumnwidths)</em> <strong>$minimumWidths</strong>)</strong> : <em>void</em><br /><em>Given the total amount of available space, and the width of the columns to place, calculate the optimum column widths to use.</em> |
| public | <strong>calculateLongestCell(</strong><em>mixed</em> <strong>$rows</strong>)</strong> : <em>void</em><br /><em>Calculate the longest cell data from any row of each of the cells.</em> |
| public | <strong>calculateLongestWord(</strong><em>mixed</em> <strong>$rows</strong>)</strong> : <em>void</em><br /><em>Calculate the longest word and longest line in the provided data.</em> |
| public | <strong>distributeLongColumns(</strong><em>mixed</em> <strong>$availableWidth</strong>, <em>[\Consolidation\OutputFormatters\Transformations\Wrap\ColumnWidths](#class-consolidationoutputformatterstransformationswrapcolumnwidths)</em> <strong>$dataWidths</strong>, <em>[\Consolidation\OutputFormatters\Transformations\Wrap\ColumnWidths](#class-consolidationoutputformatterstransformationswrapcolumnwidths)</em> <strong>$minimumWidths</strong>)</strong> : <em>void</em><br /><em>Distribute the remainig space among the columns that were not included in the list of "short" columns.</em> |
| public | <strong>getShortColumns(</strong><em>mixed</em> <strong>$availableWidth</strong>, <em>[\Consolidation\OutputFormatters\Transformations\Wrap\ColumnWidths](#class-consolidationoutputformatterstransformationswrapcolumnwidths)</em> <strong>$dataWidths</strong>, <em>[\Consolidation\OutputFormatters\Transformations\Wrap\ColumnWidths](#class-consolidationoutputformatterstransformationswrapcolumnwidths)</em> <strong>$minimumWidths</strong>)</strong> : <em>mixed</em><br /><em>Return all of the columns whose longest line length is less than or equal to the average width.</em> |
| protected | <strong>calculateColumnWidths(</strong><em>mixed</em> <strong>$rows</strong>, <em>\callable</em> <strong>$fn</strong>)</strong> : <em>void</em> |
| protected static | <strong>longestWordLength(</strong><em>string</em> <strong>$str</strong>)</strong> : <em>int</em><br /><em>Return the length of the longest word in the string.</em> |

<hr />

### Class: \Consolidation\OutputFormatters\Transformations\Wrap\ColumnWidths

> Calculate the width of data in table cells in preparation for word wrapping.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>__construct(</strong><em>array</em> <strong>$widths=array()</strong>)</strong> : <em>void</em> |
| public | <strong>adjustMinimumWidths(</strong><em>mixed</em> <strong>$availableWidth</strong>, <em>mixed</em> <strong>$dataCellWidths</strong>)</strong> : <em>void</em><br /><em>If the widths specified by this object do not fit within the provided avaiable width, then reduce them all proportionally.</em> |
| public | <strong>averageWidth(</strong><em>mixed</em> <strong>$availableWidth</strong>)</strong> : <em>void</em><br /><em>Calculate how much space is available on average for all columns.</em> |
| public | <strong>combine(</strong><em>[\Consolidation\OutputFormatters\Transformations\Wrap\ColumnWidths](#class-consolidationoutputformatterstransformationswrapcolumnwidths)</em> <strong>$combineWith</strong>)</strong> : <em>void</em><br /><em>Combine this set of widths with another set, and return a new set that contains the entries from both.</em> |
| public | <strong>count()</strong> : <em>void</em><br /><em>Return the number of columns.</em> |
| public | <strong>distribute(</strong><em>mixed</em> <strong>$availableWidth</strong>)</strong> : <em>void</em><br /><em>Return proportional weights</em> |
| public | <strong>enforceMinimums(</strong><em>mixed</em> <strong>$minimumWidths</strong>)</strong> : <em>void</em><br /><em>Ensure that every item in $widths that has a corresponding entry in $minimumWidths is as least as large as the minimum value held there.</em> |
| public | <strong>findShortColumns(</strong><em>mixed</em> <strong>$thresholdWidth</strong>)</strong> : <em>mixed</em><br /><em>Find all of the columns that are shorter than the specified threshold.</em> |
| public | <strong>findUndersizedColumns(</strong><em>mixed</em> <strong>$minimumWidths</strong>)</strong> : <em>mixed</em><br /><em>Find all of the columns that are shorter than the corresponding minimum widths.</em> |
| public | <strong>isEmpty()</strong> : <em>bool</em><br /><em>Return true if there is no data in this object</em> |
| public | <strong>keys()</strong> : <em>void</em><br /><em>Return the available keys (column identifiers) from the calculated data set.</em> |
| public | <strong>lastColumn()</strong> : <em>void</em> |
| public | <strong>paddingSpace(</strong><em>mixed</em> <strong>$paddingInEachCell</strong>, <em>mixed</em> <strong>$extraPaddingAtEndOfLine</strong>, <em>mixed</em> <strong>$extraPaddingAtBeginningOfLine</strong>)</strong> : <em>void</em> |
| public | <strong>removeColumns(</strong><em>mixed</em> <strong>$columnKeys</strong>)</strong> : <em>void</em><br /><em>Remove all of the specified columns from this data structure.</em> |
| public | <strong>selectColumns(</strong><em>mixed</em> <strong>$columnKeys</strong>)</strong> : <em>void</em><br /><em>Select all columns that exist in the provided list of keys.</em> |
| public | <strong>setWidth(</strong><em>mixed</em> <strong>$key</strong>, <em>mixed</em> <strong>$width</strong>)</strong> : <em>void</em><br /><em>Set the length of the specified column.</em> |
| public static | <strong>sumWidth(</strong><em>mixed</em> <strong>$widths</strong>)</strong> : <em>void</em><br /><em>Return the sum of the lengths of the provided widths.</em> |
| public | <strong>totalWidth()</strong> : <em>void</em><br /><em>Return the sum of the lengths of the provided widths.</em> |
| public | <strong>width(</strong><em>mixed</em> <strong>$key</strong>)</strong> : <em>void</em><br /><em>Return the length of the specified column.</em> |
| public | <strong>widths()</strong> : <em>void</em><br /><em>Return all of the lengths</em> |
| protected | <strong>findColumnsUnderThreshold(</strong><em>array</em> <strong>$thresholdWidths</strong>)</strong> : <em>mixed</em> |

<hr />

### Interface: \Consolidation\OutputFormatters\Validate\ValidationInterface

> Formatters may implement ValidationInterface in order to indicate whether a particular data structure is supported.  Any formatter that does not implement ValidationInterface is assumed to only operate on arrays, or data types that implement SimplifyToArrayInterface.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>isValidDataType(</strong><em>[\ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)</em> <strong>$dataType</strong>)</strong> : <em>bool</em><br /><em>Return true if the specified format is valid for use with this formatter.</em> |
| public | <strong>validate(</strong><em>mixed</em> <strong>$structuredData</strong>)</strong> : <em>mixed</em><br /><em>Throw an IncompatibleDataException if the provided data cannot be processed by this formatter.  Return the source data if it is valid. The data may be encapsulated or converted if necessary.</em> |

<hr />

### Interface: \Consolidation\OutputFormatters\Validate\ValidDataTypesInterface

> Formatters may implement ValidDataTypesInterface in order to indicate exactly which formats they support.  The validDataTypes method can be called to retrieve a list of data types useful in providing hints in exception messages about which data types can be used with the formatter. Note that it is OPTIONAL for formatters to implement this interface. If a formatter implements only ValidationInterface, then clients that request the formatter via FormatterManager::write() will still get a list (via an InvalidFormatException) of all of the formats that are usable with the provided data type.  Implementing ValidDataTypesInterface is benefitial to clients who instantiate a formatter directly (via `new`). Formatters that implement ValidDataTypesInterface may wish to use ValidDataTypesTrait.

| Visibility | Function |
|:-----------|:---------|
| public | <strong>validDataTypes()</strong> : <em>[\ReflectionClass[]](http://php.net/manual/en/class.reflectionclass.php)</em><br /><em>Return the list of data types acceptable to this formatter</em> |

*This class implements [\Consolidation\OutputFormatters\Validate\ValidationInterface](#interface-consolidationoutputformattersvalidatevalidationinterface)*

