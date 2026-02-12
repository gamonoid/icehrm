<?php
namespace Classes\Migration;

class v20251210_130000_update_system_settings_order extends AbstractMigration {

	public function up() {
		$sql = [];
		
		// Delete settings
		$sql[] = "DELETE FROM Settings WHERE name = 'System: Google Sync Calendar';";
		$sql[] = "DELETE FROM Settings WHERE name = 'Api: REST Api Token';";
		
		// Rename setting
		$sql[] = "UPDATE Settings SET name = 'System: Advanced Timesheets' WHERE name = 'System: Time-sheet Entry Start and End time Required';";
		
		// Update setting_order for System settings
		$sql[] = "UPDATE Settings SET setting_order = 1 WHERE name = 'System: Allowed Countries';";
		$sql[] = "UPDATE Settings SET setting_order = 2 WHERE name = 'System: Allowed Currencies';";
		$sql[] = "UPDATE Settings SET setting_order = 3 WHERE name = 'System: Allowed Nationality';";
		
		$sql[] = "UPDATE Settings SET setting_order = 10 WHERE name = 'System: Company Structure Managers Enabled';";
		$sql[] = "UPDATE Settings SET setting_order = 11 WHERE name = 'System: Child Company Structure Managers Enabled';";
		$sql[] = "UPDATE Settings SET setting_order = 12 WHERE name = 'System: Managers Can Switch to Employee Profiles';";
		$sql[] = "UPDATE Settings SET setting_order = 13 WHERE name = 'System: Advanced Timesheets';";
		
		$sql[] = "UPDATE Settings SET setting_order = 20 WHERE name = 'System: G Suite Enabled';";
		$sql[] = "UPDATE Settings SET setting_order = 21 WHERE name = 'System: G Suite Disable Password Login';";
		$sql[] = "UPDATE Settings SET setting_order = 22 WHERE name = 'System: Google Client Secret Path';";
		$sql[] = "UPDATE Settings SET setting_order = 23 WHERE name = 'System: Google Maps Api Key';";
		
		$sql[] = "UPDATE Settings SET setting_order = 30 WHERE name = 'System: AWS Region';";
		$sql[] = "UPDATE Settings SET setting_order = 31 WHERE name = 'System: AWS Region for Editor';";
		
		$sql[] = "UPDATE Settings SET setting_order = 40 WHERE name = 'System: Data Directory';";
		$sql[] = "UPDATE Settings SET setting_order = 41 WHERE name = 'System: Language';";
		$sql[] = "UPDATE Settings SET setting_order = 42 WHERE name = 'System: Debug Mode';";
		
		$sql[] = "UPDATE Settings SET setting_order = 50 WHERE name = 'System: Reset Modules and Permissions';";
		$sql[] = "UPDATE Settings SET setting_order = 51 WHERE name = 'System: Reset Module Names';";
		$sql[] = "UPDATE Settings SET setting_order = 52 WHERE name = 'System: Add New Permissions';";
		
		// Update setting_order for Email settings
		$sql[] = "UPDATE Settings SET setting_order = 1 WHERE name = 'Email: Enable';";
		$sql[] = "UPDATE Settings SET setting_order = 2 WHERE name = 'Email: Email From';";
		$sql[] = "UPDATE Settings SET setting_order = 3 WHERE name = 'Email: Mode';";
		$sql[] = "UPDATE Settings SET setting_order = 4 WHERE name = 'Email: SMTP Host';";
		$sql[] = "UPDATE Settings SET setting_order = 5 WHERE name = 'Email: SMTP Authentication Required';";
		$sql[] = "UPDATE Settings SET setting_order = 6 WHERE name = 'Email: SMTP User';";
		$sql[] = "UPDATE Settings SET setting_order = 7 WHERE name = 'Email: SMTP Password';";
		$sql[] = "UPDATE Settings SET setting_order = 8 WHERE name = 'Email: SMTP Port';";
		$sql[] = "UPDATE Settings SET setting_order = 9 WHERE name = 'Email: Amazon Access Key ID';";
		$sql[] = "UPDATE Settings SET setting_order = 10 WHERE name = 'Email: Amazon Secret Access Key';";
		
		// Update descriptions
		$sql[] = "UPDATE Settings SET description = 'Update the company name' WHERE name = 'Company: Name';";
		$sql[] = "UPDATE Settings SET description = 'When enabled, prints additional error logs' WHERE name = 'System: Debug Mode';";
		$sql[] = "UPDATE Settings SET description = 'Allow managers to view employees within their assigned company structure if they are designated as the lead' WHERE name = 'System: Company Structure Managers Enabled';";
		$sql[] = "UPDATE Settings SET description = 'Allow managers to view employees within their assigned company structure and all child structures if they are designated as the head' WHERE name = 'System: Child Company Structure Managers Enabled';";
		$sql[] = "UPDATE Settings SET description = 'IceHrm supports both simple timesheet data entry and detailed entries with exact time durations. Select \"Yes\" to require employees to enter notes and exact start and end times' WHERE name = 'System: Advanced Timesheets';";
		$sql[] = "UPDATE Settings SET description = 'Enable login via Google Workspace accounts' WHERE name = 'System: G Suite Enabled';";
		$sql[] = "UPDATE Settings SET description = 'Disable password-based login when Google Workspace login is enabled' WHERE name = 'System: G Suite Disable Password Login';";
		$sql[] = "UPDATE Settings SET description = 'Path to store data files on your server, e.g., /user/local/data/. If not specified, the default path <icehrm>/app/data will be used' WHERE name = 'System: Data Directory';";
		$sql[] = "UPDATE Settings SET description = 'Enable or disable all outgoing emails' WHERE name = 'Email: Enable';";
		$sql[] = "UPDATE Settings SET description = 'Usually 25, but may vary depending on your SMTP service' WHERE name = 'Email: SMTP Port';";
		$sql[] = "UPDATE Settings SET description = 'If email mode is Amazon SES please provide SES Key' WHERE name = 'Email: Amazon Access Key ID';";
		$sql[] = "UPDATE Settings SET description = 'If email mode is Amazon SES please provide SES Secret' WHERE name = 'Email: Amazon Secret Access Key';";

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
