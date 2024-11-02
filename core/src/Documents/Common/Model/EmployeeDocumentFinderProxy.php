<?php

namespace Documents\Common\Model;

use Classes\BaseService;
use Classes\FinderProxy;

class EmployeeDocumentFinderProxy extends EmployeeDocument implements FinderProxy
{
    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array())
    {
        $find = $this->createFindQuery();
        if (strpos($whereOrderBy, 'ORDER BY') !== false) {
            $parts = explode('ORDER BY', $whereOrderBy);
            $whereOrderBy = sprintf('%s AND %s%s ORDER BY %s', $parts[0], $find, $this->getAdditionalQuery(), $parts[1]);
        } else {
            $parts = explode('LIMIT', $whereOrderBy);
            $whereOrderBy = sprintf('%s AND %s%s LIMIT %s', $parts[0], $find, $this->getAdditionalQuery(), $parts[1]);
        }
        return parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);
    }
    // @codingStandardsIgnoreEnd

    protected function getAdditionalQuery()
    {
        return 'hidden = 0 ';
    }

    public function getTotalCount($query, $data)
    {
        $find = $this->createFindQuery();
        $sql = "Select count(id) as count from " . $this->table;
        $sql .= " WHERE 1=1 AND " . $find.$this->getAdditionalQuery().$query;
        return $this->countRows($sql, $data);
    }

    /**
     * @return string
     */
    private function createFindQuery()
    {
        $find = '';
        $user = BaseService::getInstance()->getCurrentUser();

        if ($user->user_level == 'Employee' || ($user->user_level == 'Manager' && !$this->isSubordinateQuery)) {
            if (BaseService::getInstance()->isEmployeeSwitched()) {
                $find = ' visible_to =\'Owner\' AND ';
            } else {
                $find = ' visible_to IN (\'Owner\', \'Owner Only\') AND ';
            }

            $document = new Document();
            $hiddenDocumentTypes = $document->Find(
                "share_with_employee = ?",
                ['No']
            );

            $hiddenTypeIds = [];
            foreach ($hiddenDocumentTypes as $hiddenDocumentType) {
                $hiddenTypeIds[] = $hiddenDocumentType->id;
            }

            if (count($hiddenTypeIds) > 0) {
                $find .= ' document NOT IN (\'' . implode('\',\'', $hiddenTypeIds) . '\') AND ';
            }
        } elseif ($user->user_level == 'Manager' && $this->isSubordinateQuery) {
            // Original $whereOrderBy already contain employee selection
            // So here if isSubOrdinates is true if the query coming from Employee -> Document Management
            // In that case we need to show documents from sub ordinates
            // These docs can can be owner and manager both
            if ($this->isSubordinateQuery) {
                $find .= ' visible_to in (\'Owner\', \'Manager\') AND ';
            } else {
                // Here we are showing the documents for the manager
                // If someone upload a document for this manager and make it visible to manager,
                // that means only the manager of this manager can see the document
                // So it should not be visible to this manager
                $find .= ' visible_to in (\'Owner\') AND ';
            }
        }
        return $find;
    }
}
