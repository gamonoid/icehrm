<?php

namespace Advance_reportsAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;

class CreateTables extends AbstractMigration implements MigrationInterface
{
	
	public function getName() {
		return 'advance_reports_create_table';
	}
	
	public function up() {
		return true;
	}
	
	public function down() {
		return true;
	}
}

