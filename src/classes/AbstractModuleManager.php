<?php
/**
 *The base class for module manager classes. ModuleManager classes which extend this class provide core backend functionality
 *to each module such as defining models, error handliing and other configuration details
 *@class AbstractModuleManager
 */
abstract class AbstractModuleManager{
	
	private $fileFieldMappings = array();
	private $userClasses = array();
	private $errorMappings = array();
	private $modelClasses = array();
	
	/**
	 * Override this method in module manager class to define user classes. 
	 * A user class is a class that is mapped to a table having a field named profile. The profile field is mapped to the id of a Profile element.
	 * When a user is saving this type of an object in db, profile field will be set to the id of the Profile of currently logged in or switched user.
	 * When a user is retriving this type of records, only the records having profile field set to currently logged in users profile id will be released.
	 * @method initializeUserClasses
	 * @example
	  	public function initializeUserClasses(){
			$this->addUserClass("EmployeeDocument");
		}
	 *  
	 */
	public abstract function initializeUserClasses();
	
	/**
	 * Override this method in module manager class to define file field mappings. If you have a table field that stores a name of a file which need to be
	 * deleted from the disk when the record is deleted a file field mapping should be added.
	 * @method initializeFieldMappings
	 * @example
		 public function initializeFieldMappings(){
			$this->addFileFieldMapping('EmployeeDocument', 'attachment', 'name');
		 }
	 */
	public abstract function initializeFieldMappings();
	
	
	/**
	 * Override this method in module manager class to define DB error mappings. Some actions to your model classes trigger database errors.
	 * These errors need to be translated to user friendly texts using DB error mappings
	 * @method initializeDatabaseErrorMappings
	 * @example
	 	public function initializeDatabaseErrorMappings(){
			$this->addDatabaseErrorMapping('CONSTRAINT `Fk_User_Employee` FOREIGN KEY',"Can not delete Employee, please delete the User for this employee first.");
			$this->addDatabaseErrorMapping("Duplicate entry|for key 'employee'","A duplicate entry found");
		}
	 */
	public abstract function initializeDatabaseErrorMappings();
	
	/**
	 * Override this method in module manager class to add model classes to this module. All the model classes defind for the module should be added here
	 * @method setupModuleClassDefinitions
	 * @example
		public function setupModuleClassDefinitions(){
			$this->addModelClass('Employee');
			$this->addModelClass('EmploymentStatus');
		}
	 */
	public abstract function setupModuleClassDefinitions();
	
	
	public function setupRestEndPoints(){
		
	}
	
	public function setupFileFieldMappings(&$fileFields){
		foreach ($this->fileFieldMappings as $mapping){
			if(empty($fileFields[$mapping[0]])){
				$fileFields[$mapping[0]] = array();
			}
			
			$fileFields[$mapping[0]][$mapping[1]] = $mapping[2];
		}	
	}
	
	public function setupUserClasses(&$userTables){
		foreach($this->userClasses as $className){
			if(!in_array($className, $userTables)){
				$userTables[] = $className;
			}	
		}
			
	}
	
	public function setupErrorMappings(&$mysqlErrors){
		foreach($this->errorMappings as $name=>$desc){
			$mysqlErrors[$name] = $desc;
		}
			
	}
	
	public function getModelClasses(){
		return $this->modelClasses;
	}
	
	protected function addFileFieldMapping($className, $fieldName, $fileTableFieldName){
		$this->fileFieldMappings[] = array($className, $fieldName, $fileTableFieldName);
	}
	
	protected function addUserClass($className){
		$this->userClasses[] = $className;
	}
	
	protected function addDatabaseErrorMapping($error, $description){
		$this->errorMappings[$error] = $description;
	}
	
	protected function addModelClass($className){
		$this->modelClasses[] = $className;
	}


}