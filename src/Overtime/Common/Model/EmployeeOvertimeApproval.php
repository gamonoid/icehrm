<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:15 PM
 */

namespace Overtime\Common\Model;

class EmployeeOvertimeApproval extends EmployeeOvertime
{

    public function Find($whereOrderBy, $bindarr = false, $pkeysArr = false, $extra = array())
    {
        return $this->findApprovals(new EmployeeOvertime(), $whereOrderBy, $bindarr, $pkeysArr, $extra);
    }
}
