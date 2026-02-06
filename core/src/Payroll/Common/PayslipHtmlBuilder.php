<?php

namespace Payroll\Common;

use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Employees\Common\Model\EmploymentStatus;
use FieldNames\Common\Model\FieldNameMapping;
use Jobs\Common\Model\JobTitle;
use Jobs\Common\Model\PayGrade;
use Metadata\Common\Model\Country;
use Metadata\Common\Model\CustomFieldValue;
use Metadata\Common\Model\Nationality;
use Metadata\Common\Model\Province;
use Payroll\Common\Model\Payroll;
use Payroll\Common\Model\PayrollColumn;
use Payroll\Common\Model\PayrollData;
use Salary\Common\Model\PayFrequency;
use Salary\Common\Model\PayrollEmployee;

class PayslipHtmlBuilder
{
    /** @var array|null Cached employee data */
    private $employeeData = null;

    /** @var array|null Cached payroll data */
    private $payrollData = null;

    /** @var array|null Cached payroll column values */
    private $payrollColumnValues = null;

    /** @var array|null Cached field name mappings from database */
    private $fieldMappings = null;

    /** @var int|null */
    private $payrollId = null;

    /** @var int|null */
    private $employeeId = null;

    /**
     * Build HTML preview from payslip template design JSON
     *
     * @param string|null $designJson The design JSON string
     * @param int|null $payrollId Optional payroll ID for real data preview
     * @param int|null $employeeId Optional employee ID (payroll employee ID) for real data preview
     * @return string The generated HTML
     */
    public function buildPreview(?string $designJson, ?int $payrollId = null, ?int $employeeId = null): string
    {
        if (empty($designJson)) {
            return '<div style="text-align: center; padding: 40px; color: #999;">No design available</div>';
        }

        $elements = json_decode($designJson, true);
        if (!is_array($elements)) {
            return '<div style="text-align: center; padding: 40px; color: #999;">Invalid design format</div>';
        }

        // Store IDs and load data if provided
        $this->payrollId = $payrollId;
        $this->employeeId = $employeeId;

        if ($payrollId) {
            $this->loadPayrollData($payrollId);
        }
        if ($employeeId) {
            $this->loadEmployeeData($employeeId);
            if ($payrollId) {
                $this->loadPayrollColumnValues($payrollId, $employeeId);
            }
        }

        $html = $this->getDocumentStart();

        foreach ($elements as $element) {
            $html .= $this->renderElement($element);
        }

        $html .= $this->getDocumentEnd();

        // Replace placeholders with actual data if we have it
        if ($this->hasRealData()) {
            $html = $this->replacePlaceholders($html);
        }

        return $html;
    }

    /**
     * Check if we have real data to replace placeholders
     */
    private function hasRealData(): bool
    {
        return $this->employeeData !== null || $this->payrollData !== null;
    }

