<?php

namespace EditorUser\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;

class CreateTables extends AbstractMigration implements MigrationInterface
{
	
	public function getName() {
		return 'editor_user_create_table';
	}
	
	public function up() {
		$sql = <<<'SQL'
create table if not exists `Content` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	title varchar(200) null,
    hash varchar(64) null,
	content longtext null,
    object_type varchar(100) null,
    object_field varchar(100) null,
    object_id bigint(20) NULL,
	url varchar(500) null,
	status varchar(12) null,
    category varchar(200) null,
    tags varchar(500) null,
	share_with_all int(4) null,
    share_departments varchar(400) null,
    share_teams varchar(400) null,
    share_employees varchar(1500) null,
    notifications_sent_employees varchar(5000) null,
	`created` DATETIME default NULL,
	`updated` DATETIME default NULL,
    unique key `UKEY_Content_object_type_field_id` (`object_type`,`object_field`,`object_id`),
    index `KEY_Content_hash` (`hash`),
	primary key  (`id`)
) engine=innodb default charset=utf8;

SQL;
		$this->executeQuery($sql);
		return true;
	}
	
	public function down() {
		return true;
	}
}

