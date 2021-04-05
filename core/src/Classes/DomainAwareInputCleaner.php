<?php
namespace Classes;

class DomainAwareInputCleaner
{
    public function cleanTableColumn($input)
    {
        if ($this->isEmpty($input) || $this->isValidColumnName($input)) {
            return $input;
        }

        return '';
    }

    public function cleanMapping($mapping)
    {
        return $mapping;
    }

    public function cleanOrderBy($orderBy)
    {
        if (empty($orderBy)) {
            return $orderBy;
        }

        $suffix = '';
        if (strstr($orderBy, ' desc')) {
            $suffix = ' desc';
            $orderBy = str_replace(' desc', '', $orderBy);
        }

        if (!$this->cleanTableColumn($orderBy)) {
            return '';
        }

        return $orderBy.$suffix;
    }

    public function cleanColumns($columns)
    {
        if (empty($columns)) {
            return $columns;
        }

        $columnData = json_decode($columns, true);

        foreach ($columnData as $column) {
            if (!$this->isValidColumnName($column)) {
                return '[]';
            }
        }

        return $columns;
    }

    public function cleanFilters($filters)
    {
        if (empty($filters)) {
            return $filters;
        }

        $filterData = json_decode($filters, true);
        foreach ($filterData as $name => $value) {
            if (!$this->isValidColumnName($name) || !$this->isValidFilterValue($value)) {
                return '';
            }
        }

        return $filters;
    }

    public function cleanSearch($searchTerm)
    {
        if (!$this->isValidFilterValue($searchTerm)) {
            return '';
        }

        return $searchTerm;
    }

    private function isEmpty($input)
    {
        return empty($input) || trim($input) === '';
    }

    private function isValidColumnName($input)
    {
        return !!preg_match('/^[a-zA-Z_]+$/', $input);
    }

    private function isValidFilterValue($input)
    {
        if (is_array($input)) {
            $isValid = true;
            foreach ($input as $val) {
                $isValid = $isValid && !!preg_match('/^[-_: \d\p{L}]+$/u', $val);
            }

            return $isValid;
        }
        return !!preg_match('/^[-_: \d\p{L}]+$/u', $input);
    }
}
