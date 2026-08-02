<?php
namespace Advance_reportsAdmin\Reports;

class EmployeeTimeTrackReport extends BaseReport
{
    protected $name = 'Employee Time Tracking Report';
    protected $description = 'This report lists employee working hours and attendance details for each day for a given period';
    protected $group = 'Time Management';
    protected $parameters = [
        [
            'name' => 'employee',
            'label' => 'Employee',
            'type' => 'select2',
            'allowNull' => false,
            'remoteSource' => ['Employee', 'id', 'first_name+last_name'],
            'required' => true,
        ],
        [
            'name' => 'date_start',
            'label' => 'Start Date',
            'type' => 'date',
        ],
        [
            'name' => 'date_end',
            'label' => 'End Date',
            'type' => 'date',
        ],
        [
            'name' => 'period',
            'label' => 'Period',
            'type' => 'select',
            'source' => [
                ['value' => 'Current Month', 'label' => 'Current Month'],
                ['value' => 'Last Month', 'label' => 'Last Month'],
                ['value' => 'Last Week', 'label' => 'Last Week'],
                ['value' => 'Last 2 Weeks', 'label' => 'Last 2 Weeks'],
            ],
        ],
    ];

    public function getReportData(array $params): array
    {
        // Determine date range based on period or explicit dates
        $dateRange = $this->getDateRange($params);

        $query = "SELECT
            DATE(at.in_time) as 'Date',
            (SELECT concat(first_name, ' ', last_name) from Employees where id = at.employee) as 'Employee',
            MIN(at.in_time) as 'First Punch In',
            MAX(at.out_time) as 'Last Punch Out',
            COUNT(*) as 'Punches',
            ROUND(SUM(TIMESTAMPDIFF(SECOND, at.in_time, IFNULL(at.out_time, NOW()))) / 3600, 2) as 'Total Hours'
        FROM Attendance at
        WHERE at.employee = ?
          AND DATE(at.in_time) >= ?
          AND DATE(at.in_time) <= ?
        GROUP BY DATE(at.in_time), at.employee
        ORDER BY DATE(at.in_time) DESC";

        $queryParams = [
            $params['employee'],
            $dateRange['start'],
            $dateRange['end'],
        ];

        return $this->executeQuery($query, $queryParams);
    }

    private function getDateRange(array $params): array
    {
        if (!empty($params['date_start']) && !empty($params['date_end'])) {
            return [
                'start' => $params['date_start'],
                'end' => $params['date_end'],
            ];
        }

        $period = $params['period'] ?? 'Current Month';

        switch ($period) {
            case 'Last Month':
                return [
                    'start' => date('Y-m-01', strtotime('first day of last month')),
                    'end' => date('Y-m-t', strtotime('last day of last month')),
                ];
            case 'Last Week':
                return [
                    'start' => date('Y-m-d', strtotime('-7 days')),
                    'end' => date('Y-m-d'),
                ];
            case 'Last 2 Weeks':
                return [
                    'start' => date('Y-m-d', strtotime('-14 days')),
                    'end' => date('Y-m-d'),
                ];
            case 'Current Month':
            default:
                return [
                    'start' => date('Y-m-01'),
                    'end' => date('Y-m-d'),
                ];
        }
    }
}
