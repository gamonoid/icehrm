<?php
namespace Advance_reportsAdmin\Reports;

class ReportRegistry
{
    private static $instance = null;
    private $reports = [];

    private function __construct()
    {
        $this->registerReports();
    }

    public static function getInstance(): ReportRegistry
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function registerReports(): void
    {
        // Employee Information Reports
        $this->reports['ActiveEmployeeReport'] = new ActiveEmployeeReport();
        $this->reports['NewHiresEmployeeReport'] = new NewHiresEmployeeReport();
        $this->reports['TerminatedEmployeeReport'] = new TerminatedEmployeeReport();
        $this->reports['EmployeeDetailsReport'] = new EmployeeDetailsReport();

        // Time Management Reports
        $this->reports['EmployeeAttendanceReport'] = new EmployeeAttendanceReport();
        $this->reports['EmployeeTimeTrackReport'] = new EmployeeTimeTrackReport();
        $this->reports['EmployeeTimeEntryReport'] = new EmployeeTimeEntryReport();
        $this->reports['EmployeeTimeSheetReport'] = new EmployeeTimeSheetReport();
        $this->reports['OvertimeReport'] = new OvertimeReport();
        $this->reports['OvertimeSummaryReport'] = new OvertimeSummaryReport();
        $this->reports['OvertimeRequestReport'] = new OvertimeRequestReport();

        // Leave Management Reports
        $this->reports['EmployeeLeavesReport'] = new EmployeeLeavesReport();
        $this->reports['EmployeeLeaveEntitlementReport'] = new EmployeeLeaveEntitlementReport();

        // Travel and Expense Management Reports
        $this->reports['TravelRequestReport'] = new TravelRequestReport();
        $this->reports['ExpenseReport'] = new ExpenseReport();

        // Resources Reports
        $this->reports['CompanyAssetReport'] = new CompanyAssetReport();
    }

    public function getReport(string $id): ?BaseReport
    {
        return $this->reports[$id] ?? null;
    }

    public function getAllReports(): array
    {
        return $this->reports;
    }

    public function getReportsByGroup(): array
    {
        $grouped = [];
        foreach ($this->reports as $report) {
            $group = $report->getGroup();
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $report->getDefinition();
        }
        return $grouped;
    }

    public function getReportDefinitions(): array
    {
        $definitions = [];
        foreach ($this->reports as $report) {
            $definitions[] = $report->getDefinition();
        }
        return $definitions;
    }
}
