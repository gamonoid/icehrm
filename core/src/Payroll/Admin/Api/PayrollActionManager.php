<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:18 PM
 */

namespace Payroll\Admin\Api;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\SubActionManager;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Payroll\Common\Model\Deduction;
use Payroll\Common\Model\DeductionGroup;
use Payroll\Common\Model\Payroll;
use Payroll\Common\Model\PayrollCalculations;
use Payroll\Common\Model\PayrollColumn;
use Payroll\Common\Model\PayrollData;
use Salary\Common\Model\EmployeeSalary;
use Salary\Common\Model\PayrollEmployee;
use Salary\Common\Model\SalaryComponent;
use Utils\LogManager;
use Utils\Math\EvalMath;
use Utils\ScriptRunner;
use Classes\Migration\AbstractMigration;

class PayrollActionManager extends SubActionManager
{

    const REG_HOURS_PER_WEEK = 40;

    protected $calCache = array();

    public function addToCalculationCache($key, $val)
    {
        $this->calCache[$key] = $val;
    }

    public function getFromCalculationCache($key)
    {
        if (isset($this->calCache[$key])) {
            return $this->calCache[$key];
        }

        return null;
    }

    public function calculatePayrollColumn(
        $col,
        $payroll,
        $employeeId,
        $payrollEmployeeId,
        $noColumnCalculations = false
    ) {

        $val = $this->getFromCalculationCache($col->id."-".$payroll->id."-".$employeeId);
        if (!empty($val)) {
            return $val;
        }

        if (!empty($col->calculation_hook)) {
            $valueData = BaseService::getInstance()->executeCalculationHook(
                array($employeeId, $payroll->date_start, $payroll->date_end),
                $col->calculation_hook,
                $col->calculation_function
            );
            if (is_array($valueData) && $valueData[0] == 'string') {
                $val = $valueData[1];
            } else {
                $val = number_format(round($valueData, 2), 2, '.', '');
            }

            $this->addToCalculationCache($col->id."-".$payroll->id."-".$employeeId, $val);
            return $val;
        }

        $sum = 0;

        $payRollEmp = new PayrollEmployee();
        $payRollEmp->Load("id = ?", array($payrollEmployeeId));

        //Salary
        LogManager::getInstance()->info("salary components row:".$col->salary_components);
        if (!empty($col->salary_components) &&
            !empty(json_decode($col->salary_components, true))) {
            $salaryComponent = new SalaryComponent();
            $salaryComponents = $salaryComponent->Find(
                "id in (".implode(",", json_decode($col->salary_components, true)).")",
                array()
            );
            LogManager::getInstance()->info("salary components:".$salaryComponents);
            foreach ($salaryComponents as $salaryComponent) {
                $sum += $this->getTotalForEmployeeSalaryByComponent($employeeId, $salaryComponent->id);
            }
        }

        //Deductions
        if (!empty($col->deductions) &&
            !empty(json_decode($col->deductions, true))) {
            $deduction = new Deduction();
            if (empty($payRollEmp->deduction_group)) {
                $deductions = $deduction->Find(
                    "id in (".implode(",", json_decode($col->deductions, true)).")",
                    array()
                );
            } else {
                $deductions = $deduction->Find(
                    "deduction_group = ? and id in (".implode(",", json_decode($col->deductions, true)).")",
                    array($payRollEmp->deduction_group)
                );
            }

            $allowedDeductions = $this->getAllowedDeductionsForEmployee($employeeId, $payRollEmp->deduction_group);
            foreach ($deductions as $deduction) {
                if (!in_array($deduction->id, $allowedDeductions)) {
                    continue;
                }
            }
            foreach ($deductions as $deduction) {
                $sum += $this->calculateDeductionValue($employeeId, $deduction, $payroll);
            }
        }

        if (!$noColumnCalculations) {
            $evalMath = new EvalMath();
            if ($col->function_type === 'Simple') {
                $evalMath->evaluate('max(x,y) = (y - x) * ceil(tanh(exp(tanh(y - x)) - exp(0))) + x');
                $evalMath->evaluate('min(x,y) = y - (y - x) * ceil(tanh(exp(tanh(y - x)) - exp(0)))');
            }

            if (!empty($col->add_columns) &&
                !empty(json_decode($col->add_columns, true))) {
                $colIds = json_decode($col->add_columns, true);
                $payrollColumn = new PayrollColumn();
                $payrollColumns = $payrollColumn->Find("id in (".implode(",", $colIds).")", array());
                foreach ($payrollColumns as $payrollColumn) {
                    $sum += $this->calculatePayrollColumn(
                        $payrollColumn,
                        $payroll,
                        $employeeId,
                        $payrollEmployeeId,
                        true
                    );
                }
            }

            if (!empty($col->sub_columns) &&
                !empty(json_decode($col->sub_columns, true))) {
                $colIds = json_decode($col->sub_columns, true);
                $payrollColumn = new PayrollColumn();
                $payrollColumns = $payrollColumn->Find("id in (".implode(",", $colIds).")", array());
                foreach ($payrollColumns as $payrollColumn) {
                    $sum -= $this->calculatePayrollColumn(
                        $payrollColumn,
                        $payroll,
                        $employeeId,
                        $payrollEmployeeId,
                        true
                    );
                }
            }

            if (!empty(trim($col->calculation_function))) {
                $cc = json_decode($col->calculation_columns);
                $func = $col->calculation_function;
                $variableList = [];
                foreach ($cc as $c) {
                    $value = $this->getFromCalculationCache($c->column."-".$payroll->id."-".$employeeId);
                    if ($col->function_type === 'Simple') {
                        if (empty($value)) {
                            $value = 0.00;
                        }
                        $func = str_replace($c->name, $value, $func);
                    } else {
                        $variableList[$c->name] = $value;
                    }
                }
                try {
                    if ($col->function_type === 'Simple') {
                        $sum += $evalMath->evaluate($func);
                    } else {
                        $variableList = $this->updateDefaultVars($variableList, $payroll, $employeeId);
                        $sum = ScriptRunner::executeJs($variableList, $func);
                    }
                } catch (\Exception $e) {
                    LogManager::getInstance()->error("Error:".$e->getMessage());
                    LogManager::getInstance()->notifyException($e);
                }
            }
        }

        if (is_numeric($sum)) {
            $sum = number_format(round($sum, 2), 2, '.', '');
        } else {
            $sum = htmlentities($sum, ENT_QUOTES, 'UTF-8');
        }

        $this->addToCalculationCache($col->id."-".$payroll->id."-".$employeeId, $sum);
        return $sum;
    }

