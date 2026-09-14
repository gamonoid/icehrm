<?php
namespace Classes\Migration;

class v20260713_150000_create_secure_resources extends AbstractMigration {

	public function up() {
		// Hash-protected direct links to internal HR resources/actions
		// (served by app/secure/ without a login). Each row names a
		// handler_class whose handle() decides what the visitor sees.
		$sql = "CREATE TABLE IF NOT EXISTS `SecureResources` (
			`id` bigint NOT NULL AUTO_INCREMENT,
			`resource_id` varchar(64) NOT NULL,
			`hash` varchar(128) NOT NULL,
			`handler_class` varchar(255) NOT NULL,
			`data` text,
			`status` enum('Active','Disabled') NOT NULL DEFAULT 'Active',
			`created` datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			UNIQUE KEY `resource_id` (`resource_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

		return $this->executeQuery($sql);
	}

	public function down() {
		return $this->executeQuery("DROP TABLE IF EXISTS `SecureResources`");
	}
}
