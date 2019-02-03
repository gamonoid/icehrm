<?php
namespace Classes\Migration;

use Model\Report;

class v20180801_240003_asset_management extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
create table `AssetTypes` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(35) NOT NULL,
	`description` TEXT NULL,
	`attachment` varchar(100) NULL,
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`)
) engine=innodb default charset=utf8;
SQL;
        $this->executeQuery($sql);


        $sql = <<<'SQL'
create table `CompanyAssets` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`code` VARCHAR(30) NOT NULL,
	`type` bigint(20) NULL,
	`attachment` varchar(100) NULL,
	`employee` bigint(20) NULL,
	`department` bigint(20) NULL,
	`description` TEXT NULL,
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`),
	CONSTRAINT `Fk_CompanyAssets_AssetTypes` FOREIGN KEY (`type`) REFERENCES `AssetTypes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_CompanyAssets_Employees` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_CompanyAssets_CompanyStructures` FOREIGN KEY (`department`) REFERENCES `CompanyStructures` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) engine=innodb default charset=utf8;
SQL;
        $this->executeQuery($sql);


        $report = new Report();
        $report->name = 'Company Asset Report';
        $report->parameters = '[["department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}],["type", {"label":"Asset Type","type":"select2","remote-source":["AssetType","id","name"],"allow-null":true}]]';
        $report->query = 'AssetUsageReport';
        $report->type = 'Class';
        $report->paramOrder = '["department","type"]';
        $report->report_group = 'Resources';
        $report->output = 'CSV';
        $report->details = 'List company assets assigned to employees and departments';
        $ok = $report->Save();

        return true;
    }

    public function down(){

        return true;
    }

}