    /**
     * Load employee data from PayrollEmployee ID
     */
    private function loadEmployeeData(int $payrollEmployeeId): void
    {
        $payrollEmployee = new PayrollEmployee();
        $payrollEmployee->Load("id = ?", [$payrollEmployeeId]);

        if (!$payrollEmployee->id) {
            return;
        }

        $employee = new Employee();
        $employee->Load("id = ?", [$payrollEmployee->employee]);

        if (!$employee->id) {
            return;
        }

        // Get country name
        $countryName = '';
        if ($employee->country) {
            $country = new Country();
            $country->Load("code = ?", [$employee->country]);
            $countryName = $country->name ?? '';
        }

        // Get province name
        $provinceName = '';
        if ($employee->province) {
            $province = new Province();
            $province->Load("id = ?", [$employee->province]);
            $provinceName = $province->name ?? '';
        }

        // Build full address
        $addressParts = array_filter([
            $employee->address1,
            $employee->address2,
            $employee->city,
            $provinceName,
            $employee->postal_code,
            $countryName,
        ]);
        $fullAddress = implode(', ', $addressParts);

        // Get department name
        $departmentName = '';
        if ($employee->department) {
            $dept = new CompanyStructure();
            $dept->Load("id = ?", [$employee->department]);
            $departmentName = $dept->title ?? '';
        }

        // Get job title
        $jobTitle = '';
        if ($employee->job_title) {
            $job = new JobTitle();
            $job->Load("id = ?", [$employee->job_title]);
            $jobTitle = $job->name ?? '';
        }

        // Get employment status
        $empStatus = '';
        if ($employee->employment_status) {
            $status = new EmploymentStatus();
            $status->Load("id = ?", [$employee->employment_status]);
            $empStatus = $status->name ?? '';
        }

        // Get pay grade
        $payGradeName = '';
        if ($employee->pay_grade) {
            $payGrade = new PayGrade();
            $payGrade->Load("id = ?", [$employee->pay_grade]);
            $payGradeName = $payGrade->name ?? '';
        }

        // Get nationality name
        $nationalityName = '';
        if ($employee->nationality) {
            $nationality = new Nationality();
            $nationality->Load("id = ?", [$employee->nationality]);
            $nationalityName = $nationality->name ?? '';
        }

        // Get supervisor name
        $supervisorName = '';
        if ($employee->supervisor) {
            $supervisor = new Employee();
            $supervisor->Load("id = ?", [$employee->supervisor]);
            if ($supervisor->id) {
                $supervisorName = trim($supervisor->first_name . ' ' . $supervisor->last_name);
            }
        }

        // Get indirect supervisors names
        $indirectSupervisorsNames = '';
        if ($employee->indirect_supervisors) {
            $indirectIds = explode(',', $employee->indirect_supervisors);
            $names = [];
            foreach ($indirectIds as $indirectId) {
                $indirectId = trim($indirectId);
                if ($indirectId) {
                    $indirectSupervisor = new Employee();
                    $indirectSupervisor->Load("id = ?", [$indirectId]);
                    if ($indirectSupervisor->id) {
                        $names[] = trim($indirectSupervisor->first_name . ' ' . $indirectSupervisor->last_name);
                    }
                }
            }
            $indirectSupervisorsNames = implode(', ', $names);
        }

        $this->employeeData = [
            // Basic Info
            'employee_name' => trim($employee->first_name . ' ' . $employee->last_name),
            'employee_id' => $employee->employee_id ?? '',
            'first_name' => $employee->first_name ?? '',
            'last_name' => $employee->last_name ?? '',
            'middle_name' => $employee->middle_name ?? '',
            'gender' => $employee->gender ?? '',
            'birthday' => $employee->birthday ?? '',
            'marital_status' => $employee->marital_status ?? '',
            'nationality' => $nationalityName,
            // Identification
            'nic_num' => $employee->nic_num ?? '',
            'ssn_num' => $employee->ssn_num ?? '',
            'tax_id' => $employee->tax_id ?? '',
            'other_id' => $employee->other_id ?? '',
            // Contact
            'work_email' => $employee->work_email ?? '',
            'mobile_phone' => $employee->mobile_phone ?? '',
            'work_phone' => $employee->work_station_id ?? '',
            // Address
            'address' => $fullAddress,
            'address1' => $employee->address1 ?? '',
            'address2' => $employee->address2 ?? '',
            'city' => $employee->city ?? '',
            'province' => $provinceName,
            'postal_code' => $employee->postal_code ?? '',
            'country' => $countryName,
            // Employment
            'department' => $departmentName,
            'job_title' => $jobTitle,
            'employment_status' => $empStatus,
            'pay_grade' => $payGradeName,
            'joined_date' => $employee->joined_date ?? '',
            'confirmation_date' => $employee->confirmation_date ?? '',
            'supervisor' => $supervisorName,
            'indirect_supervisors' => $indirectSupervisorsNames,
            // Other
            'health_insurance' => $employee->health_insurance ?? '',
            'timezone' => $employee->timezone ?? '',
        ];

        // Load custom field values
        $this->loadCustomFieldValues($employee->id);
    }

