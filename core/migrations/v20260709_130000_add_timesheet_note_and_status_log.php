<?php
namespace Classes\Migration;

class v20260709_130000_add_timesheet_note_and_status_log extends AbstractMigration {

	public function up() {
		$sql = [];

		// Note for a timesheet status change (e.g. an optional rejection reason).
		$sql[] = "ALTER TABLE EmployeeTimeSheets ADD COLUMN note TEXT NULL";

		// Timesheet approvals move Submitted -> Approved/Rejected, so 'Submitted'
		// must be allowed in the status-change log enum for those to be recorded.
		$sql[] = "ALTER TABLE StatusChangeLogs MODIFY status_from "
			."ENUM('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing','Submitted') NULL";
		$sql[] = "ALTER TABLE StatusChangeLogs MODIFY status_to "
			."ENUM('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing','Submitted') NULL DEFAULT 'Pending'";

		$result = true;
		foreach ($sql as $query) {
			$result = $this->executeQuery($query) && $result;
		}

		return $result;
	}

	public function down() {
		return true;
	}

}
