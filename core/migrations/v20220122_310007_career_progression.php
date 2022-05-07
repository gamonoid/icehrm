<?php

namespace Classes\Migration;

class v20220122_310007_career_progression extends AbstractMigration
{

    public function up()
    {
        $sql = <<<'SQL'
create table `EmployeeCareer` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`employee` bigint(20) NOT NULL,
	`job_title` bigint(20) NULL,
	`employment_status` bigint(20) NULL,
	`department` bigint(20) NULL,
	`supervisor` bigint(20) NULL,
	`date_start` date NOT NULL,
	`date_end` date NULL,
	`details` text default NULL,
	`created` DATETIME default NULL,
	`updated` DATETIME default NULL,
	primary key  (`id`),
	CONSTRAINT `Fk_EmployeeCareer_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `Fk_EmployeeCareer_JobTitles` FOREIGN KEY (`job_title`) REFERENCES `JobTitles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_EmployeeCareer_EmploymentStatus` FOREIGN KEY (`employment_status`) REFERENCES `EmploymentStatus` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_EmployeeCareer_CompanyStructures` FOREIGN KEY (`department`) REFERENCES `CompanyStructures` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_EmployeeCareer_Supervisor` FOREIGN KEY (`supervisor`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) engine=innodb default charset=utf8;
SQL;

        return $this->executeQuery($sql);
    }

    public function down()
    {
        return true;
    }
}
