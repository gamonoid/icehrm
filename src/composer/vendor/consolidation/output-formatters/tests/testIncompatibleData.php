<?php
namespace Consolidation\OutputFormatters;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\PropertyList;
use Consolidation\OutputFormatters\Exception\IncompatibleDataException;

class IncompatibleDataTests extends \PHPUnit_Framework_TestCase
{
    protected $formatterManager;

    function setup() {
        $this->formatterManager = new FormatterManager();
    }

    protected function assertIncompatibleDataMessage($expected, $formatter, $data)
    {
        $e = new IncompatibleDataException($formatter, $data, $formatter->validDataTypes());
        $this->assertEquals($expected, $e->getMessage());
    }

    public function testIncompatibleData()
    {
        $tableFormatter = $this->formatterManager->getFormatter('table');

        $this->assertIncompatibleDataMessage('Data provided to Consolidation\OutputFormatters\Formatters\TableFormatter must be either an instance of Consolidation\OutputFormatters\StructuredData\RowsOfFields or an instance of Consolidation\OutputFormatters\StructuredData\PropertyList. Instead, a string was provided.', $tableFormatter, 'a string');
        $this->assertIncompatibleDataMessage('Data provided to Consolidation\OutputFormatters\Formatters\TableFormatter must be either an instance of Consolidation\OutputFormatters\StructuredData\RowsOfFields or an instance of Consolidation\OutputFormatters\StructuredData\PropertyList. Instead, an instance of Consolidation\OutputFormatters\FormatterManager was provided.', $tableFormatter, $this->formatterManager);
        $this->assertIncompatibleDataMessage('Data provided to Consolidation\OutputFormatters\Formatters\TableFormatter must be either an instance of Consolidation\OutputFormatters\StructuredData\RowsOfFields or an instance of Consolidation\OutputFormatters\StructuredData\PropertyList. Instead, an array was provided.', $tableFormatter, []);
        $this->assertIncompatibleDataMessage('Data provided to Consolidation\OutputFormatters\Formatters\TableFormatter must be either an instance of Consolidation\OutputFormatters\StructuredData\RowsOfFields or an instance of Consolidation\OutputFormatters\StructuredData\PropertyList. Instead, an instance of Consolidation\OutputFormatters\StructuredData\PropertyList was provided.', $tableFormatter, new PropertyList([]));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Undescribable data error: NULL
     */
    public function testUndescribableData()
    {
        $tableFormatter = $this->formatterManager->getFormatter('table');
        $data = $tableFormatter->validate(null);
        $this->assertEquals('Will throw before comparing.', $data);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Data provided to Consolidation\OutputFormatters\Formatters\TableFormatter must be either an instance of Consolidation\OutputFormatters\StructuredData\RowsOfFields or an instance of Consolidation\OutputFormatters\StructuredData\PropertyList. Instead, a string was provided.
     */
    public function testInvalidTableData()
    {
        $tableFormatter = $this->formatterManager->getFormatter('table');
        $data = $tableFormatter->validate('bad data type');
        $this->assertEquals('Will throw before comparing.', $data);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Data provided to Consolidation\OutputFormatters\Formatters\SectionsFormatter must be an instance of Consolidation\OutputFormatters\StructuredData\RowsOfFields. Instead, a string was provided.
     */
    public function testInvalidSectionsData()
    {
        $tableFormatter = $this->formatterManager->getFormatter('sections');
        $data = $tableFormatter->validate('bad data type');
        $this->assertEquals('Will throw before comparing.', $data);
    }
}
