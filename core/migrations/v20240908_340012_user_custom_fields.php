<?php

namespace Classes\Migration;

class v20240908_340012_user_custom_fields extends AbstractMigration
{

	public function up()
	{
		$sql = <<<'SQL'
create table `UserMeta` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`user_id` bigint(20) NOT NULL,
	`meta_key` varchar(20) NULL,
	`meta_value` text default NULL,
	`created` DATETIME default NULL,
	`updated` DATETIME default NULL,
	primary key  (`id`),
	UNIQUE UserCustomFields_user_id_key (`user_id`, `meta_key`),
	CONSTRAINT `Fk_UserCustomFields_User` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) engine=innodb default charset=utf8;
SQL;

		return $this->executeQuery($sql);
	}

	public function down()
	{
		return true;
	}
}
