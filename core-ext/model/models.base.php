<?php
class ICEHRM_Record extends ADOdb_Active_Record{

	public function getAdminAccess(){
		return array("get","element","save","delete");
	}

	public function getOtherAccess(){
		return array("get","element","save","delete");
	}

	public function getManagerAccess(){
		return array("get","element");
	}

	public function getUserAccess(){
		return array("get","element");
	}

	public function getEmployeeAccess(){
		return $this->getUserAccess();
	}

	public function getAnonymousAccess(){
		return array();
	}

	public function getUserOnlyMeAccess(){
		return array("get","element");
	}

	public function getUserOnlyMeAccessField(){
		return "employee";
	}

	public function getUserOnlyMeAccessRequestField(){
		return "employee";
	}

	public function validateSave($obj){
		return new IceResponse(IceResponse::SUCCESS,"");
	}

	public function executePreSaveActions($obj){
		return new IceResponse(IceResponse::SUCCESS,$obj);
	}

	public function executePreUpdateActions($obj){
		return new IceResponse(IceResponse::SUCCESS,$obj);
	}

	public function executePostSaveActions($obj){

	}

	public function executePostUpdateActions($obj){

	}

	public function postProcessGetData($obj){
		return $obj;
	}

	public function postProcessGetElement($obj){
		return $obj;
	}

	public function getDefaultAccessLevel(){
		return array("get","element","save","delete");
	}

	public function getVirtualFields(){
		return array(
		);
	}

	public function allowIndirectMapping(){
		return false;
	}
}