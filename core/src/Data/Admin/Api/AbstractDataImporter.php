<?php
namespace Data\Admin\Api;

use Classes\BaseService;
use Classes\IceResponse;
use Data\Common\Model\DataImport;
use FieldNames\Common\Model\CustomField;
use Utils\LogManager;
use Utils\ScriptRunner;

abstract class AbstractDataImporter implements DataImporter
{

    protected $dataImport = null;

    protected $headerMapping = array();
    protected $primaryKeyColumn = 0;

    protected $rowObjects = array();
    protected $attachedObjects = array();
    protected $objectKeys;
    protected $customFields;
    protected $columnsCompeted = array();
    protected $relatedColumns = array();

    protected $status = IceResponse::SUCCESS;

    public function getResult()
    {
        return $this->rowObjects;
    }

    public function getHeaderMapping()
    {
        return $this->headerMapping;
    }

    public function processHeader($data)
    {
        $columns = $this->dataImport->columns;
        $headers = json_decode($columns);

        $counter = 0;
        $expectedColumnOrder = [];
        $actualColumnOrder = [];
        $headerValidationFailed = false;

        if (count($headers) != count($data)) {
            if ($headerValidationFailed) {
                return new IceResponse(
                    IceResponse::ERROR,
                    [
                        'Column count in the file do not match the header count'
                    ]
                );
            }
        }

        foreach ($headers as $testColumn) {
            $expectedColumnOrder[] = $testColumn->name;
            $actualColumnOrder[] = $data[$counter];
            if (trim($testColumn->name) !==  trim($data[$counter])) {
                $headerValidationFailed = true;
            }

            $counter++;
        }

        if ($headerValidationFailed) {
            return new IceResponse(
                IceResponse::ERROR,
                [
                    'expected' => $expectedColumnOrder,
                    'actual'=> $actualColumnOrder
                ]
            );
        }

        $counter = 0;
        foreach ($headers as $column) {
            $this->headerMapping[] = $column;
            if ($column->idField == "Yes") {
                $this->primaryKeyColumn = $counter;
            }
            $counter++;
        }

        $modelObject = $this->getModelObject();
        $obj = new $modelObject();
        $this->objectKeys = $obj->getObjectKeys();

        $this->updateCustomFields();
        return new IceResponse(IceResponse::SUCCESS);
    }

    public function setDataImportId($dataImportId)
    {
        $this->dataImport = new DataImport();
        $this->dataImport->Load("id =?", array($dataImportId));
    }

    public function updateCustomFields()
    {
        $customField = new CustomField();
        $customFields = $customField->Find("type = ?", array($this->getModelObjectName()));
        $this->customFields = array();
        foreach ($customFields as $cf) {
            $this->customFields[$cf->name] = $cf;
        }
    }

    public function markCellCompleted($row, $col)
    {
        if (!isset($this->columnsCompeted[$row])) {
            $this->columnsCompeted[$row] = array();
        }

        $this->columnsCompeted[$row][$col] = true;
    }

    public function isCellCompleted($row, $col)
    {
        if (!isset($this->columnsCompeted[$row])) {
            return false;
        } else {
            if (!isset($this->columnsCompeted[$row][$col])) {
                return false;
            }
            return true;
        }
    }

    public function processData($row, $column, $data, $allData)
    {

        LogManager::getInstance()->debug("processData:".json_encode($data));
        if ($this->isCellCompleted($row, $column)) {
            LogManager::getInstance()->debug("Already Competed");
            return new IceResponse(IceResponse::SUCCESS, "Already Competed");
        }

        $headerColumn = $this->headerMapping[$column];

        //Load row object if its empty
        if (!isset($this->rowObjects[$row])) {
            $modelObj = $this->getModelObject();
            $this->rowObjects[$row] = new $modelObj();
            if ($headerColumn->idField == "Yes") {
                LogManager::getInstance()->debug("[processData] Load : $headerColumn->name = $data");
                $this->rowObjects[$row]->Load("$headerColumn->name = ?", array($data));
            }
        }

        if ($headerColumn->type == "Normal") {
            $this->rowObjects[$row]->{$headerColumn->name} = $data === 'NULL' ? null : $data;
        } elseif ($headerColumn->type == "Reference") {
            $hcClass = BaseService::getInstance()->getFullQualifiedModelClassName($headerColumn->dependOn);
            $hcField = $headerColumn->dependOnField;
            /* @var \Model\BaseModel $hcObject */
            $hcObject = new $hcClass();
            $hcObject->Load("$hcField = ?", array($data));
            $this->rowObjects[$row]->{$headerColumn->name} = $hcObject->id;
        }

        $this->markCellCompleted($row, $column);
        return new IceResponse(IceResponse::SUCCESS, $this->rowObjects[$row]);
    }

