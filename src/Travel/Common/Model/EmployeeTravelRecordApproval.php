<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 8:06 AM
 */

namespace Travel\Common\Model;

class EmployeeTravelRecordApproval extends EmployeeTravelRecord
{

    public function Find($whereOrderBy, $bindarr = false, $pkeysArr = false, $extra = array())
    {
        return $this->findApprovals(
            new EmployeeTravelRecord(),
            $whereOrderBy,
            $bindarr,
            $pkeysArr,
            $extra
        );
    }
}
