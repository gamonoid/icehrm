<?php
namespace Classes\Migration;

class v20170918_200000_add_attendance_image_out extends AbstractMigration{

	public function up(){

		$sql = <<<'SQL'
        Alter table Attendance add column `image_out` longtext default null;
SQL;
		return $this->executeQuery($sql);
	}

	public function down(){
		$sql = <<<'SQL'
        Alter table Attendance drop column `image_out`;
SQL;

		return $this->executeQuery($sql);
	}

}
