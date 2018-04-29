<?php
namespace Reports\Admin\Reports;

use Payroll\Common\Model\Deduction;
use Payroll\Common\Model\DeductionGroup;
use Payroll\Common\Model\Payroll;
use Payroll\Common\Model\PayrollColumn;
use Payroll\Common\Model\PayslipTemplate;
use Reports\Admin\Api\ReportBuilder;
use Reports\Admin\Api\ReportBuilderInterface;
use Salary\Common\Model\SalaryComponent;
use Salary\Common\Model\SalaryComponentType;
use Utils\LogManager;

class PayrollDataExport extends ReportBuilder implements ReportBuilderInterface
{

    public function getData($report, $request)
    {
        // Add Deduction Group
        if (empty($request['deduction_group']) || $request['deduction_group'] === "NULL") {
            return ['Calculation Group not found'];
        } else {
            $deductionGroup = new DeductionGroup();
            $deductionGroup->Load("id = ?", array($request['deduction_group']));
            if (empty($deductionGroup->id)) {
                return ['Calculation Group not found'];
            } else {
                $data = [
                    'name' => $deductionGroup->name,
                    'description' => $deductionGroup->description,
                ];
            }
        }

        // Add Deductions
        $deduction = new Deduction();
        $deductions = $deduction->Find("deduction_group = ?", array($deductionGroup->id));
        $data['deductions'] = [];
        foreach ($deductions as $deduction) {
            $data['deductions'][] = [
                'id' => $deduction->id,
                'name' => $deduction->name,
                'componentType' => $deduction->componentType,
                'component' => $deduction->component,
                'payrollColumn' => $deduction->payrollColumn,
                'rangeAmounts' => $deduction->rangeAmounts,
            ];
        }

        // Payroll Columns
        $col = new PayrollColumn();
        $columns = $col->Find("deduction_group = ?", array($deductionGroup->id));
        $data['columns'] = [];
        $salaryComponentIds = [];
        foreach ($columns as $col) {
            $data['columns'][] = [
                'id' => $col->id,
                'name' => $col->name,
                'calculation_hook' => $col->calculation_hook,
                'salary_components' => $col->salary_components, // ["1"]
                'deductions' => $col->deductions, // ["1"]
                'add_columns' => $col->add_columns, // ["1"]
                'sub_columns' => $col->sub_columns,
                'colorder' => $col->colorder,
                'editable' => $col->editable,
                'enabled' => $col->enabled,
                'default_value' => $col->default_value,
                'calculation_columns' => $col->calculation_columns, //[{"name":"O","column":"107","id":"calculation_columns_1"}]
                'calculation_function' => $col->calculation_function,
            ];

            if (!empty($col->salary_components)) {
                $ids = json_decode($col->salary_components, true);
                foreach ($ids as $id) {
                    $salaryComponentIds[$id] = $id;
                }
            }
        }

        // Get Salary Components
        $data['salaryComponents'] = [];
        $salaryComponentGroupIds = [];
        $salaryComponent = new SalaryComponent();
        $salaryComponents = $salaryComponent->Find(
            "id in (" . implode(',', array_keys($salaryComponentIds)) .")",
            array()
        );
        foreach ($salaryComponents as $salaryComponent) {
            $data['salaryComponents'][] = [
                'id' => $salaryComponent->id,
                'name' => $salaryComponent->name,
                'componentType' => $salaryComponent->componentType,
                'details' => $salaryComponent->details,
            ];

            $salaryComponentGroupIds[$salaryComponent->componentType] = $salaryComponent->componentType;
        }

        // Get Salary Component Types
        $data['salaryComponentTypes'] = [];
        $salaryComponentType = new SalaryComponentType();
        $salaryComponentTypes = $salaryComponentType->Find(
            "id in (" . implode(',', array_keys($salaryComponentGroupIds)) .")",
            array()
        );
        foreach ($salaryComponentTypes as $salaryComponentType) {
            $data['salaryComponentTypes'][] = [
                'id' => $salaryComponentType->id,
                'code' => $salaryComponentType->code,
                'name' => $salaryComponentType->name,
            ];
        }

        // Get Sample Payroll
        $payroll = new Payroll();
        $payroll->Load("id = ?", array($request['payroll']));

        $data['samplePayroll'] = [
            'name' => $payroll->name,
            'pay_period' => $payroll->pay_period,
            'columns' => $payroll->columns,
            'date_start' => $payroll->date_start,
            'date_end' => $payroll->date_end,
            'status' => $payroll->status,
        ];

        $payslipTemplate = new PayslipTemplate();
        $payslipTemplate->Load("id = ?", array($payroll->payslipTemplate));

        $data['payslipTemplate'] = [
            'name' => $payslipTemplate->name,
            'data' => $payslipTemplate->data,
            'status' => $payslipTemplate->status,
            'created' => $payslipTemplate->created,
            'updated' => $payslipTemplate->updated,
        ];

        return $data;
    }

    public function createReportFile($report, $data)
    {
        $fileFirstPart = "Report_".str_replace(" ", "_", $report->name)."-".date("Y-m-d_H-i-s");
        $fileName = $fileFirstPart.".txt";

        $fileFullName = CLIENT_BASE_PATH.'data/'.$fileName;
        $fp = fopen($fileFullName, 'w');

        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT));

        fclose($fp);
        return array($fileFirstPart, $fileName, $fileFullName);
    }
}
