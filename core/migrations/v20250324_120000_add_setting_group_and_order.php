<?php
namespace Classes\Migration;

class v20250324_120000_add_setting_group_and_order extends AbstractMigration {

	public function up() {
		$sql = [];
		
		// Add setting_group column
		$sql[] = <<<'SQL'
ALTER TABLE Settings ADD COLUMN `setting_group` varchar(20) NULL;
SQL;

		// Add setting_order column with default value 0
		$sql[] = <<<'SQL'
ALTER TABLE Settings ADD COLUMN `setting_order` int NOT NULL DEFAULT 0;
SQL;

		// Execute each query separately
		$result = true;
		foreach ($sql as $query) {
			$result = $result && $this->executeQuery($query);
		}

		return $result;
	}

	public function down() {
		return true;
	}

}
