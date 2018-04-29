<?php
/**
 *The base class for module manager classes. ModuleManager classes which extend this
 * class provide core backend functionality
 *to each module such as defining models, error handliing and other configuration details
 *@class AbstractModuleManager
 */
namespace Classes;

use Utils\LogManager;

abstract class AbstractModuleManager
{

    private $fileFieldMappings = array();
    private $userClasses = array();
    private $errorMappings = array();
    private $modelClasses = array();

    private $modulePath = null;
    private $moduleObject = null;
    private $moduleType = null;
    private $actionManager = null;

    /**
     * Override this method in module manager class to define user classes.
     * A user class is a class that is mapped to a table having a field named profile.
     * The profile field is mapped to the id of a Profile element.
     * When a user is saving this type of an object in db, profile field will be set to the id of the
     * Profile of currently logged in or switched user.
     * When a user is retriving this type of records, only the records having profile field set to c
     * urrently logged in users profile id will be released.
     * @method initializeUserClasses
     * @example
        public function initializeUserClasses(){
            $this->addUserClass("EmployeeDocument");
        }
     *
     */
    abstract public function initializeUserClasses();

    /**
     * Override this method in module manager class to define file field mappings.
     * If you have a table field that stores a name of a file which need to be
     * deleted from the disk when the record is deleted a file field mapping should be added.
     * @method initializeFieldMappings
     * @example
         public function initializeFieldMappings(){
            $this->addFileFieldMapping('EmployeeDocument', 'attachment', 'name');
         }
     */
    abstract public function initializeFieldMappings();

    /**
     * Override this method in module manager class to define DB error mappings. Some actions to your
     * model classes trigger database errors.
     * These errors need to be translated to user friendly texts using DB error mappings
     * @method initializeDatabaseErrorMappings
     * @example
        public function initializeDatabaseErrorMappings(){
            $this->addDatabaseErrorMapping('CONSTRAINT `Fk_User_Employee` FOREIGN KEY',"Can not delete Employee,
     * please delete the User for this employee first.");
            $this->addDatabaseErrorMapping("Duplicate entry|for key 'employee'","A duplicate entry found");
        }
     */
    abstract public function initializeDatabaseErrorMappings();

    /**
     * Override this method in module manager class to add model classes to this module.
     * All the model classes defind for the module should be added here
     * @method setupModuleClassDefinitions
     * @example
        public function setupModuleClassDefinitions(){
            $this->addModelClass('Employee');
            $this->addModelClass('EmploymentStatus');
        }
     */
    abstract public function setupModuleClassDefinitions();

    public function initCalculationHooks()
    {
    }

    public function initQuickAccessMenu()
    {
    }

    /**
     * @return null
     */
    public function getInitializer()
    {
        return null;
    }

    public function setModuleObject($obj)
    {
        $this->moduleObject = $obj;
    }

    public function getModuleObject()
    {
        return $this->moduleObject;
    }

    /**
     * @return null
     */
    public function getActionManager()
    {
        return $this->actionManager;
    }

    /**
     * @param null $actionManager
     */
    public function setActionManager($actionManager)
    {
        $this->actionManager = $actionManager;
    }

    public function setModuleType($type)
    {
        $this->moduleType = $type;
    }

    public function getModuleType()
    {
        return $this->moduleType;
    }

    public function getModulePath()
    {
        /*
        $subClass = get_called_class();
        $reflector = new \ReflectionClass($subClass);
        $fn = $reflector->getFileName();
        $this->modulePath = realpath(dirname($fn)."/..");
        LogManager::getInstance()->info("Module Path: [$subClass | $fn]".$this->modulePath);
        */
        return $this->modulePath;
    }

    public function setModulePath($modulePath)
    {
        $this->modulePath = $modulePath;
    }

    public function getDashboardItemData()
    {
        return array();
    }

    public function getDashboardItem()
    {
        $this->getModulePath();
        if (!file_exists($this->modulePath."/dashboard.html")) {
            //LogManager::getInstance()->error("Dashboard file not found :".$this->modulePath."/dashboard.html");
            return null;
        }
        $dashboardItem = file_get_contents($this->modulePath."/dashboard.html");
        if (empty($dashboardItem)) {
            //LogManager::getInstance()->error("Dashboard file is empty :".$this->modulePath."/dashboard.html");
            return null;
        }

        $data = $this->getDashboardItemData();
        $data['moduleLink'] = $this->getModuleLink();
        LogManager::getInstance()->info("Module Link:".$data['moduleLink']);
        foreach ($data as $k => $v) {
            $dashboardItem = str_replace("#_".$k."_#", $v, $dashboardItem);
        }

        return $dashboardItem;
    }

    public function getDashboardItemIndex()
    {
        $metaData = json_decode(file_get_contents($this->modulePath."/meta.json"), true);
        if (!isset($metaData['dashboardPosition'])) {
            return 100;
        } else {
            return $metaData['dashboardPosition'];
        }
    }

    private function getModuleLink()
    {

        $metaData = json_decode(file_get_contents($this->modulePath."/meta.json"), true);

        $mod = basename($this->modulePath);
        $group = basename(realpath($this->modulePath."/.."));

        //?g=admin&n=candidates&m=admin_Recruitment

        return CLIENT_BASE_URL."?g=".$group."&n=".$mod."&m=".$group."_".str_replace(" ", "_", $metaData['label']);
    }

    public function setupRestEndPoints()
    {
    }

    public function setupFileFieldMappings(&$fileFields)
    {
        foreach ($this->fileFieldMappings as $mapping) {
            if (empty($fileFields[$mapping[0]])) {
                $fileFields[$mapping[0]] = array();
            }

            $fileFields[$mapping[0]][$mapping[1]] = $mapping[2];
        }
    }

    public function setupUserClasses(&$userTables)
    {
        foreach ($this->userClasses as $className) {
            if (!in_array($className, $userTables)) {
                $userTables[] = $className;
            }
        }
    }

    public function setupErrorMappings(&$mysqlErrors)
    {
        foreach ($this->errorMappings as $name => $desc) {
            $mysqlErrors[$name] = $desc;
        }
    }

    public function getModelClasses()
    {
        return $this->modelClasses;
    }

    protected function addFileFieldMapping($className, $fieldName, $fileTableFieldName)
    {
        $this->fileFieldMappings[] = array($className, $fieldName, $fileTableFieldName);
    }

    protected function addUserClass($className)
    {
        $this->userClasses[] = $className;
    }

    protected function addDatabaseErrorMapping($error, $description)
    {
        $this->errorMappings[$error] = $description;
    }

    protected function addModelClass($className)
    {
        $this->modelClasses[] = $className;
        BaseService::getInstance()->addModelClass($className, $this->moduleObject['model_namespace']."\\".$className);
    }

    protected function addHistoryGeneric($type, $table, $refName, $refId, $field, $oldValue, $newValue)
    {
        /* @var \Model\BaseModel $eh */
        $nsTable = BaseService::getInstance()->getFullQualifiedModelClassName($table);
        $eh = new $nsTable();
        $eh->type = $type;
        $eh->$refName = $refId;
        $eh->field = $field;
        $eh->user = BaseService::getInstance()->getCurrentUser()->id;
        $eh->old_value = $oldValue;
        $eh->new_value = $newValue;
        $eh->created = date("Y-m-d H:i:s");
        $eh->updated = date("Y-m-d H:i:s");

        $eh->Save();
    }

    public function addCalculationHook($code, $name, $class, $method)
    {
        BaseService::getInstance()->addCalculationHook($code, $name, $class, $method);
    }
}
