<?php
namespace Classes\Migration;

class v20230916_340002_leave_unavailable_period extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
    ('Leave: Lock Period After Joined Date', '0', 'The number of months an employee should wait to apply for their first leave after the joined date','["value", {"label":"Value","type":"select","source":[["0","0"],["1","1"],["2","2"],["3","3"],["4","4"],["5","5"],["6","6"],["7","7"],["8","8"],["9","9"],["10","10"],["11","11"],["12","12"]]}]','Leave');
SQL;
        return $this->executeQuery($sql);

    }

    public function down(){

        return true;
    }

}
