<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 10/3/17
 * Time: 5:39 PM
 */

namespace Data\Admin\Import;

use Classes\IceResponse;
use Company\Common\Model\CompanyStructure;
use Data\Admin\Api\DataImporter;
use Payroll\Common\Model\Deduction;
use Payroll\Common\Model\DeductionGroup;
use Payroll\Common\Model\Payroll;
use Payroll\Common\Model\PayrollColumn;
use Payroll\Common\Model\PayslipTemplate;
use Salary\Common\Model\SalaryComponent;
use Salary\Common\Model\SalaryComponentType;
use Utils\LogManager;

class PayrollDataImporter implements DataImporter
{

    protected $model;
    protected $lastStatus = IceResponse::ERROR;

    public function getResult()
    {
        return $this->model;
    }

    public function process($data, $dataImporterId)
    {
        $compStructure = new CompanyStructure();
        $compStructure->Load("1 = 1 limit 1", array());
        if (empty($compStructure->id)) {
            $this->lastStatus = IceResponse::ERROR;
            return "No company structures exists";
        }
        $this->model = json_decode($data);
        $deductionGroup = $this->createDeductionGroup($this->model);
        if (!$deductionGroup) {
            $this->lastStatus = IceResponse::ERROR;
            return "Deduction group already exists";
        }
        $salaryComponentTypeIdMap = $this->addSalaryComponentTypes($this->model);
        $salaryComponentIdMap = $this->addSalaryComponents($this->model, $salaryComponentTypeIdMap);
        $payrollColumnIdMap = $this->addPayrollColumns($this->model, $deductionGroup->id, $salaryComponentIdMap);
        $deductionIdMap = $this->addDeductions(
            $this->model,
            $deductionGroup->id,
            $salaryComponentTypeIdMap,
            $salaryComponentIdMap,
            $payrollColumnIdMap
        );
        $this->refinePayrollColumns($payrollColumnIdMap, $deductionIdMap);
        $payslipTemplate = $this->addPayslipTemplate($this->model, $payrollColumnIdMap);
        $this->addPayroll(
            $this->model,
            $payrollColumnIdMap,
            $deductionGroup->id,
            $payslipTemplate->id,
            $compStructure->id
        );
        $this->lastStatus = IceResponse::SUCCESS;

        return $this->model;
    }

    protected function addPayroll($model, $payrollColumnIdMap, $deductionGroupId, $payslipId, $departmentId)
    {
        $samplePr = $model->samplePayroll;
        $payroll = new Payroll();
        $payroll->name = $samplePr->name;
        $payroll->pay_period = $samplePr->pay_period;
        $payroll->date_start = $samplePr->date_start;
        $payroll->date_end = $samplePr->date_end;
        $payroll->columns = $this->replaceJsonIds(
            $samplePr->columns,
            $payrollColumnIdMap
        );
        $payroll->status = $samplePr->status;
        $payroll->deduction_group = $deductionGroupId;
        $payroll->payslipTemplate = $payslipId;
        $payroll->department = $departmentId;
        $ok = $payroll->Save();

        if (!$ok) {
            LogManager::getInstance()->error($payroll->ErrorMsg());
        }
    }

    protected function addPayslipTemplate($model, $payrollColumnIdMap)
    {
        $pst = $model->payslipTemplate;
        $payslipTemplate = new PayslipTemplate();
        $payslipTemplate->name = $pst->name;

        $data = json_decode($pst->data, true);
        $newData = [];
        foreach ($data as $row) {
            if ($row['type'] === 'Payroll Column') {
                $row['payrollColumn'] = $payrollColumnIdMap[$row['payrollColumn']];
            }
            $newData[] = $row;
        }

        $payslipTemplate->data = json_encode($newData);

        $payslipTemplate->status = $pst->status;
        $payslipTemplate->created = $pst->created;
        $payslipTemplate->updated = $pst->updated;

        $payslipTemplate->Save();

        return $payslipTemplate;
    }

    protected function refinePayrollColumns($payrollColumnIdMap, $deductionIdMap)
    {
        $payrollColumn = new PayrollColumn();
        $columns = $payrollColumn->Find('id in ('.implode(',', array_values($payrollColumnIdMap)).')', array());
        foreach ($columns as $column) {
            $column->deductions = $this->replaceJsonIds(
                $column->deductions,
                $deductionIdMap
            );

            $column->add_columns = $this->replaceJsonIds(
                $column->add_columns,
                $payrollColumnIdMap
            );

            $column->sub_columns = $this->replaceJsonIds(
                $column->sub_columns,
                $payrollColumnIdMap
            );

            $column->sub_columns = $this->replaceJsonIdsForCalculations(
                $column->calculation_columns,
                $payrollColumnIdMap
            );

            $column->Save();
        }
    }

