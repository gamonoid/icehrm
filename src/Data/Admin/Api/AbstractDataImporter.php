<?php
namespace Data\Admin\Api;

use Classes\BaseService;
use Classes\IceResponse;
use Data\Common\Model\DataImport;
use FieldNames\Common\Model\CustomField;
use Utils\LogManager;

abstract class AbstractDataImporter
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

    public function getRowObjects()
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
        foreach ($headers as $column) {
            $this->headerMapping[] = $column;
            if ($column->idField == "Yes") {
                $this->primaryKeyColumn = $counter;
            }
            //Update related columns
            if (($column->type == "Reference" || $column->type == "Attached") && $column->isKeyField == "Yes") {
                $this->relatedColumns[$counter] = array();
                for ($i = 0; $i< count($headers); $i++) {
                    $columnNew = $headers[$i];
                    if ($columnNew->id != $column->id &&
                        $columnNew->dependOn == $column->dependOn && $column->dependOn != "NULL") {
                        $this->relatedColumns[$counter][$i] = $columnNew;
                    }
                }
            }

            $counter++;
        }

        $modelObject = $this->getModelObject();
        $obj = new $modelObject();
        $this->objectKeys = $obj->getObjectKeys();

        $this->updateCustomFields();
    }

    public function setDataImportId($dataImportId)
    {
        $this->dataImport = new DataImport();
        $this->dataImport->Load("id =?", array($dataImportId));
    }

    public function updateCustomFields()
    {
        $customField = new CustomField();
        $customFields = $customField->Find("type = ?", array($this->getModelObject()));
        $this->customFields = array();
        foreach ($customFields as $cf) {
            $this->customFields[$cf->name] = $cf;
        }
    }

    public function addCustomField($column)
    {
        $customField = new CustomField();
        $customField->type = $this->getModelObject();
        $customField->name = $column->name;
        $customField->display = "Form";
        $customField->field_type = "text";
        $customField->field_label = $column->title;
        $customField->field_validation = "none";
        $customField->display_order = 0;
        $customField->Save();
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

        //Check for non existing column names
        if (!isset($this->objectKeys[$headerColumn->name])) {
            if (!isset($this->customFields[$headerColumn->name])) {
                $this->addCustomField($headerColumn);
                $this->updateCustomFields();
            }
        }

        if ($headerColumn->type == "Normal") {
            $this->rowObjects[$row]->{$headerColumn->name} = $data;
        } elseif ($headerColumn->type == "Reference" || $headerColumn->type == "Attached") {
            if ($headerColumn->isKeyField == "Yes") {
                $hcClass = BaseService::getInstance()->getFullQualifiedModelClassName($headerColumn->dependOn);
                $hcField = $headerColumn->dependOnField;
                /* @var \Model\BaseModel $hcObject */
                $hcObject = new $hcClass();
                if ($headerColumn->type == "Attached" && !empty($this->rowObjects[$row]->id)) {
                    $hcObject->Load("$hcField = ? and employee = ?", array($data,$this->rowObjects[$row]->id));
                } else {
                    $hcObject->Load("$hcField = ?", array($data));
                }

                $hcObject->{$hcField} = $data;
                foreach ($this->relatedColumns[$column] as $key => $val) {
                    $tempValName = $val->name;
                    if (strstr($val->name, "/")) {
                        $tempValName = explode("/", $val->name)[1];
                    }
                    $hcObject->{$tempValName} = $allData[$key];
                    $this->markCellCompleted($row, $key);
                }

                if ($headerColumn->type == "Reference") {
                    $hcObject->Save();
                } else {
                    if (!isset($this->attachedObjects[$row])) {
                        $this->attachedObjects[$row] = array();
                    }
                    $this->attachedObjects[$row][] = $hcObject;
                }
                $this->rowObjects[$row]->{$headerColumn->name} = $hcObject->id;
            }
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
                            ->addCustomField($class, $this->rowObjects[$row]->id, $k, $v);
                        $result['CustomFields'][] = array($class, $this->rowObjects[$row]->id, $k, $v);
                    }
                }

                if (!empty($this->attachedObjects[$row])) {
                    /* @var \Model\BaseModel $aObj */
                    foreach ($this->attachedObjects[$row] as $aObj) {
                        $aObj->employee = $this->rowObjects[$row]->id;

                        $aObj->Save();
                        $result['attachedObjects'][] = $aObj;
                    }
                }
            }
        } else {
            $result['Error'] = "Duplicate Object Found";
        }

        return $result;
    }

    abstract public function getModelObject();
    public function isDuplicate($obj)
    {
        return false;
    }
    abstract public function fixBeforeSave($object, $data);
}
