<?php
namespace Classes\Migration;

class v20180507_230001_update_travel_record_type extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        Alter table EmployeeTravelRecords modify column `type` varchar(200)  DEFAULT '';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
        Update Settings set value = '1' where name = 'System: Reset Module Names';
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
