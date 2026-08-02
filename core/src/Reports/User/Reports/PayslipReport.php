<?php
namespace Reports\User\Reports;

use Classes\BaseService;
use Payroll\Common\Model\Payroll;
use Payroll\Common\Model\PayrollColumn;
use Payroll\Common\Model\PayrollData;
use Payroll\Common\Model\PayslipTemplate;
use Reports\Admin\Api\PDFReportBuilder;
use Reports\Admin\Api\PDFReportBuilderInterface;

class PayslipReport extends PDFReportBuilder implements PDFReportBuilderInterface
{
    protected $employee;

    /**
     * @param mixed $employee
     */
    public function setEmployee($employee)
    {
        $this->employee = $employee;
    }

    public function getData($report, $request)
    {
        $data = $this->getDefaultData();

        if ($this->employee === null) {
            $this->employee = BaseService::getInstance()->getElement(
                'Employee',
                BaseService::getInstance()->getCurrentProfileId(),
                null,
                true
            );
        }

        $data['fields'] = array();

        $payroll = new Payroll();
        $payroll->Load("id = ?", array($request['payroll']));

        if (empty($payroll->payslipTemplate)) {
            return null;
        }

        $payslipTemplate = new PayslipTemplate();
        $payslipTemplate->Load("id = ?", array($payroll->payslipTemplate));

        if (empty($payslipTemplate->id)) {
            return null;
        }

        $fields = json_decode($payslipTemplate->data, true);

        foreach ($fields as $field) {
            if ($field['type'] == 'Payroll Column') {
                $col = new PayrollColumn();
                $col->Load("id = ?", [$field['payrollColumn']]);
                if (empty($col->id)) {
                    continue;
                }
                $payrollData = new PayrollData();
                $payrollData->Load(
                    "payroll = ? and payroll_item = ? and employee = ?",
                    [
                        $request['payroll'],
                        $col->id,
                        $this->employee->id,
                    ]
                );

                if (empty($payrollData->id)) {
                    continue;
                }

                $field['value'] = $payrollData->amount;

                if (empty($field['label'])) {
                    $field['label'] = $col->name;
                }
            }

            if ($field['status'] == 'Show') {
                $data['fields'][] = $field;
            }
        }


        $data['employeeName'] = $this->employee->first_name.' '.$this->employee->last_name;
        $data['payroll'] = $payroll;
        return $data;
    }

    public function getTemplate()
    {
        return "payslip.html";
    }

    /**
     * Render the single payslip via its Twig template (the payslip design), then
     * to PDF natively with mPDF — instead of the inherited PDFReportBuilder path
     * that shells out to wkhtmltopdf. The template layout/design is preserved.
     */
    public function createReportFile($report, $data)
    {
        $this->initTemplateEngine($report);
        $template = $this->twig->loadTemplate($this->getTemplate());
        $html = $template->render($data);

        $fileFirstPart = "Report_" . str_replace(" ", "_", $report->name) . "-" . date("Y-m-d_H-i-s");
        $fileName = $fileFirstPart . ".pdf";
        $fileFullName = BaseService::getInstance()->getDataDirectory() . $fileName;
        \Classes\Pdf\HtmlPdfRenderer::toFile($html, $fileFullName);

        return array($fileFirstPart, $fileName, $fileFullName);
    }
}
