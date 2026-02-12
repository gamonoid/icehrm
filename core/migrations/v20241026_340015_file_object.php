<?php

namespace Classes\Migration;

class v20241026_340015_file_object extends AbstractMigration
{

	public function up()
	{
		$sql = <<<'SQL'
ALTER TABLE Files ADD COLUMN `object_type` varchar(50) NULL;
SQL;

		$this->executeQuery($sql);

		$sql = <<<'SQL'
ALTER TABLE Files ADD COLUMN `object_id` bigint(20) NULL;
SQL;

		$this->executeQuery($sql);

		return true;
	}

	public function down()
	{
		return true;
	}
}
