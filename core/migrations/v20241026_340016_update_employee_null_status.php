<?php

namespace Classes\Migration;

class v20241026_340016_update_employee_null_status extends AbstractMigration
{

	public function up()
	{
		$sql = <<<'SQL'
UPDATE Employees set status = 'Active' where status is NULL;
SQL;

		return $this->executeQuery($sql);
	}

	public function down()
	{
		return true;
	}
}