    private function updateDefaultVars($variableList, $payroll, $employeeId)
    {
        $mapping = '{"nationality":["Nationality","id","name"],'
            .'"employment_status":["EmploymentStatus","id","name"],"job_title":["JobTitle","id","name"],'
            .'"pay_grade":["PayGrade","id","name"],"country":["Country","code","name"],'
            .'"province":["Province","id","name"],"department":["CompanyStructure","id","title"],'
            .'"supervisor":["Employee","id","first_name+last_name"]}';

        $employee = BaseService::getInstance()->getElement('Employee', $employeeId, $mapping, true);
        $employee = BaseService::getInstance()->cleanUpAll($employee);

        $variableList['employee'] = $employee;
        $variableList['payroll'] = $payroll;

        return $variableList;
    }

    private function calculateDeductionValue($employeeId, $deduction, $payroll)
    {

        $salaryComponents = array();
        if (!empty($deduction->componentType) && !empty(json_decode($deduction->componentType, true))) {
            $salaryComponent = new SalaryComponent();
            $salaryComponents = $salaryComponent->Find(
                "componentType in (".implode(",", json_decode($deduction->componentType, true)).")",
                array()
            );
        }

        $salaryComponents2 = array();
        if (!empty($deduction->component) && !empty(json_decode($deduction->component, true))) {
            $salaryComponent = new SalaryComponent();
            $salaryComponents2 = $salaryComponent->Find(
                "id in (".implode(",", json_decode($deduction->component, true)).")",
                array()
            );
        }

        $sum = 0;

        $salaryComponentIDs = array();
        foreach ($salaryComponents as $sc) {
            $salaryComponentIDs[] = $sc->id;
            $sum += $this->getTotalForEmployeeSalaryByComponent($employeeId, $sc->id);
        }

        foreach ($salaryComponents2 as $sc) {
            if (!in_array($sc->id, $salaryComponentIDs)) {
                $salaryComponents[] = $sc;
                $sum += $this->getTotalForEmployeeSalaryByComponent($employeeId, $sc->id);
            }
        }

        if (!empty($deduction->payrollColumn)) {
            $columnVal = $this->getFromCalculationCache($deduction->payrollColumn."-".$payroll->id."-".$employeeId);
            if (!empty($columnVal)) {
                $sum += $columnVal;
            }
        }

        $deductionFunction = $this->getDeductionFunction($deduction, $sum);
        if (empty($deductionFunction)) {
            LogManager::getInstance()->error("Deduction function not found");
            return 0;
        }

        $deductionFunction = str_replace("X", $sum, $deductionFunction);

        $evalMath = new EvalMath();
        $val = $evalMath->evaluate($deductionFunction);
        return floatval($val);
    }

