<?php
namespace Classes\Data\Query;

class DataQuery
{
    protected $table;
    protected $columns = [];
    protected $fieldMapping;
    protected $filters = null;
    protected $startPage = 0;
    protected $length = 15;
    protected $isSubOrdinates = false;
    protected $skipProfileRestriction = false;
    protected $sortingEnabled = false;
    protected $sortColumn;
    protected $sortOrder = '';
    protected $searchTerm = null;
    protected $isLengthSet = false;
    protected $orderBy = null;

    /**
     * DataQuery constructor.
     *
     * @param $table
     * @param bool       $sortingEnabled
     * @param $sortColumn
     * @param string     $sortOrder
     */
    public function __construct($table, $sortingEnabled = false, $sortColumn = false, $sortOrder = '', $orderBy = null)
    {
        $this->table = $table;
        $this->sortingEnabled = $sortingEnabled;
        $this->sortColumn = $sortColumn;
        $this->sortOrder = $sortOrder;
        $this->orderBy = $orderBy;
    }


    public function addColumn($column)
    {
        $this->columns[] = $column;
        return $this;
    }

    public function addFilter($filter)
    {
        if ($this->filters === null) {
            $this->filters = new \stdClass();
        }
        $this->filters->{$filter->getName()} = $filter->getValue();
        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return string
     */
    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * @return string
     */
    public function getFilters()
    {
        return empty($this->filters) ? '' : json_encode($this->filters);
    }

    /**
     * @return int
     */
    public function getStartPage()
    {
        return $this->startPage;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->searchTerm;
    }

    /**
     * @param string $searchTerm
     */
    public function setSearchTerm($searchTerm)
    {
        $this->searchTerm = $searchTerm;
    }

    /**
     * @return boolean
     */
    public function isIsSubOrdinates()
    {
        return $this->isSubOrdinates;
    }

    /**
     * @return boolean
     */
    public function isSkipProfileRestriction()
    {
        return $this->skipProfileRestriction;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    public function getSortingData()
    {
        $data = array();

        $data['sorting'] = $this->sortingEnabled ? '1' : '0';
        if (!$this->sortingEnabled) {
            return $data;
        }

        $data['column'] = $this->sortColumn;
        $data['order'] = $this->sortOrder;

        return $data;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
        if ($length > 0) {
            $this->isLengthSet = true;
        }
    }

    /**
     * @param int $startPage
     */
    public function setStartPage($startPage)
    {
        $this->startPage = $startPage;
    }

    /**
     * @param boolean $isSubOrdinates
     */
    public function setIsSubOrdinates($isSubOrdinates)
    {
        $this->isSubOrdinates = $isSubOrdinates;
    }

    /**
     * @return bool
     */
    public function isLengthSet()
    {
        return $this->isLengthSet;
    }

    /**
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * @param string $fieldMapping
     */
    public function setFieldMapping($fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * @param $filters
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @param bool $skipProfileRestriction
     */
    public function setSkipProfileRestriction(bool $skipProfileRestriction)
    {
        $this->skipProfileRestriction = $skipProfileRestriction;
    }

    /**
     * @param bool $sortingEnabled
     */
    public function setSortingEnabled(bool $sortingEnabled)
    {
        $this->sortingEnabled = $sortingEnabled;
    }

    /**
     * @param string $sortColumn
     */
    public function setSortColumn($sortColumn)
    {
        $this->sortColumn = $sortColumn;
    }

    /**
     * @param string $sortOrder
     */
    public function setSortOrder(string $sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return null
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param null $orderBy
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }
}
