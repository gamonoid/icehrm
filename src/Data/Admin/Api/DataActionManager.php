<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 8:02 AM
 */

namespace Data\Admin\Api;

use Classes\FileService;
use Classes\IceResponse;
use Classes\SubActionManager;
use Data\Common\Model\DataImport;
use Data\Common\Model\DataImportFile;
use Utils\LogManager;

class DataActionManager extends SubActionManager
{

    public function processDataFile($req)
    {
        $id = $req->id;
        $dataFile = new DataImportFile();
        $dataFile->Load("id = ?", array($id));
        if (empty($dataFile->id)) {
            return new IceResponse(IceResponse::ERROR, null);
        }
        $url = FileService::getInstance()->getFileUrl($dataFile->file);

        if (strstr($url, CLIENT_BASE_URL) !== false) {
            $url = str_replace(CLIENT_BASE_URL, CLIENT_BASE_PATH, $url);
        }

        LogManager::getInstance()->info("File Path:".$url);

        $data = file_get_contents($url);

        $data = str_replace("\r", "\n", $data);
        $data = str_replace("\n\n", "\n", $data);

        $lines = str_getcsv($data, "\n");

        $headerProcessed = false;

        $counter = 0;

        $dataImport = new DataImport();
        $dataImport->Load("id =?", array($dataFile->data_import_definition));
        if (empty($dataImport->id)) {
            return new IceResponse(IceResponse::ERROR, null);
        }

        $processClass = '\\Data\Admin\Import\\'.$dataImport->dataType;
        $processObj = new $processClass();

        LogManager::getInstance()->info("Line Count:".count($lines));

        $res = array();

        foreach ($lines as $line) {
            $cells = str_getcsv($line, ",");
            if ($headerProcessed === false) {
                $processObj->setDataImportId($dataImport->id);
                $processObj->processHeader($cells);
                $headerProcessed = true;
            } else {
                $result = $processObj->processDataRow($counter, $cells);
                $res[] = array($cells,$result);
            }
            $counter++;
        }

        $dataFile->status = "Processed";
        $dataFile->details = json_encode($res, JSON_PRETTY_PRINT);
        $dataFile->Save();
        return new IceResponse(IceResponse::SUCCESS, $processObj->getRowObjects());
    }

    private function processHeader($dataImportId, $data)
    {
    }
}
