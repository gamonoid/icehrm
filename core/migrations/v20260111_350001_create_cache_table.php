<?php

namespace Classes\Migration;

class v20260111_350001_create_cache_table extends AbstractMigration
{

	public function up()
	{
		$sql = <<<'SQL'
CREATE TABLE `Cache` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`cache_key` varchar(255) NOT NULL,
	`cache_value` longtext NULL,
	`expires_at` datetime NOT NULL,
	`created_at` datetime NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `idx_cache_key` (`cache_key`),
	KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

		return $this->executeQuery($sql);
	}

	public function down()
	{
		return true;
	}
}
