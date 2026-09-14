<?php
namespace Advance_reportsUser\Reports;

class UserTimeEntryReport extends BaseUserReport
{
    protected $name = 'Time Entry Report';
    protected $description = 'View your time entries by date range and project';
    protected $group = 'Time Management';
    protected $parameters = [
        [
            'name' => 'client',
            'label' => 'Select Client',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'Not Selected',
            'remoteSource' => ['Client', 'id', 'name'],
        ],
        [
            'name' => 'project',
            'label' => 'Or Project',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'All Projects',
            'remoteSource' => ['Project', 'id', 'name'],
        ],
        [
            'name' => 'date_start',
            'label' => 'Start Date',
            'type' => 'date',
            'required' => true,
        ],
        [
            'name' => 'date_end',
            'label' => 'End Date',
            'type' => 'date',
            'required' => true,
        ],
    ];

    public function getReportData(array $params): array
    {
        $employeeId = $this->getCurrentEmployeeId();

        $query = "SELECT
            te.date_start as 'Date',
            (SELECT name from Projects where id = te.project) as 'Project',
            (SELECT name from Clients where id = (SELECT client from Projects where id = te.project)) as 'Client',
            te.time_start as 'Start Time',
            te.time_end as 'End Time',
            te.details as 'Details'
        FROM EmployeeTimeEntry te
        WHERE te.employee = ?
        AND te.date_start >= ? AND te.date_start <= ?";

        $queryParams = [
            $employeeId,
            $params['date_start'],
            $params['date_end'],
        ];

        if (!empty($params['client']) && $params['client'] !== 'NULL') {
            $query .= " AND te.project IN (SELECT id FROM Projects WHERE client = ?)";
            $queryParams[] = $params['client'];
        }

        if (!empty($params['project']) && $params['project'] !== 'NULL') {
            $query .= " AND te.project = ?";
            $queryParams[] = $params['project'];
        }

        $query .= " ORDER BY te.date_start DESC, te.time_start DESC";

        return $this->executeQuery($query, $queryParams);
    }
}
