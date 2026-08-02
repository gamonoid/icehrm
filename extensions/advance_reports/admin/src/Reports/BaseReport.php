<?php
namespace Advance_reportsAdmin\Reports;

use Classes\BaseService;
use Classes\FileService;
use Model\File;
use Model\ReportFile;
use Utils\LogManager;

abstract class BaseReport
{
    protected $name;
    protected $description;
    protected $group;
    protected $parameters = [];

    abstract public function getReportData(array $params): array;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getDefinition(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->name,
            'description' => $this->description,
            'group' => $this->group,
            'parameters' => $this->parameters,
        ];
    }

    public function getId(): string
    {
        $className = get_class($this);
        $parts = explode('\\', $className);
        return end($parts);
    }

    protected function executeQuery($query, $params = []): array
    {
        $db = BaseService::getInstance()->getDB();
        LogManager::getInstance()->debug("Report Query: " . $query);
        LogManager::getInstance()->debug("Report Params: " . json_encode($params));

        $rs = $db->Execute($query, $params);

        if (!empty($db->ErrorMsg())) {
            LogManager::getInstance()->error("Report Error: " . $db->ErrorMsg());
            return [];
        }

        $data = [];
        $headers = null;

        foreach ($rs as $row) {
            if ($headers === null) {
                $headers = array_keys($row);
                $data[] = $headers;
            }
            $data[] = array_values($row);
        }

        return $data;
    }

    protected function parseEmployeeList($employeeParam): array
    {
        if (empty($employeeParam)) {
            return [];
        }

        $list = json_decode($employeeParam, true);
        if (!is_array($list)) {
            return [];
        }

        // Employee ids are integers. Cast every value and drop anything that is not a
        // positive integer. This is a security control, not just cleanup: the returned
        // list is interpolated directly into `... IN (implode(',', $list))` in ~10 report
        // queries, so unsanitized values here are a SQL-injection vector.
        $ids = [];
        foreach ($list as $v) {
            if ($v === null || $v === 'NULL') {
                continue;
            }
            $id = filter_var($v, FILTER_VALIDATE_INT);
            if ($id !== false && $id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    public function generateCsv(array $data): array
    {
        $fileFirstPart = "Report_" . str_replace(" ", "_", $this->name) . "-" . date("Y-m-d_H-i-s");
        $fileName = $fileFirstPart . ".csv";
        $fileFullName = BaseService::getInstance()->getDataDirectory() . $fileName;

        $fp = fopen($fileFullName, 'w');
        foreach ($data as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        // Save file record
        $fileObj = new File();
        $fileObj->name = $fileFirstPart;
        $fileObj->filename = $fileName;
        $fileObj->file_group = "Report";
        $fileObj->Save();

        $reportFile = new ReportFile();
        $reportFile->name = $fileObj->filename;
        $reportFile->attachment = $fileObj->name;
        $reportFile->created = date("Y-m-d H:i:s");
        $reportFile->employee = BaseService::getInstance()->getCurrentProfileId();
        $reportFile->Save();

        return [
            'success' => true,
            'file' => $fileName,
            'url' => FileService::getInstance()->getLocalSecureUrl($fileName),
        ];
    }
}
