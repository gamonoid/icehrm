<?php
namespace Reports\Admin\Reports;

use Classes\BaseService;
use Reports\Admin\Api\ClassBasedReportBuilder;
use Reports\Admin\Api\ReportBuilderInterface;

class AssetUsageReport extends ClassBasedReportBuilder implements ReportBuilderInterface
{

    public function getData($report, $request)
    {
        $filters = [];

        if (!empty($request['department']) && $request['department'] !== "NULL") {
            $filters['department'] = $request['department'];
        }

        if (!empty($request['type']) && $request['type'] !== "NULL") {
            $filters['type'] = $request['type'];
        }


        $mapping = [
            "department" => ["CompanyStructure","id","title"],
            "employee" => ["Employee","id","first_name+last_name"],
            "type" => ["AssetType","id","name"],
        ];


        $reportColumns = [
            ['label' => 'Code', 'column' => 'code'],
            ['label' => 'Type', 'column' => 'type'],
            ['label' => 'Assigned Employee', 'column' => 'employee'],
            ['label' => 'Assigned Department', 'column' => 'department'],
            ['label' => 'Description', 'column' => 'description'],
        ];

        $customFieldsList = BaseService::getInstance()->getCustomFields('CompanyAsset');

        foreach ($customFieldsList as $customField) {
            $reportColumns[] = [
                'label' => $customField->field_label,
                'column' => $customField->name,
            ];
        }

        $entries = BaseService::getInstance()->get('CompanyAsset', null, $filters);
        $data = [];
        foreach ($entries as $item) {
            $item =  BaseService::getInstance()->enrichObjectMappings($mapping, $item);
            $item =  BaseService::getInstance()->enrichObjectCustomFields('CompanyAsset', $item);
            $data[] = $item;
        }

        $mappedColumns = array_keys($mapping);


        $reportData = [];
        $reportData[] = array_column($reportColumns, 'label');

        foreach ($data as $item) {
            $row = [];
            foreach ($reportColumns as $column) {
                if (in_array($column['column'], $mappedColumns)) {
                    $row[] = $item->{$column['column'].'_Name'};
                } else {
                    $row[] = $item->{$column['column']};
                }
            }
            $reportData[] = $row;
        }

        return $reportData;
    }
}
