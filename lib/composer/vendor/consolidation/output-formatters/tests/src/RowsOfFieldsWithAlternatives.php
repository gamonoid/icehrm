<?php
namespace Consolidation\TestUtils;

use Consolidation\OutputFormatters\Options\FormatterOptions;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Consolidation\OutputFormatters\StructuredData\RenderCellInterface;

class RowsOfFieldsWithAlternatives extends RowsOfFields implements RenderCellInterface
{
    public function renderCell($key, $cellData, FormatterOptions $options, $rowData)
    {
        if (is_array($cellData)) {
            return implode('|', $cellData);
        }
        return $cellData;
    }
}
