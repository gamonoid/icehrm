<?php
namespace Classes\Migration;

class v20210606_290003_system_meta_data extends AbstractMigration {

    public function up(){
        $sql = <<<'SQL'
create table `SystemData` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(50) not null,
	`value` text default null,
	primary key  (`id`),
	unique key `name` (`name`)
) engine=innodb default charset=utf8;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
