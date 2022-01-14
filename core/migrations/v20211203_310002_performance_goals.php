<?php

namespace Classes\Migration;

class v20211203_310002_performance_goals extends AbstractMigration
{

    public function up()
    {
        $sql = <<<'SQL'
create table `EmployeeGoals` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`employee` bigint(20) NOT NULL,
	`review` bigint(11) null,
	`status` varchar(15) default null,
	`title` varchar(250) default null,
	`description` text default null,
	`manager_rating` int(11) default null,
	`manager_feedback` text default null,
	`employee_rating` int(11) default null,
	`employee_feedback` text default null,
	`created` DATETIME default NULL,
	`updated` DATETIME default NULL,
	CONSTRAINT `Fk_EmployeeGoals_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `Fk_EmployeeGoals_Review` FOREIGN KEY (`review`) REFERENCES `PerformanceReviews` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
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
