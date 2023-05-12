<?php

namespace KudosAdmin\Migrations;

use Classes\Migration\AbstractMigration;
use Classes\Migration\MigrationInterface;

class CreateTables extends AbstractMigration implements MigrationInterface
{
	
	public function getName() {
		return 'kudos_create_table';
	}
	
	public function up() {
        $sql = <<<'SQL'
            create table EmployeeKudos
            (
                id bigint auto_increment primary key,
                employee bigint null,
                sender bigint not null,
                message varchar(500) charset utf8 not null,
                created datetime null,
                updated datetime null,
                CONSTRAINT `Fk_EmployeeKudos_Sender` FOREIGN KEY (`sender`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `Fk_EmployeeKudos_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            )
            collate=utf8mb4_unicode_ci;
SQL;
        $this->executeQuery($sql);
	}
	
	public function down() {
        $sql = <<<'SQL'
            DROP TABLE IF EXISTS `EmployeeKudos`; 
SQL;
        return $this->executeQuery($sql);
	}
}

