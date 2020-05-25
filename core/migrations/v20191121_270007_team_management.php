<?php
namespace Classes\Migration;

class v20191121_270007_team_management extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
create table `EmployeeTeams` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(100),
	`description` text,
	`lead` bigint(20),
	`department` bigint(20),
	primary key  (`id`),
	CONSTRAINT `Fk_EmployeeTeams_Lead` FOREIGN KEY (`lead`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_EmployeeTeams_Department` FOREIGN KEY (`department`) REFERENCES `CompanyStructures` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) engine=innodb default charset=utf8;
SQL;

        $this->executeQuery($sql);

        $sql = <<<'SQL'
create table `EmployeeTeamMembers` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`team` bigint(20) ,
	`member` bigint(20),
	`role` varchar(60),
	primary key  (`id`),
	CONSTRAINT `Fk_EmployeeTeamMembers_Team` FOREIGN KEY (`team`) REFERENCES `EmployeeTeams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `Fk_EmployeeTeamMembers_Member` FOREIGN KEY (`member`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) engine=innodb default charset=utf8;
SQL;

        return $this->executeQuery($sql);
    }
    public function down(){
        return true;
    }
}
