<?php
if (!class_exists('Company_structureAdminManager')) {
	
	class Company_structureAdminManager extends AbstractModuleManager{
		
		public function initializeUserClasses(){
			
		}
		
		public function initializeFieldMappings(){
	
		}
		
		public function initializeDatabaseErrorMappings(){
			$this->addDatabaseErrorMapping("CONSTRAINT `Fk_Employee_CompanyStructures` FOREIGN KEY (`department`) REFERENCES `CompanyStructures` (`id`)", "Can not delete a company structure while employees are assigned to it");
			$this->addDatabaseErrorMapping("CONSTRAINT `Fk_CompanyStructures_Own` FOREIGN KEY (`parent`) REFERENCES ", "Can not delete a parent structure");
		}
		
		public function setupModuleClassDefinitions(){

			$this->addModelClass('CompanyStructure');
		}

        public function getDashboardItemData(){
            $data = array();
            $company = new CompanyStructure();
            $data['numberOfCompanyStuctures'] = $company->Count("1 = 1");
            return $data;

        }
		
	}
}


if (!class_exists('CompanyStructure')) {
	class CompanyStructure extends ICEHRM_Record {
		var $_table = 'CompanyStructures';
			
		public function getAdminAccess(){
			return array("get","element","save","delete");
		}
			
		public function getManagerAccess(){
			return array("get","element","save","delete");
		}
			
		public function getUserAccess(){
			return array("get","element");
		}
			
		public function validateSave($obj){
			if($obj->id == $obj->parent && !empty($obj->parent)){
				return new IceResponse(IceResponse::ERROR,"A Company structure unit can not be the parent of the same unit");
			}

			return new IceResponse(IceResponse::SUCCESS,"");
		}

        public static function getAllChildCompanyStructures($companyStructureId){
            $structures = array();
            $companyStructure = new CompanyStructure();
            $companyStructure->Load("id = ?",array($companyStructureId));

            if($companyStructure->id != $companyStructureId || empty($companyStructure->id)){
                return new IceResponse(IceResponse::ERROR, array());
            }

            self::getChildCompanyStructures($companyStructure, $structures);

            $structures[$companyStructure->id] = $companyStructure;

            return new IceResponse(IceResponse::SUCCESS, array_values($structures));

        }

        private static function getChildCompanyStructures($companyStructure, &$structures){
            $child = new CompanyStructure();
            $children = $child->Find("parent = ?",array($companyStructure->id));
            if(!empty($children)){
                foreach($children as $c){
                    if(isset($structures[$c->id])){
                        continue;
                    }
                    $structures[$c->id] = $c;
                    self::getChildCompanyStructures($c, $structures);
                }
            }
        }
	}
}


if (!class_exists('Timezone')) {
    class Timezone extends ICEHRM_Record {
        var $_table = 'Timezones';

        public function getAdminAccess(){
            return array("get","element","save","delete");
        }

        public function getManagerAccess(){
            return array("get","element","save","delete");
        }

        public function getUserAccess(){
            return array("get","element");
        }

    }
}