<?php
namespace Classes\Migration;

class v20200411_270009_email_log extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
create table `EmailLog` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`subject` varchar(300) NOT NULL,
	`toEmail` varchar(300) NOT NULL,
	`body` text NULL,
	`cclist` varchar(500) NULL,
	`bcclist` varchar(500) NULL,
	`created` DATETIME default NULL,
	`updated` DATETIME default NULL,
	`status` enum('Pending','Sent','Failed') default 'Pending',
	primary key  (`id`),
	key `KEY_EmailLog_status` (`status`)
) engine=innodb default charset=utf8;
SQL;

        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