    private function getDeductionFunction($deduction, $amount)
    {
        $amount = floatval($amount);
        $ranges = json_decode($deduction->rangeAmounts);
        foreach ($ranges as $range) {
            $lowerLimitPassed = false;
            if ($range->lowerCondition == "No Lower Limit") {
                $lowerLimitPassed = true;
            } elseif ($range->lowerCondition == "gt") {
                if (floatval($range->lowerLimit) < $amount) {
                    $lowerLimitPassed = true;
                }
            } elseif ($range->lowerCondition == "gte") {
                if (floatval($range->lowerLimit) <= $amount) {
                    $lowerLimitPassed = true;
                }
            }

            $upperLimitPassed = false;
            if ($range->upperCondition == "No Upper Limit") {
                $upperLimitPassed = true;
            } elseif ($range->upperCondition == "lt") {
                if (floatval($range->upperLimit) > $amount) {
                    $upperLimitPassed = true;
                }
            } elseif ($range->upperCondition == "lte") {
                if (floatval($range->upperLimit) >= $amount) {
                    $upperLimitPassed = true;
                }
            }

            if ($lowerLimitPassed && $upperLimitPassed) {
                return $range->amount;
            }
        }
        return null;
    }

    private function getTotalForEmployeeSalaryByComponent($employeeId, $salaryComponentId)
    {
        $empSalary = new EmployeeSalary();
        $list = $empSalary->Find("employee = ? and component =?", array($employeeId, $salaryComponentId));
        $sum = 0;
        foreach ($list as $empSalary) {
            $sum += floatval($empSalary->amount);
        }

        return $sum;
    }

    private function getAllowedDeductionsForEmployee($employeeId, $deductionGroup)
    {
        $payrollEmp = new PayrollEmployee();
        $payrollEmp->Load("employee = ?", array($employeeId));

        $allowed = array();
        $deduction = new Deduction();
        if (empty($payrollEmp->deduction_allowed) || empty(json_decode($payrollEmp->deduction_allowed, true))) {
            $allowed =  $deduction->Find("deduction_group = ?", array($deductionGroup));
        } else {
            $allowedIds = json_decode($payrollEmp->deduction_allowed, true);
            $allowed =  $deduction->Find("id in (".implode(",", $allowedIds).")");
        }

        $allowedFiltered = array();
        $disallowedIds = json_decode($payrollEmp->deduction_exemptions, true);

        if (!empty($disallowedIds)) {
            foreach ($allowed as $item) {
                if (!in_array($item->id, $disallowedIds)) {
                    $allowedFiltered[] = $item;
                }
            }
        } else {
            $allowedFiltered = $allowed;
        }

        $allowedIds = array();
        foreach ($allowedFiltered as $item) {
            $allowedIds[] = $item->id;
        }

        return $allowedIds;
    }

