<?php
namespace Classes\Migration;

class v20240724_340010_add_leave_group_to_holiday extends AbstractMigration {

	public function up(){

		$sql = <<<'SQL'
ALTER TABLE HoliDays ADD COLUMN `leave_group` bigint(20) NULL;
SQL;
		$this->executeQuery($sql);

		$sql = <<<'SQL'
ALTER TABLE HoliDays DROP INDEX holidays_dateh_country;
SQL;
		$this->executeQuery($sql);

		$sql = <<<'SQL'
ALTER TABLE HoliDays ADD CONSTRAINT holidays_dateh_country_group UNIQUE (dateh, country, leave_group);
SQL;
		$this->executeQuery($sql);

		return true;
	}

	public function down(){
		return true;
	}
}
