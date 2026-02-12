<?php
namespace Classes\Migration;

class v20220802_330006_add_event_id_col extends AbstractMigration {

    public function up(){
        $sql = <<<'SQL'
ALTER TABLE `EmployeeLeaves` ADD COLUMN `event_ids` text;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE `HoliDays` ADD COLUMN `event_id` varchar(100) default '';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE `PerformanceReviews` ADD COLUMN `event_id` varchar(100) default '';
SQL;
        $this->executeQuery($sql);

        return true;
    }

    public function down(){
    }

}
