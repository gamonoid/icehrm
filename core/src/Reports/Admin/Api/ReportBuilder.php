<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:36 PM
 */

namespace Reports\Admin\Api;

use Classes\BaseService;
use Classes\S3FileSystem;
use Classes\SettingsManager;
use Model\File;
use Model\ReportFile;
use Utils\LogManager;

abstract class ReportBuilder
{

    protected function execute($report, $query, $parameters)
    {
        // $report->DB()->SetFetchMode(ADODB_FETCH_ASSOC);
        LogManager::getInstance()->debug("Query: ".$query);
        LogManager::getInstance()->debug("Parameters: ".json_encode($parameters));
        $rs = $report->DB()->Execute($query, $parameters);
        if (!$rs) {
            LogManager::getInstance()->info($report->DB()->ErrorMsg());
            return array("ERROR","Error generating report");
        }

        $reportNamesFilled = false;
        $columnNames = array();
        $reportData = array();
        foreach ($rs as $rowId => $row) {
            $reportData[] = array();
            if (!$reportNamesFilled) {
                $countIt = 0;
                foreach ($row as $name => $value) {
                    $countIt++;
                    $columnNames[$countIt] = $name;
                    $reportData[count($reportData)-1][] = $value;
                }
                $reportNamesFilled = true;
            } else {
                foreach ($row as $name => $value) {
                    $reportData[count($reportData)-1][] = $this->transformData($name, $value);
                }
            }
        }

        array_unshift($reportData, $columnNames);

        return $reportData;
    }

    public function transformData($name, $value)
    {
        return $value;
    }

    public function createReportFile($report, $data)
    {
        $fileFirstPart = "Report_".str_replace(" ", "_", $report->name)."-".date("Y-m-d_H-i-s");
        $fileName = $fileFirstPart.".csv";

        $fileFullName = BaseService::getInstance()->getDataDirectory().$fileName;
        $fp = fopen($fileFullName, 'w');

        foreach ($data as $fields) {
            fputcsv($fp, $fields);
        }

        fclose($fp);
        return array($fileFirstPart, $fileName, $fileFullName);
    }

    public function saveFile($fileFirstPart, $file, $fileFullName)
    {
        $uploadedToS3 = false;

        $uploadFilesToS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");
        $uploadFilesToS3Key = SettingsManager::getInstance()->getSetting("Files: Amazon S3 Key for File Upload");
        $uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting("Files: Amazon S3 Secret for File Upload");
        $s3Bucket = SettingsManager::getInstance()->getSetting("Files: S3 Bucket");
        $s3WebUrl = SettingsManager::getInstance()->getSetting("Files: S3 Web Url");

        if ($uploadFilesToS3.'' == '1' && !empty($uploadFilesToS3Key)
            && !empty($uploadFilesToS3Secret) && !empty($s3Bucket) && !empty($s3WebUrl)) {
            $uploadname = CLIENT_NAME."/".$file;
            $s3FileSys = new S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
            $res = $s3FileSys->putObject($s3Bucket, $uploadname, $fileFullName, 'authenticated-read');

            if (empty($res)) {
                return array("ERROR",$file);
            }

            unlink($fileFullName);
            $file_url = $s3WebUrl.$uploadname;
            $file_url = $s3FileSys->generateExpiringURL($file_url);
            $uploadedToS3 = true;
        }

        $fileObj = new File();
        $fileObj->name = $fileFirstPart;
        $fileObj->filename = $file;
        $fileObj->file_group = "Report";
        $ok = $fileObj->Save();

        if (!$ok) {
            LogManager::getInstance()->info($fileObj->ErrorMsg());
            return array("ERROR","Error generating report");
        }

        $reportFile = new ReportFile();
        $reportFile->name = $fileObj->filename;
        $reportFile->attachment = $fileObj->name;
        $reportFile->created = date("Y-m-d H:i:s");
        $reportFile->employee = BaseService::getInstance()->getCurrentProfileId();
        $ok = $reportFile->Save();

        if (!$ok) {
            LogManager::getInstance()->info($reportFile->ErrorMsg());
            return array("ERROR","Error generating report");
        }

        if ($uploadedToS3) {
            return array("SUCCESS",$file_url);
        } else {
            return array("SUCCESS",$file);
        }
    }
}
