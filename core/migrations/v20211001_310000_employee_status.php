<?php

namespace Classes\Migration;

class v20211001_310000_employee_status extends AbstractMigration
{

    public function up()
    {
        $sql = <<<'SQL'
create table `EmployeeStatus` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`employee` bigint(20) NOT NULL,
	`status` int(11) default null,
	`feeling` int(11) default null,
	`message` varchar(300) default null,
	`status_date` DATE NULL default NULL,
	CONSTRAINT `Fk_EmployeeStatus_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
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
