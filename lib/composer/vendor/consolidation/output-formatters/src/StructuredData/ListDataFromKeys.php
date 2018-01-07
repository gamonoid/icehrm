<?php
namespace Consolidation\OutputFormatters\StructuredData;

use Consolidation\OutputFormatters\Options\FormatterOptions;

/**
 * Represents aribtrary array data (structured or unstructured) where the
 * data to display in --list format comes from the array keys.
 */
class ListDataFromKeys extends \ArrayObject implements ListDataInterface
{
    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getListData(FormatterOptions $options)
    {
        return array_keys($this->getArrayCopy());
    }
}