    public function getAllData($req)
    {

        $cal = new PayrollCalculations();

        $rowTable = BaseService::getInstance()->getFullQualifiedModelClassName($req->rowTable);
        $columnTable = BaseService::getInstance()->getFullQualifiedModelClassName($req->columnTable);
        $valueTable = BaseService::getInstance()->getFullQualifiedModelClassName($req->valueTable);
        $save = $req->save;

        //Only select employees matching pay frequency

        $payroll = new Payroll();
        $payroll->Load("id = ?", array($req->payrollId));
        $columnList = json_decode($payroll->columns, true);

        //Get Child company structures
        $cssResp = CompanyStructure::getAllChildCompanyStructures($payroll->department);
        error_log(json_encode($cssResp));
        $css = $cssResp->getData();
        $cssIds = array();
        foreach ($css as $c) {
            $cssIds[] = $c->id;
        }

        $employeeNamesById = array();
        $baseEmp = new Employee();
        $baseEmpList = $baseEmp->Find(
            "department in (".implode(",", $cssIds).") and status = ?",
            array('Active')
        );
        $empIds = array();
        foreach ($baseEmpList as $baseEmp) {
            $employeeNamesById[$baseEmp->id] = $baseEmp->first_name." ".$baseEmp->last_name;
            $empIds[] = $baseEmp->id;
        }

        $emp = new $rowTable();
        $emps = $emp->Find(
            "pay_frequency = ? and deduction_group = ? and employee in (".implode(",", $empIds).")",
            array($payroll->pay_period, $payroll->deduction_group)
        );
        if (!$emps) {
            error_log("Error:".$emp->ErrorMsg());
        } else {
            error_log("Employees:".json_encode($emps));
        }

        $employees = array();
        foreach ($emps as $emp) {
            $empNew = new \stdClass();
            $empNew->id = $emp->employee;
            $empNew->payrollEmployeeId = $emp->id;
            $empNew->name = $employeeNamesById[$emp->employee];
            $employees[] = $empNew;
        }

        $column = new $columnTable();
        $columns = $column->Find(
            "enabled = ? and id in (".implode(",", $columnList).") order by colorder, id",
            array('Yes')
        );

        $cost = new $valueTable();
        $costs = $cost->Find("payroll = ?", array($req->payrollId));

        //Build value map
        $valueMap = array();
        foreach ($costs as $val) {
            if (!isset($valueMap[$val->employee])) {
                $valueMap[$val->employee] = array();
            }

            $valueMap[$val->employee][$val->payroll_item] = $val;
        }

        //Fill hours worked
        foreach ($employees as $e) {
            if ($payroll->status != "Completed") {
                foreach ($columns as $column) {
                    if (isset($valueMap[$e->id][$column->id]) && $column->editable == "Yes") {
                        $this->addToCalculationCache(
                            $column->id."-".$payroll->id."-".$e->id,
                            $valueMap[$e->id][$column->id]->amount
                        );
                        continue;
                    }
                    $item = new PayrollData();
                    $item->payroll = $req->payrollId;
                    $item->employee = $e->id;
                    $item->payroll_item = $column->id;
                    $item->amount = $this->calculatePayrollColumn($column, $payroll, $e->id, $e->payrollEmployeeId);
                    if ($item->amount == "") {
                        $item->amount =  $column->default_value;
                    }
                    $valueMap[$e->id][$column->id] = $item;
                }
            }
        }

        $values = array();
        foreach ($valueMap as $key => $val) {
            $values = array_merge($values, array_values($val));
        }

        if ($save == "1") {
            foreach ($values as $value) {
                if (empty($value->id)) {
                    $value->Save();
                }
            }
        }

        if ($payroll->status == 'Processing') {
            $payroll->status =  'Completed';
            $payroll->Save();
        }

        if ($payroll->status == 'Completed') {
            $newCols = array();
            foreach ($columns as $col) {
                $col->editable = 'No';
                $newCols[] = $col;
            }
            $columns = $newCols;
        }
        return new IceResponse(IceResponse::SUCCESS, array($employees,$columns,$values));
    }

