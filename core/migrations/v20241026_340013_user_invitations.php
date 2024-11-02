<?php

namespace Classes\Migration;

class v20241026_340013_user_invitations extends AbstractMigration
{

	public function up()
	{
		$sql = <<<'SQL'
create table `UserInvitations` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`email` varchar(100) NOT NULL,
	`username` varchar(100) NOT NULL,
	`first_name` varchar(100) NOT NULL,
	`last_name` varchar(100) NOT NULL,
	`user_level` varchar(20) NOT NULL,
	`password` varchar(100) NOT NULL,
	`timezone` varchar(100) NULL,
	`employee_id` varchar(50) NOT NULL,
	`department` bigint(20) NULL,
	`joined_date` date NULL,
	`job_title` bigint(20) NULL,
	`employment_status` bigint(20) NULL,
	`pay_grade` bigint(20) NULL,
	`supervisor` bigint(20) NULL,
	`country` char(2) NULL,
	`invitation_status` int(4) DEFAULT 0,
	`created_user_id` bigint(20) NULL,
	`created_employee_id` bigint(20) NULL,
	`created` DATETIME default NULL,
	`updated` DATETIME default NULL,
	primary key  (`id`)
) engine=innodb default charset=utf8;
SQL;

		return $this->executeQuery($sql);
	}

	public function down()
	{
		return true;
	}
}