    /**
     * Load custom field values for an employee
     */
    private function loadCustomFieldValues(int $employeeId): void
    {
        $customFieldValue = new CustomFieldValue();
        $values = $customFieldValue->Find("object_id = ? AND type = ?", [$employeeId, 'Employee']);

        foreach ($values as $cfv) {
            $this->employeeData['custom_' . $cfv->field_id] = $cfv->value ?? '';
        }
    }

    /**
     * Load payroll data
     */
    private function loadPayrollData(int $payrollId): void
    {
        $payroll = new Payroll();
        $payroll->Load("id = ?", [$payrollId]);

        if (!$payroll->id) {
            return;
        }

        // Get pay period name
        $payPeriodName = '';
        if ($payroll->pay_period) {
            $payFreq = new PayFrequency();
            $payFreq->Load("id = ?", [$payroll->pay_period]);
            $payPeriodName = $payFreq->name ?? '';
        }

        $this->payrollData = [
            'payroll_name' => $payroll->name ?? '',
            'pay_period' => $payPeriodName,
            'date_start' => $payroll->date_start ?? '',
            'date_end' => $payroll->date_end ?? '',
        ];
    }

    /**
     * Load payroll column values for a specific employee
     */
    private function loadPayrollColumnValues(int $payrollId, int $payrollEmployeeId): void
    {
        $this->payrollColumnValues = [];

        // Get the actual employee ID from PayrollEmployee
        $payrollEmployee = new PayrollEmployee();
        $payrollEmployee->Load("id = ?", [$payrollEmployeeId]);

        if (!$payrollEmployee->id) {
            return;
        }

        $employeeId = $payrollEmployee->employee;

        // Load all payroll data for this employee in this payroll
        $payrollData = new PayrollData();
        $dataRows = $payrollData->Find("payroll = ? AND employee = ?", [$payrollId, $employeeId]);

        foreach ($dataRows as $row) {
            // payroll_item is the column ID
            $this->payrollColumnValues['column_' . $row->payroll_item] = $row->amount ?? '0.00';
        }
    }

    /**
     * Replace placeholders in the HTML with actual values
     */
    private function replacePlaceholders(string $html): string
    {
        // Replace {{field}} patterns with actual values
        $pattern = '/\{\{([^}]+)\}\}/';

        $result = preg_replace_callback($pattern, function ($matches) {
            $fieldKey = $matches[1];

            // Check payroll columns first (column_123)
            if (strpos($fieldKey, 'column_') === 0) {
                if ($this->payrollColumnValues !== null && isset($this->payrollColumnValues[$fieldKey])) {
                    $value = $this->payrollColumnValues[$fieldKey];
                    // Format as currency if numeric
                    if (is_numeric($value)) {
                        $value = number_format((float)$value, 2);
                    }
                    return '<span class="field-value" style="font-weight: 500;">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</span>';
                }
                // Return placeholder if no data
                return $matches[0];
            }

            // Check payroll fields
            if ($this->payrollData !== null && isset($this->payrollData[$fieldKey])) {
                $value = $this->payrollData[$fieldKey];
                return '<span class="field-value" style="font-weight: 500;">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</span>';
            }

            // Check employee fields
            if ($this->employeeData !== null && isset($this->employeeData[$fieldKey])) {
                $value = $this->employeeData[$fieldKey];
                return '<span class="field-value" style="font-weight: 500;">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</span>';
            }

            // Return the original placeholder if no data
            return $matches[0];
        }, $html);

        return $result;
    }

