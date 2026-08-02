<?php
namespace Classes\Migration;

class v20260717_160000_create_backups extends AbstractMigration {

	public function up() {
		// Backup records created from the Connection ("System Status") module.
		// Designed to hold BOTH database backups (type='database') and, later,
		// file backups (type='files'/'full') — the file_* columns and the free-form
		// `meta` JSON carry per-backup details for either kind.
		$sql = "CREATE TABLE IF NOT EXISTS `Backups` (
			`id` bigint NOT NULL AUTO_INCREMENT,
			`name` varchar(255) DEFAULT NULL,
			`type` varchar(32) NOT NULL DEFAULT 'database',
			`status` varchar(32) NOT NULL DEFAULT 'Completed',
			`file_name` varchar(255) DEFAULT NULL,
			`file_path` varchar(500) DEFAULT NULL,
			`file_size` bigint DEFAULT NULL,
			`format` varchar(64) DEFAULT NULL,
			`encrypted` tinyint(1) NOT NULL DEFAULT '0',
			`checksum` varchar(128) DEFAULT NULL,
			`meta` text,
			`error` text,
			`created_by` int DEFAULT NULL,
			`created` datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `type` (`type`),
			KEY `created` (`created`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

		return $this->executeQuery($sql);
	}

	public function down() {
		return $this->executeQuery("DROP TABLE IF EXISTS `Backups`");
	}
}
