<?php
namespace Classes\Migration;

/**
 * Attendance.in_ip and out_ip were varchar(25), which is too short for an IPv6
 * address (up to 45 characters) or a proxy's X-Forwarded-For list. With strict SQL
 * mode the punch was rejected ("Data too long for column 'in_ip'") and the employee
 * saw "Error occurred while saving attendance". Widen both columns to the same
 * size AuditLog.ip already uses.
 */
class v20261106_100000_widen_attendance_ip extends AbstractMigration {

	public function up() {
		return $this->executeQuery(
			"ALTER TABLE Attendance MODIFY `in_ip` varchar(100) default null, MODIFY `out_ip` varchar(100) default null"
		);
	}

	public function down() {
		return true;
	}

}
