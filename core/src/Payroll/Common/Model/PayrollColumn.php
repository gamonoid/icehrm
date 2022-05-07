<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:14 PM
 */

namespace Payroll\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

class PayrollColumn extends BaseModel
{
    public $table = 'PayrollColumns';
    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    private function reorderColumns()
    {
        $payrollColumn = new PayrollColumn();
        $columns = $payrollColumn->Find('1 = 1 order by colorder', []);
        $currentOrder = 1;
        foreach ($columns as $column) {
            $column->colorder = $currentOrder;
            $currentOrder++;
            $column->Save();
        }
    }

    private function shouldReorderColumns()
    {
        $payrollColumn = new PayrollColumn();
        $columns = $payrollColumn->Find('colorder <= ?', [0]);

        return count($columns) > 0;
    }

    private function handleColumnOrderChange($newCol)
    {
        $payrollColumn = new PayrollColumn();
        $columns = $payrollColumn->Find('1 = 1 order by colorder', []);
        $currentOrder = 1;
        foreach ($columns as $column) {
            if (!empty($newCol->id) && (int)$column->id ===  (int)$newCol->id) {
                continue;
            }
            if ((int)$newCol->colorder === (int)$currentOrder) {
                $currentOrder++;
            }
            $column->colorder = $currentOrder;
            $currentOrder++;
            $column->Save();
        }
    }

    private function decodeCalculationFunction($obj)
    {
        if (!empty($obj->calculation_function)) {
            $obj->calculation_function = base64_decode($obj->calculation_function);
        }

        return $obj;
    }

    /**
     * @param $obj
     * @return IceResponse
     */
    public function executePreSaveActions($obj)
    {
//        if ($this->shouldReorderColumns()) {
//            $this->reorderColumns();
//        }

        $this->handleColumnOrderChange($obj);

        $obj = $this->decodeCalculationFunction($obj);
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    /**
     * @param $obj
     * @return IceResponse
     */
    public function executePreUpdateActions($obj)
    {
        $oldColumn = new PayrollColumn();
        $oldColumn->Load('id = ?', [$obj->id]);

//        if ($this->shouldReorderColumns()) {
//            $this->reorderColumns();
//        }

        if (!empty($oldColumn->id) && (int)$oldColumn->coloder !== (int)$obj->colorder) {
            $this->handleColumnOrderChange($obj);
        }

        $obj = $this->decodeCalculationFunction($obj);

        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePostDeleteActions($obj)
    {
        $this->reorderColumns();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('payroll', 'admin'),
        ];
    }
}
