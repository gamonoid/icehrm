<?php
namespace Classes\Migration;

class v20250324_120100_update_settings_categories extends AbstractMigration {

	public function up() {
		$sql = [];
		
		// Update category to 'Files' for all settings where name starts with 'Files:'
		$sql[] = <<<'SQL'
UPDATE Settings SET category = 'Files' WHERE name LIKE 'Files:%';
SQL;

		// Update category to 'Attendance' for the specific setting
		$sql[] = <<<'SQL'
UPDATE Settings SET category = 'Attendance' WHERE name = 'Attendance: Request Attendance Location on Mobile';
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
