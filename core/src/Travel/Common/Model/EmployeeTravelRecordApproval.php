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
    protected $allowCustomFields = false;

    // @codingStandardsIgnoreStart
    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array())
    {
        // @codingStandardsIgnoreEnd
        return $this->findApprovals(
            new EmployeeTravelRecord(),
            $whereOrderBy,
            $bindarr,
            $pkeysArr,
            $extra
        );
    }
    /**
     * No module grants Manager access to this model (module meta.json user_levels),
     * so no manager-facing screen reads it. The inherited BaseModel default
     * would expose the whole table on the generic service.php path.
     */
    public function getManagerAccess()
    {
        return array();
    }

    /**
     * No module grants Employee access to this model (module meta.json user_levels),
     * so no employee-facing screen reads it. The inherited BaseModel default
     * would expose the whole table on the generic service.php path.
     */
    public function getUserAccess()
    {
        return array();
    }

}
