<?php
namespace Classes\Migration;

class v20171001_200201_update_attendance_out extends AbstractMigration{

	public function up(){

		$sql = <<<'SQL'
        alter table Attendance modify column `out_time` datetime default NULL;
SQL;
		return $this->executeQuery($sql);
	}

	public function down(){
		$sql = <<<'SQL'
        alter table Attendance modify column `out_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00';
SQL;

		return $this->executeQuery($sql);
	}

}

