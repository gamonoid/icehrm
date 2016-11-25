<?php
namespace Consolidation\OutputFormatters\StructuredData;

interface TableDataInterface
{
    /**
     * Return the original data for this table.  Used by any
     * formatter that is -not- a table.
     */
    public function getOriginalData();

    /**
     * Convert structured data into a form suitable for use
     * by the table formatter.
     *
     * @param boolean $includeRowKey Add a field containing the
     *   key from each row.
     *
     * @return array
     */
    public function getTableData($includeRowKey = false);
}
