<?php
namespace Classes\Migration;

class v20260709_120000_force_advanced_timesheets_off extends AbstractMigration {

	public function up() {
		// Advanced Timesheets is retired (ignored by the new UI). Force the stored
		// value off; it is also hidden from the settings screen and forced to '0'
		// at runtime in SettingsManager::getSetting().
		return $this->executeQuery(
			"UPDATE Settings SET value = '0' WHERE name = 'System: Advanced Timesheets';"
		);
	}

	public function down() {
		return true;
	}

}