    protected function addPayrollColumns($model, $deductionGroupId, $salaryComponentIdMap)
    {
        $payrollColumnIdMap = [];
        $payrollColumns = $model->columns;
        foreach ($payrollColumns as $payrollColumn) {
            $column = new PayrollColumn();
            $column->name = $payrollColumn->name;
            $column->calculation_hook = $payrollColumn->calculation_hook;
            $column->salary_components = $this->replaceJsonIds(
                $payrollColumn->salary_components,
                $salaryComponentIdMap
            );

            $column->deductions = $payrollColumn->deductions; // need to map
            $column->add_columns = $payrollColumn->add_columns; // need to map
            $column->sub_columns = $payrollColumn->sub_columns; // need to map
            $column->colorder = $payrollColumn->colorder;
            $column->editable = $payrollColumn->editable;
            $column->enabled = $payrollColumn->enabled;
            $column->default_value = $payrollColumn->default_value;
            $column->calculation_columns = $payrollColumn->calculation_columns; // need to map
            $column->calculation_function = $payrollColumn->calculation_function;
            $column->deduction_group = $deductionGroupId;

            $column->Save();

            $payrollColumnIdMap[$payrollColumn->id] = $column->id;
        }
        return $payrollColumnIdMap;
    }

    protected function addDeductions(
        $model,
        $deductionGroupId,
        $salaryComponentTypeIdMap,
        $salaryComponentIdMap,
        $payrollColumnIdMap
    ) {
        $deductionIdMap = [];
        $deductions = $model->deductions;

        foreach ($deductions as $deduction) {
            $dbDeduction = new Deduction();
            $dbDeduction->name = $deduction->name;
            $dbDeduction->componentType = $this->replaceJsonIds(
                $deduction->componentType,
                $salaryComponentTypeIdMap
            );
            $dbDeduction->component = $this->replaceJsonIds(
                $deduction->component,
                $salaryComponentIdMap
            );

            $dbDeduction->payrollColumn = $payrollColumnIdMap[$deduction->payrollColumn];
            $dbDeduction->rangeAmounts = $deduction->rangeAmounts;
            $dbDeduction->deduction_group = $deductionGroupId;

            $dbDeduction->Save();
            $deductionIdMap[$deduction->id] = $dbDeduction->id;
        }
        return $deductionIdMap;
    }

    protected function createDeductionGroup($model)
    {
        $deductionGroup = new DeductionGroup();
        $deductionGroup->Load("name = ?", array($model->name));
        if (!empty($deductionGroup->id)) {
            return false;
        }

        $deductionGroup->name = $model->name;
        $deductionGroup->description = $model->description;
        $ok = $deductionGroup->Save();

        if (!$ok) {
            return false;
        }

        return $deductionGroup;
    }

    /**
     * @param $salaryComponentTypeIdMap
     */
    protected function addSalaryComponentTypes($model)
    {
        $salaryComponentTypeIdMap = [];
        $salaryComponentTypes = $model->salaryComponentTypes;
        foreach ($salaryComponentTypes as $salaryComponentType) {
            $tempSct = new SalaryComponentType();
            $tempSct->Load(
                "code = ? and name = ?",
                array($salaryComponentType->code, $salaryComponentType->name)
            );

            if (!empty($tempSct->id)) {
                $salaryComponentTypeIdMap[$salaryComponentType->id] = $tempSct->id;
                continue;
            }

            $tempSct = new SalaryComponentType();
            $tempSct->code = $salaryComponentType->code;
            $tempSct->name = $salaryComponentType->name;
            $tempSct->Save();

            $salaryComponentTypeIdMap[$salaryComponentType->id] = $tempSct->id;
        }

        return $salaryComponentTypeIdMap;
    }

    protected function addSalaryComponents($model, $salaryComponentTypeIdMap)
    {
        $salaryComponentIdMap = [];
        $salaryComponents = $model->salaryComponents;
        foreach ($salaryComponents as $salaryComponent) {
            $tempSct = new SalaryComponent();
            $tempSct->Load(
                "componentType = ? and name = ?",
                array(
                    $salaryComponentTypeIdMap[$salaryComponent->componentType],
                    $salaryComponent->name
                )
            );

            if (!empty($tempSct->id)) {
                $salaryComponentIdMap[$salaryComponent->id] = $tempSct->id;
                continue;
            }

            $tempSct = new SalaryComponent();
            $tempSct->name = $salaryComponent->name;
            $tempSct->componentType = $salaryComponentTypeIdMap[$salaryComponent->componentType];
            $tempSct->details = $salaryComponent->details;
            $tempSct->Save();

            $salaryComponentIdMap[$salaryComponent->id] = $tempSct->id;
        }

        return $salaryComponentIdMap;
    }

    private function replaceJsonIds($ids, $idMap)
    {
        $newIds = [];
        $data = json_decode($ids, true);
        foreach ($data as $id) {
            $newIds[] = $idMap[$id];
        }
        return json_encode($newIds);
    }

    private function replaceJsonIdsForCalculations($calculations, $idMap)
    {
        $newCalculations = [];
        $data = json_decode($calculations, true);
        foreach ($data as $cal) {
            $cal['column'] = $idMap[$cal['column']];
            $newCalculations[] = $cal;
        }
        return json_encode($newCalculations);
    }

    public function getLastStatus()
    {
        return $this->lastStatus;
    }
}
