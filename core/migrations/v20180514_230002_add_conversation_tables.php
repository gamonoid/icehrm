<?php
namespace Classes\Migration;

class v20180514_230002_add_conversation_tables extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
create table `Conversations` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`message` LONGTEXT NOT NULL,
	`type` varchar(35) NOT NULL,
	`attachment` varchar(100) NULL,
	`employee` bigint(20) NOT NULL,
	`target` bigint(20) NULL,
	`created` DATETIME default NULL,
	`updated` DATETIME default NULL,
	`timeint` BIGINT(20) NOT NULL,
	primary key  (`id`),
	unique key `KEY_Conversations_attachment` (`attachment`),
	index `KEY_Conversations_type` (`type`),
	index `KEY_Conversations_employee` (`employee`),
	index `KEY_Conversations_target` (`target`),
	index `KEY_Conversations_target_type` (`target`,`type`),
	index `KEY_Conversations_timeint` (`timeint`),
	index `KEY_Conversations_timeint_type` (`timeint`, `type`)
) engine=innodb default charset=utf8;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
create table `ConversationUserStatus` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
	`employee` bigint(20) NOT NULL,
	`status` varchar(15) NULL,
	`seen_at` DATETIME default NULL,
	primary key  (`id`),
	unique key `KEY_ConversationLastSeen_employee` (`employee`),
	index `KEY_ConversationLastSeen_seen_at` (`seen_at`),
	index `KEY_ConversationLastSeen_status` (`seen_at`)
) engine=innodb default charset=utf8;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Update Settings set value = '1' where name = 'System: Reset Module Names';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Update Settings set value = '1' where name = 'System: Add New Permissions';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        INSERT INTO `SupportedLanguages` (`name`, `description`) VALUES ('fi', 'Finnish');
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
