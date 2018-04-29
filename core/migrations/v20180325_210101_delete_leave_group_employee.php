<?php
namespace Classes\Migration;

class v20180325_210101_delete_leave_group_employee extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        alter table LeaveGroupEmployees drop FOREIGN KEY `Fk_LeaveGroupEmployees_Employee`;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        alter table LeaveGroupEmployees drop index `LeaveGroupEmployees_employee`;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        alter table LeaveGroupEmployees add CONSTRAINT `Fk_LeaveGroupEmployees_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        $sql = <<<'SQL'
        alter table LeaveGroupEmployees add UNIQUE KEY `LeaveGroupEmployees_employee` (`employee`);
SQL;
        return $this->executeQuery($sql);
    }

}
