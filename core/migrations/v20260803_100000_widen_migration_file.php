<?php
namespace Classes\Migration;

/**
 * Migrations.file was varchar(50), which silently truncated-and-rejected any
 * migration whose filename exceeded 50 chars ("Data too long for column 'file'").
 * Those migrations could never be recorded, so they were re-queued and never run
 * on every single request. Widen the column so long names are storable.
 *
 * Kept deliberately short-named so it can record itself under the old width.
 */
class v20260803_100000_widen_migration_file extends AbstractMigration {

	public function up() {
		return $this->executeQuery("ALTER TABLE Migrations MODIFY `file` varchar(255) NOT NULL");
	}

	public function down() {
		return true;
	}

}
