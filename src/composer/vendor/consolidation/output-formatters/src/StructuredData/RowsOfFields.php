<?php
namespace Consolidation\OutputFormatters\StructuredData;

use Consolidation\OutputFormatters\Options\FormatterOptions;

/**
 * Holds an array where each element of the array is one row,
 * and each row contains an associative array where the keys
 * are the field names, and the values are the field data.
 *
 * It is presumed that every row contains the same keys.
 */
class RowsOfFields extends AbstractStructuredList
{
    /**
     * Restructure this data for output by converting it into a table
     * transformation object.
     *
     * @param FormatterOptions $options Options that affect output formatting.
     * @return Consolidation\OutputFormatters\Transformations\TableTransformation
     */
    public function restructure(FormatterOptions $options)
    {
        $data = $this->getArrayCopy();
        return $this->createTableTransformation($data, $options);
    }

    public function getListData(FormatterOptions $options)
    {
        return array_keys($this->getArrayCopy());
    }

    protected function defaultOptions()
    {
        return [
            FormatterOptions::LIST_ORIENTATION => false,
        ] + parent::defaultOptions();
    }
}
