<?php
namespace Advance_reportsUser\Reports;

class UserReportRegistry
{
    private static $instance = null;
    private $reports = [];

    private function __construct()
    {
        $this->registerReports();
    }

    public static function getInstance(): UserReportRegistry
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function registerReports(): void
    {
        // Leave Management Reports
        $this->reports['UserLeavesReport'] = new UserLeavesReport();

        // Time Management Reports
        $this->reports['UserTimeEntryReport'] = new UserTimeEntryReport();
        $this->reports['UserAttendanceReport'] = new UserAttendanceReport();
        $this->reports['UserTimeTrackReport'] = new UserTimeTrackReport();
        $this->reports['UserTimeSheetReport'] = new UserTimeSheetReport();
        $this->reports['UserOvertimeReport'] = new UserOvertimeReport();
        $this->reports['UserOvertimeSummaryReport'] = new UserOvertimeSummaryReport();
        $this->reports['UserClientProjectTimeReport'] = new UserClientProjectTimeReport();

        // Travel and Expense Management Reports
        $this->reports['UserTravelRequestReport'] = new UserTravelRequestReport();
        $this->reports['UserExpenseReport'] = new UserExpenseReport();
    }

    public function getReport(string $id): ?BaseUserReport
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
