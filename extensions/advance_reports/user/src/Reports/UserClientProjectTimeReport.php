<?php
namespace Advance_reportsUser\Reports;

class UserClientProjectTimeReport extends BaseUserReport
{
    protected $name = 'Client Project Time Report';
    protected $description = 'View your time entries for projects under a given client';
    protected $group = 'Time Management';
    protected $parameters = [
        [
            'name' => 'client',
            'label' => 'Select Client',
            'type' => 'select2',
            'allowNull' => false,
            'remoteSource' => ['Client', 'id', 'name'],
            'required' => true,
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
            (SELECT name from Clients where id = p.client) as 'Client',
            p.name as 'Project',
            te.date_start as 'Date',
            te.time_start as 'Start Time',
            te.time_end as 'End Time',
            te.details as 'Details'
        FROM EmployeeTimeEntry te
        JOIN Projects p ON te.project = p.id
        WHERE te.employee = ?
        AND p.client = ?
        AND te.date_start >= ? AND te.date_start <= ?
        ORDER BY te.date_start DESC, p.name, te.time_start DESC";

        $queryParams = [
            $employeeId,
            $params['client'],
            $params['date_start'],
            $params['date_end'],
        ];

        return $this->executeQuery($query, $queryParams);
    }
}
