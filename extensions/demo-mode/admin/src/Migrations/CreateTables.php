<?php

namespace DemoModeAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;

class CreateTables extends AbstractMigration implements MigrationInterface
{

	public function getName() {
		return 'demo-mode_create_table';
	}

	public function up() {
		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `DemoDataEntries` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `record_id` bigint(20) NOT NULL,
  `data_type` varchar(50) NOT NULL COMMENT 'employee, leave, attendance, timesheet, payroll',
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_table_record` (`table_name`, `record_id`),
  KEY `idx_data_type` (`data_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
		return $this->executeQuery($sql);
	}

	public function down() {
		$sql = "DROP TABLE IF EXISTS `DemoDataEntries`;";
		return $this->executeQuery($sql);
	}
}

