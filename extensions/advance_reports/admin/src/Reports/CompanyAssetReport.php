<?php
namespace Advance_reportsAdmin\Reports;

class CompanyAssetReport extends BaseReport
{
    protected $name = 'Company Asset Report';
    protected $description = 'List company assets assigned to employees and departments';
    protected $group = 'Resources';
    protected $parameters = [
        [
            'name' => 'department',
            'label' => 'Department',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'All Departments',
            'remoteSource' => ['CompanyStructure', 'id', 'title'],
        ],
        [
            'name' => 'type',
            'label' => 'Asset Type',
            'type' => 'select2',
            'allowNull' => true,
            'nullLabel' => 'All Asset Types',
            'remoteSource' => ['AssetType', 'id', 'name'],
        ],
    ];

    public function getReportData(array $params): array
    {
        $query = "SELECT
            a.code as 'Asset Code',
            (SELECT name from AssetTypes where id = a.type) as 'Asset Type',
            (SELECT concat(first_name, ' ', last_name) from Employees where id = a.employee) as 'Assigned Employee',
            (SELECT title from CompanyStructures where id = a.department) as 'Department',
            a.description as 'Description'
        FROM CompanyAssets a
        WHERE 1=1";

        $queryParams = [];

        if (!empty($params['type']) && $params['type'] !== 'NULL') {
            $query .= " AND a.type = ?";
            $queryParams[] = $params['type'];
        }

        if (!empty($params['department']) && $params['department'] !== 'NULL') {
            $query .= " AND a.department = ?";
            $queryParams[] = $params['department'];
        }

        $query .= " ORDER BY a.type, a.code";

        return $this->executeQuery($query, $queryParams);
    }
}
