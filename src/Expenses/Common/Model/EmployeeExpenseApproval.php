<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 12:12 PM
 */

namespace Expenses\Common\Model;

class EmployeeExpenseApproval extends EmployeeExpense
{

    public function Find($whereOrderBy, $bindarr = false, $pkeysArr = false, $extra = array())
    {
        return $this->findApprovals(new EmployeeExpense(), $whereOrderBy, $bindarr, $pkeysArr, $extra);
    }
}
