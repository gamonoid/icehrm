<?php
namespace Classes\Migration;

class v20240615_340007_increase_custom_filed_type extends AbstractMigration {

	public function up(){

		$sql = <<<'SQL'
Alter table CustomFieldValues modify column `type` varchar(40) not null;
SQL;
		$this->executeQuery($sql);

		$sql = <<<'SQL'
Alter table Job add column `additional_fields` TEXT null;
SQL;
		$this->executeQuery($sql);

		return true;
	}

	public function down(){
		return true;
	}
}