    /**
     * Get document start with styles
     */
    private function getDocumentStart(): string
    {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        td, th {
            border: 1px solid #d9d9d9;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #fafafa;
            font-weight: bold;
        }
        hr {
            border: none;
            border-top: 1px solid #d9d9d9;
            margin: 15px 0;
        }
        ul, ol {
            margin: 10px 0;
            padding-left: 25px;
        }
        li {
            margin: 5px 0;
        }
        blockquote {
            border-left: 3px solid #d9d9d9;
            padding-left: 12px;
            color: #666;
            margin: 10px 0;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow: auto;
            font-family: monospace;
        }
        code {
            background-color: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        img {
            max-width: 100%;
            height: auto;
        }
        a {
            color: #1677ff;
            text-decoration: underline;
        }
        .container {
            padding: 10px;
            border: 1px dashed #d9d9d9;
            border-radius: 4px;
            margin: 10px 0;
        }
        .employee-field {
            display: inline-block;
            margin: 2px 0;
        }
        .employee-field .field-label {
            font-weight: 500;
            color: #666;
        }
        .employee-field .field-value {
            font-weight: 500;
        }
        .payroll-field {
            display: inline-block;
            margin: 2px 0;
        }
        .payroll-field .field-label {
            font-weight: 500;
            color: #666;
        }
        .payroll-field .field-value {
            font-weight: 500;
        }
        .payroll-column {
            display: inline-block;
            margin: 2px 0;
        }
        .payroll-column .field-label {
            font-weight: 500;
            color: #666;
        }
        .payroll-column .field-value {
            font-weight: 500;
        }
    </style>
</head>
<body>
';
    }

    /**
     * Get document end
     */
    private function getDocumentEnd(): string
    {
        return '
</body>
</html>';
    }

    /**
     * Map of payroll field values to display names
     * Fields from Payroll entity: name, pay_period, date_start, date_end
     */
    private static $payrollFieldLabels = [
        'payroll_name' => 'Payroll Name',
        'pay_period' => 'Pay Period',
        'date_start' => 'Start Date',
        'date_end' => 'End Date',
    ];

    /**
     * Map of employee field values to display names
     */
    private static $employeeFieldLabels = [
        // Basic Info
        'employee_name' => 'Employee Name',
        'employee_id' => 'Employee ID',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'middle_name' => 'Middle Name',
        'gender' => 'Gender',
        'birthday' => 'Birthday',
        'marital_status' => 'Marital Status',
        'nationality' => 'Nationality',
        // Identification
        'nic_num' => 'NIC Number',
        'ssn_num' => 'SSN',
        'tax_id' => 'Tax ID',
        'other_id' => 'Other ID',
        // Contact
        'work_email' => 'Work Email',
        'mobile_phone' => 'Mobile Phone',
        'work_phone' => 'Work Phone',
        // Address
        'address' => 'Full Address',
        'address1' => 'Address Line 1',
        'address2' => 'Address Line 2',
        'city' => 'City',
        'province' => 'State/Province',
        'postal_code' => 'Postal Code',
        'country' => 'Country',
        // Employment
        'department' => 'Department',
        'job_title' => 'Job Title',
        'employment_status' => 'Employment Status',
        'pay_grade' => 'Pay Grade',
        'joined_date' => 'Joined Date',
        'confirmation_date' => 'Confirmation Date',
        'supervisor' => 'Supervisor',
        'indirect_supervisors' => 'Indirect Supervisors',
        // Other
        'health_insurance' => 'Health Insurance',
        'timezone' => 'Timezone',
    ];

    /**
     * Load field name mappings from database
     */
    private function loadFieldMappings(): void
    {
        if ($this->fieldMappings !== null) {
            return;
        }

        $this->fieldMappings = [];
        $fieldMapping = new FieldNameMapping();
        $items = $fieldMapping->Find("type = ?", ['Employee']);

        foreach ($items as $item) {
            // Use textMapped if available, otherwise fall back to textOrig
            $label = $item->textMapped ?? $item->textOrig ?? $item->name;
            $this->fieldMappings[$item->name] = $label;
        }
    }

    /**
     * Get employee field label for display
     * Uses FieldNameMappings from database if available, otherwise falls back to static labels
     */
    private function getFieldLabel(string $fieldValue): string
    {
        if (strpos($fieldValue, 'custom_') === 0) {
            return 'Custom Field';
        }

        // Load field mappings from database if not already loaded
        $this->loadFieldMappings();

        // Use database mapping if available, otherwise use static fallback
        if (isset($this->fieldMappings[$fieldValue])) {
            return $this->fieldMappings[$fieldValue];
        }

        return self::$employeeFieldLabels[$fieldValue] ?? $fieldValue;
    }

    /**
     * Get payroll field label for display
     */
    private function getPayrollFieldLabel(string $fieldValue): string
    {
        return self::$payrollFieldLabels[$fieldValue] ?? $fieldValue;
    }

    /**
     * Check if a field value is a payroll field
     */
    private function isPayrollField(string $fieldValue): bool
    {
        return isset(self::$payrollFieldLabels[$fieldValue]);
    }

    /**
     * Render a single element
     */
    private function renderElement(array $element): string
    {
        $type = $element['type'] ?? '';
        $content = htmlspecialchars($element['content'] ?? '', ENT_QUOTES, 'UTF-8');
        $styles = $this->buildStyleString($element['styles'] ?? []);

        switch ($type) {
            case 'employee-field':
                return $this->renderEmployeeField($element);

            case 'payroll-field':
                return $this->renderPayrollField($element);

            case 'payroll-column':
                return $this->renderPayrollColumn($element);

            case 'h1':
                return "<h1 style=\"{$styles}\">{$content}</h1>\n";

            case 'h2':
                return "<h2 style=\"{$styles}\">{$content}</h2>\n";

            case 'h3':
                return "<h3 style=\"{$styles}\">{$content}</h3>\n";

            case 'h4':
                return "<h4 style=\"{$styles}\">{$content}</h4>\n";

            case 'h5':
                return "<h5 style=\"{$styles}\">{$content}</h5>\n";

            case 'h6':
                return "<h6 style=\"{$styles}\">{$content}</h6>\n";

            case 'p':
                return "<p style=\"{$styles}\">{$content}</p>\n";

            case 'span':
                return "<span style=\"{$styles}\">{$content}</span>\n";

            case 'strong':
                return "<strong style=\"{$styles}\">{$content}</strong>\n";

            case 'em':
                return "<em style=\"{$styles}\">{$content}</em>\n";

            case 'blockquote':
                return "<blockquote style=\"{$styles}\">{$content}</blockquote>\n";

            case 'pre':
                return "<pre style=\"{$styles}\">{$content}</pre>\n";

            case 'code':
                return "<code style=\"{$styles}\">{$content}</code>\n";

            case 'div':
                return "<div class=\"container\" style=\"{$styles}\">{$content}</div>\n";

            case 'a':
                $href = htmlspecialchars($element['link']['href'] ?? '#', ENT_QUOTES, 'UTF-8');
                $target = $element['link']['target'] ?? '_blank';
                return "<a href=\"{$href}\" target=\"{$target}\" style=\"{$styles}\">{$content}</a>\n";

            case 'img':
                return $this->renderImage($element);

            case 'hr':
                return "<hr>\n";

            case 'br':
                return "<br>\n";

            case 'ul':
                return $this->renderList($element, 'ul');

            case 'ol':
                return $this->renderList($element, 'ol');

            case 'table':
                return $this->renderTable($element);

            default:
                return '';
        }
    }

    /**
     * Build inline style string from styles array
     */
    private function buildStyleString(array $styles): string
    {
        $styleStr = '';

        if (!empty($styles['textAlign'])) {
            $styleStr .= "text-align: {$styles['textAlign']}; ";
        }
        if (!empty($styles['color'])) {
            $styleStr .= "color: {$styles['color']}; ";
        }
        if (!empty($styles['backgroundColor'])) {
            $styleStr .= "background-color: {$styles['backgroundColor']}; ";
        }
        if (!empty($styles['fontSize'])) {
            $styleStr .= "font-size: {$styles['fontSize']}px; ";
        }
        if (!empty($styles['fontWeight'])) {
            $styleStr .= "font-weight: {$styles['fontWeight']}; ";
        }
        if (!empty($styles['padding'])) {
            $styleStr .= "padding: {$styles['padding']}px; ";
        }
        if (!empty($styles['margin'])) {
            $styleStr .= "margin: {$styles['margin']}px; ";
        }
        if (!empty($styles['border'])) {
            $styleStr .= "border: {$styles['border']}; ";
        }

        return trim($styleStr);
    }

    /**
     * Render image element
     */
    private function renderImage(array $element): string
    {
        $image = $element['image'] ?? [];
        $src = htmlspecialchars($image['src'] ?? '', ENT_QUOTES, 'UTF-8');
        $alt = htmlspecialchars($image['alt'] ?? 'Image', ENT_QUOTES, 'UTF-8');

        if (empty($src)) {
            return '<div style="width: 200px; height: 100px; background: #f0f0f0; border: 2px dashed #d9d9d9; display: flex; align-items: center; justify-content: center; color: #999;">No image</div>';
        }

        $style = '';
        if (!empty($image['width'])) {
            $style .= "width: {$image['width']}px; ";
        }
        if (!empty($image['height'])) {
            $style .= "height: {$image['height']}px; ";
        }

        return "<img src=\"{$src}\" alt=\"{$alt}\" style=\"{$style}\">\n";
    }

    /**
     * Render list element (ul/ol)
     */
    private function renderList(array $element, string $tag): string
    {
        $items = $element['list']['items'] ?? [];
        $styles = $this->buildStyleString($element['styles'] ?? []);

        $html = "<{$tag} style=\"{$styles}\">\n";
        foreach ($items as $item) {
            $itemContent = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
            $html .= "    <li>{$itemContent}</li>\n";
        }
        $html .= "</{$tag}>\n";

        return $html;
    }

    /**
     * Render table element
     */
    private function renderTable(array $element): string
    {
        $table = $element['table'] ?? [];
        $cells = $table['cells'] ?? [];
        $hasHeader = $table['hasHeader'] ?? true;

        if (empty($cells)) {
            return '';
        }

        $html = "<table>\n";

        foreach ($cells as $rowIndex => $row) {
            $html .= "    <tr>\n";
            foreach ($row as $cell) {
                $cellContent = $this->renderCellContent($cell);
                if ($hasHeader && $rowIndex === 0) {
                    $html .= "        <th>{$cellContent}</th>\n";
                } else {
                    $html .= "        <td>{$cellContent}</td>\n";
                }
            }
            $html .= "    </tr>\n";
        }

        $html .= "</table>\n";

        return $html;
    }

    /**
     * Render cell content, processing field placeholders
     */
    private function renderCellContent(string $content): string
    {
        // First escape the content
        $escaped = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        // Then replace {{field}} placeholders with styled spans
        $pattern = '/\{\{([^}]+)\}\}/';
        $result = preg_replace_callback($pattern, function ($matches) {
            $fieldValue = $matches[1];

            // Check if it's a payroll column (green styling)
            if (strpos($fieldValue, 'column_') === 0) {
                return '<span class="field-value" style="background-color: #f6ffed; padding: 2px 8px; border-radius: 4px; color: #52c41a;">{{' . htmlspecialchars($fieldValue, ENT_QUOTES, 'UTF-8') . '}}</span>';
            }

            // Check if it's a payroll field (orange styling)
            if ($this->isPayrollField($fieldValue)) {
                return '<span class="field-value" style="background-color: #fff7e6; padding: 2px 8px; border-radius: 4px; color: #fa8c16;">{{' . htmlspecialchars($fieldValue, ENT_QUOTES, 'UTF-8') . '}}</span>';
            }

            // Default: employee field (blue styling)
            return '<span class="field-value" style="background-color: #e6f4ff; padding: 2px 8px; border-radius: 4px; color: #1677ff;">{{' . htmlspecialchars($fieldValue, ENT_QUOTES, 'UTF-8') . '}}</span>';
        }, $escaped);

        return $result;
    }

    /**
     * Render employee field element
     */
    private function renderEmployeeField(array $element): string
    {
        $employeeField = $element['employeeField'] ?? [];
        $field = $employeeField['field'] ?? 'employee_name';
        $label = $employeeField['label'] ?? '';
        $showLabel = $employeeField['showLabel'] ?? false;
        $styles = $this->buildStyleString($element['styles'] ?? []);

        $fieldLabel = $this->getFieldLabel($field);
        $placeholder = "{{" . $field . "}}";

        $html = "<div class=\"employee-field\" style=\"{$styles}\">";

        if ($showLabel && !empty($label)) {
            $labelText = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<span class=\"field-label\">{$labelText}: </span>";
        } elseif ($showLabel) {
            $html .= "<span class=\"field-label\">{$fieldLabel}: </span>";
        }

        $html .= "<span class=\"field-value\" style=\"background-color: #e6f4ff; padding: 2px 8px; border-radius: 4px; color: #1677ff;\">{$placeholder}</span>";
        $html .= "</div>\n";

        return $html;
    }

    /**
     * Render payroll field element
     */
    private function renderPayrollField(array $element): string
    {
        $payrollField = $element['payrollField'] ?? [];
        $field = $payrollField['field'] ?? 'net_salary';
        $label = $payrollField['label'] ?? '';
        $showLabel = $payrollField['showLabel'] ?? false;
        $styles = $this->buildStyleString($element['styles'] ?? []);

        $fieldLabel = $this->getPayrollFieldLabel($field);
        $placeholder = "{{" . $field . "}}";

        $html = "<div class=\"payroll-field\" style=\"{$styles}\">";

        if ($showLabel && !empty($label)) {
            $labelText = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<span class=\"field-label\">{$labelText}: </span>";
        } elseif ($showLabel) {
            $html .= "<span class=\"field-label\">{$fieldLabel}: </span>";
        }

        $html .= "<span class=\"field-value\" style=\"background-color: #fff7e6; padding: 2px 8px; border-radius: 4px; color: #fa8c16;\">{$placeholder}</span>";
        $html .= "</div>\n";

        return $html;
    }

    /**
     * Render payroll column element
     */
    private function renderPayrollColumn(array $element): string
    {
        $payrollColumn = $element['payrollColumn'] ?? [];
        $columnId = $payrollColumn['columnId'] ?? null;
        $columnName = $payrollColumn['columnName'] ?? 'Payroll Column';
        $label = $payrollColumn['label'] ?? '';
        $showLabel = $payrollColumn['showLabel'] ?? false;
        $styles = $this->buildStyleString($element['styles'] ?? []);

        $placeholder = "{{column_" . $columnId . "}}";

        $html = "<div class=\"payroll-column\" style=\"{$styles}\">";

        if ($showLabel && !empty($label)) {
            $labelText = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $html .= "<span class=\"field-label\">{$labelText}: </span>";
        } elseif ($showLabel) {
            $html .= "<span class=\"field-label\">" . htmlspecialchars($columnName, ENT_QUOTES, 'UTF-8') . ": </span>";
        }

        $html .= "<span class=\"field-value\" style=\"background-color: #f6ffed; padding: 2px 8px; border-radius: 4px; color: #52c41a;\">{$placeholder}</span>";
        $html .= "</div>\n";

        return $html;
    }
}