    public function processDataRow($row, $data)
    {

        $result = array();

        LogManager::getInstance()->debug("processDataRow:".json_encode($data));
        $counter = 0;
        foreach ($data as $cell) {
            $this->processData($row, $counter, $cell, $data);
            $counter++;
        }
        LogManager::getInstance()->debug("Row Object :" . print_r($this->rowObjects[$row], true));
        $this->rowObjects[$row] = $this->fixBeforeSave($this->rowObjects[$row], $data);
        if (!$this->isDuplicate($this->rowObjects[$row])) {
            $ok = $this->rowObjects[$row]->Save();

            if (!$ok) {
                LogManager::getInstance()->error(
                    "Row Object Error Saving Employee :" . $this->rowObjects[$row]->ErrorMsg()
                );
                $result['Error'] = "Row Object Error Saving Employee :" . $this->rowObjects[$row]->ErrorMsg();
            } else {
                $result['MainId'] = $this->rowObjects[$row]->id;
                $class = $this->getModelObject();
                $ele = new $class();

                $result['CustomFields'] = array();
                $customFields = $ele->getCustomFields($this->rowObjects[$row]);
                foreach ($this->rowObjects[$row] as $k => $v) {
                    if (isset($customFields[$k])) {
                        BaseService::getInstance()->customFieldManager
                            ->addCustomField($this->getModelObjectName(), $this->rowObjects[$row]->id, $k, $v);
                        $result['CustomFields'][] = array($class, $this->rowObjects[$row]->id, $k, $v);
                    }
                }
            }
        } else {
            $result['Error'] = "Duplicate Object Found";
        }

        return $result;
    }

    abstract public function getModelObject();
    abstract public function getModelObjectName();
    public function setModelObjectName($name)
    {
    }
    public function isDuplicate($obj)
    {
        return false;
    }
    abstract public function fixBeforeSave($object, $data);


    public function process($data, $dataImportId)
    {
        $data = str_replace("\r", "\n", $data);
        $data = str_replace("\n\n", "\n", $data);

        $lines = str_getcsv($data, "\n");

        $headerProcessed = false;

        $counter = 0;

        LogManager::getInstance()->info("Line Count:".count($lines));

        $res = array();
        $dataImport = new DataImport();
        $dataImport->Load("id =?", array($dataImportId));
        $func = $dataImport->details;

        foreach ($lines as $line) {
            $cells = str_getcsv($line, ",");
            if ($headerProcessed === false) {
                $this->setDataImportId($dataImportId);
                $result = $this->processHeader($cells);
                if ($result->getStatus() === IceResponse::ERROR) {
                    $this->status = IceResponse::ERROR;
                    $this->rowObjects = $result->getData();
                    return $result->getData();
                }
                $headerProcessed = true;
            } else {
                $i = 1;
                $variableList = [];
                foreach ($cells as $value) {
                    $variableList["col$i"] = $value;
                    $i++;
                }
                if ($func) {
                    $scriptResult = ScriptRunner::executeJs($variableList, $func);
                    if (empty($scriptResult)) {
                        continue;
                    }
                    $cells = explode(',', $scriptResult);
                }
                $result = $this->processDataRow($counter, $cells);
                $res[] = array($cells,$result);
            }
            $counter++;
        }

        return $res;
    }

    public function getLastStatus()
    {
        return $this->status;
    }
}
