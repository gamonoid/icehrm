<?php
namespace Advance_reportsUser\Reports;

use Classes\BaseService;
use Classes\FileService;
use Model\File;
use Model\ReportFile;
use Utils\LogManager;

abstract class BaseUserReport
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

    protected function getCurrentEmployeeId(): int
    {
        return BaseService::getInstance()->getCurrentProfileId();
    }

    protected function executeQuery($query, $params = []): array
    {
        $db = BaseService::getInstance()->getDB();
        LogManager::getInstance()->debug("User Report Query: " . $query);
        LogManager::getInstance()->debug("User Report Params: " . json_encode($params));

        $rs = $db->Execute($query, $params);

        if (!empty($db->ErrorMsg())) {
            LogManager::getInstance()->error("User Report Error: " . $db->ErrorMsg());
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
