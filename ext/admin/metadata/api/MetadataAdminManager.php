<?php
if (!class_exists('MetadataAdminManager')) {
	
	class MetadataAdminManager extends AbstractModuleManager{
		
		public function initializeUserClasses(){
			
		}
		
		public function initializeFieldMappings(){
			
		}
		
		public function initializeDatabaseErrorMappings(){
			
		}
		
		public function setupModuleClassDefinitions(){			
			$this->addModelClass('Country');
			$this->addModelClass('Province');
			$this->addModelClass('CurrencyType');
			$this->addModelClass('Nationality');
			$this->addModelClass('ImmigrationStatus');
			$this->addModelClass('Ethnicity');
			$this->addModelClass('CalculationHook');
		}
		
	}
}

if (!class_exists('Country')) {
	class Country extends ICEHRM_Record {
		var $_table = 'Country';
	
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
	
		public function getUserAccess(){
			return array();
		}
	
		public function getAnonymousAccess(){
			return array("get","element");
		}

		function Find($whereOrderBy,$bindarr=false,$pkeysArr=false,$extra=array()){
			$allowedCountriesStr = SettingsManager::getInstance()->getSetting('System: Allowed Countries');
			$allowedCountries = array();
			if(!empty($allowedCountriesStr)){
				$allowedCountries = json_decode($allowedCountriesStr,true);
			}

			if(!empty($allowedCountries)){
				$res =  parent::Find("id in (".implode(",",$allowedCountries).")" , array());
				if(empty($res)){
					SettingsManager::getInstance()->setSetting('System: Allowed Countries','');
				}else{
					return $res;
				}
			}

			return parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);
		}
	}	
}

if (!class_exists('Province')) {
	class Province extends ICEHRM_Record {
		var $_table = 'Province';
	
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
	
		public function getUserAccess(){
			return array();
		}
	
		public function getAnonymousAccess(){
			return array("get","element");
		}
	}	
}


if (!class_exists('CurrencyType')) {
	class CurrencyType extends ICEHRM_Record {
		var $_table = 'CurrencyTypes';
	
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
	
		public function getUserAccess(){
			return array();
		}
	
		public function getAnonymousAccess(){
			return array("get","element");
		}

		function Find($whereOrderBy,$bindarr=false,$pkeysArr=false,$extra=array()){
			$allowedCountriesStr = SettingsManager::getInstance()->getSetting('System: Allowed Currencies');
			$allowedCountries = array();
			if(!empty($allowedCountriesStr)){
				$allowedCountries = json_decode($allowedCountriesStr,true);
			}

			if(!empty($allowedCountries)){
				$res =  parent::Find("id in (".implode(",",$allowedCountries).")" , array());
				if(empty($res)){
					SettingsManager::getInstance()->setSetting('System: Allowed Currencies','');
				}else{
					return $res;
				}
			}

			return parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);
		}
	}	
}


if (!class_exists('Nationality')) {
	class Nationality extends ICEHRM_Record {
		var $_table = 'Nationality';
	
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
	
		public function getUserAccess(){
			return array();
		}
	
		public function getAnonymousAccess(){
			return array("get","element");
		}

		function Find($whereOrderBy,$bindarr=false,$pkeysArr=false,$extra=array()){
			$allowedCountriesStr = SettingsManager::getInstance()->getSetting('System: Allowed Nationality');
			$allowedCountries = array();
			if(!empty($allowedCountriesStr)){
				$allowedCountries = json_decode($allowedCountriesStr,true);
			}

			if(!empty($allowedCountries)){
				$res = parent::Find("id in (".implode(",",$allowedCountries).")" , array());
				if(empty($res)){
					SettingsManager::getInstance()->setSetting('System: Allowed Currencies','');
				}else{
					return $res;
				}
			}

			return parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);
		}


	}
}


if (!class_exists('ImmigrationStatus')) {
    class ImmigrationStatus extends ICEHRM_Record {
        var $_table = 'ImmigrationStatus';

        public function getAdminAccess(){
            return array("get","element","save","delete");
        }

        public function getUserAccess(){
            return array();
        }

        public function getAnonymousAccess(){
            return array("get","element");
        }
    }
}


if (!class_exists('Ethnicity')) {
    class Ethnicity extends ICEHRM_Record {
        var $_table = 'Ethnicity';

        public function getAdminAccess(){
            return array("get","element","save","delete");
        }

        public function getUserAccess(){
            return array();
        }

        public function getAnonymousAccess(){
            return array("get","element");
        }
    }
}

if (!class_exists('CalculationHook')) {
    class CalculationHook extends ICEHRM_Record {
        var $_table = 'CalculationHooks';

        public function getAdminAccess(){
            return array("get","element","save","delete");
        }

        public function getUserAccess(){
            return array();
        }

        public function getAnonymousAccess(){
            return array("get","element");
        }

		function Find($whereOrderBy,$bindarr=false,$pkeysArr=false,$extra=array()){
			return BaseService::getInstance()->getCalculationHooks();
		}

		function Load($where=null,$bindarr=false){
			return BaseService::getInstance()->getCalculationHook($bindarr[0]);
		}
    }
}

if (!class_exists('CustomFieldValue')) {
	class CustomFieldValue extends ICEHRM_Record {
		var $_table = 'CustomFieldValues';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getUserAccess(){
			return array("get","element","save","delete");
		}

		public function getAnonymousAccess(){
			return array();
		}
	}
}

if (!class_exists('SupportedLanguage')) {
	class SupportedLanguage extends ICEHRM_Record {
		var $_table = 'SupportedLanguages';

		public function getAdminAccess(){
			return array("get","element","save","delete");
		}

		public function getUserAccess(){
			return array();
		}

		public function getAnonymousAccess(){
			return array("get","element");
		}
	}
}