    public function updateAllData($req)
    {

        $resp = $this->updateData($req);

        if ($resp->getStatus() == IceResponse::SUCCESS) {
            $payroll = new Payroll();
            $payroll->Load("id = ?", array($req->payrollId));
            $payroll->status = 'Processing';
            $ok = $payroll->Save();
            if (!$ok) {
                return new IceResponse(IceResponse::ERROR, $payroll->ErrorMsg());
            }
        }
        return $resp;
    }

    public function updateData($req)
    {
        $payroll = new Payroll();
        $payroll->Load("id = ?", array($req->payrollId));
        if ($payroll->status == 'Completed') {
            return new IceResponse(IceResponse::ERROR, true);
        }
        $valueTable = BaseService::getInstance()->getFullQualifiedModelClassName($req->valueTable);
        $payrollId = $req->payrollId;
        foreach ($req as $key => $val) {
            if (!is_array($val)) {
                continue;
            }
            $data = new $valueTable();
            $data->Load("payroll = ? and employee = ? and payroll_item = ?", array($payrollId,$val[1],$val[0]));
            if (empty($data->id)) {
                $data->payroll = $payrollId;
                $data->employee = $val[1];
                $data->payroll_item = $val[0];
            }
            $data->amount = $val[2];
            LogManager::getInstance()->info("Saving payroll data :".json_encode($data));
            $ok = $data->Save();
            if (!$ok) {
                LogManager::getInstance()->error("Error saving payroll data:".$data->ErrorMsg());
            }
        }

        return new IceResponse(IceResponse::SUCCESS, true);
    }

    public function deletePayrollGroup($req)
    {
        $employee = new PayrollEmployee();
        $report = new Payroll();
        $payrollGroup = new DeductionGroup();
        $payrollColumn = new PayrollColumn();
        $calcMethod =new Deduction();


        $payrollGroup->Load("id = ?", array($req->id));
        if (empty($payrollGroup->id)) {
            return new IceResponse(IceResponse::ERROR);
        }

        $this->baseService->checkSecureAccess('delete', $payrollGroup, 'DeductionGroup', $_POST);
        $this->baseService->checkSecureAccess('delete', $payrollColumn, 'payrollColumn', $_POST);
        $this->baseService->checkSecureAccess('delete', $calcMethod, 'Deduction', $_POST);

        $employee->Load("deduction_group = ?", $payrollGroup->id);
        $report->Load("deduction_group = ?", $payrollGroup->id);
        $payrollColumn->Load("deduction_group = ?", $payrollGroup->id);
        $calcMethod->Load("deduction_group = ?", $payrollGroup->id);

        if (!empty($employee->id)) {
            return new IceResponse(
                IceResponse::ERROR,
                "There are employees attached to this payroll group,
                 please re-assign employees to a different payroll group"
            );
        }

        if (!empty($report->id)) {
            return new IceResponse(
                IceResponse::ERROR,
                "There are payroll reports attached to this group, 
                please move the payroll reports to a different payroll group before deleting this group"
            );
        }

        BaseService::getInstance()->getDB()->Execute(
            'DELETE FROM PayrollColumns WHERE deduction_group= ? ',
            array($req->id)
        );
        BaseService::getInstance()->getDB()->Execute(
            'DELETE FROM Deductions WHERE deduction_group= ? ',
            array($req->id)
        );
        $ok = $payrollGroup->Delete();
        if (!$ok) {
            return new IceResponse(
                IceResponse::ERROR,
                "Error occurred while deleting payroll group"
            );
        }

        return new IceResponse(IceResponse::SUCCESS);
    }
}
