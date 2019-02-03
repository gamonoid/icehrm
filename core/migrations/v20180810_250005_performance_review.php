<?php
namespace Classes\Migration;

class v20180810_250005_performance_review extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
create table `ReviewTemplates` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(50) not null,
	`description` varchar(500) null,
	`items` text null,
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`)
) engine=innodb default charset=utf8;
SQL;
        $this->executeQuery($sql);


        $sql = <<<'SQL'
create table `PerformanceReviews` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(150) NOT NULL,
	`employee` bigint(20) NULL,
	`coordinator` bigint(20) NULL,
	`attendees` VARCHAR(50) NOT NULL,
	`form` bigint(20) NULL,
	`status` varchar(20) NOT NULL,
	`review_date` DATETIME default '0000-00-00 00:00:00',
	`review_period_start` DATETIME default '0000-00-00 00:00:00',
	`review_period_end` DATETIME default '0000-00-00 00:00:00',
	`self_assessment_due` DATETIME default '0000-00-00 00:00:00',
	`notes` TEXT NULL,
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`),
	CONSTRAINT `Fk_PerformanceReviews_ReviewTemplates` FOREIGN KEY (`form`) REFERENCES ReviewTemplates (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_PerformanceReviews_Employees1` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_PerformanceReviews_Employees2` FOREIGN KEY (`coordinator`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) engine=innodb default charset=utf8;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
create table `ReviewFeedbacks` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`employee` bigint(20) NULL,
	`review` bigint(20) NULL,
	`subject` bigint(20) NULL,
	`form` bigint(20) NULL,
	`status` varchar(20) NOT NULL,
	`dueon` DATETIME default '0000-00-00 00:00:00',
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`),
	CONSTRAINT `Fk_ReviewFeedbacks_ReviewTemplates` FOREIGN KEY (`form`) REFERENCES ReviewTemplates (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_ReviewFeedbacks_PerformanceReviews` FOREIGN KEY (`review`) REFERENCES PerformanceReviews (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_ReviewFeedbacks_Employees1` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	CONSTRAINT `Fk_ReviewFeedbacks_Employees2` FOREIGN KEY (`subject`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) engine=innodb default charset=utf8;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
