<?php
namespace Classes\Migration;

class v20240908_340011_no_leave_approvals extends AbstractMigration {

	public function up(){

		$sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'Leave: Manager Needs to Approve', 
        '1',
        'Manager Needs to Approve Leave Requests',
        '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]',
        'Leave'
        );
SQL;

		$sql[] = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'System: Managers Can Switch to Employee Profiles', 
        '0',
        'Control if Managers can login as their direct reports',
        '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]',
        'System'
        );
SQL;

		$sql[] = <<<'SQL'
Alter table LeaveTypes add column `notes` TEXT NULL;
SQL;

		$result = true;
		foreach ($sql as $query) {
			$result = $result && $this->executeQuery($query);
		}

		return $result;
	}

	public function down(){
		return true;
	}

}
