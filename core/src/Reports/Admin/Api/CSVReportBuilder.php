<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:40 PM
 */

namespace Reports\Admin\Api;

class CSVReportBuilder extends ReportBuilder
{
    public function getData($report, $request)
    {
        $query = $this->getMainQuery();
        $where = $this->getWhereQuery($request);
        if ($query == null || $where == null) {
            return null;
        }
        $query.=" ".$where[0];
        return $this->execute($report, $query, $where[1]);
    }
}
