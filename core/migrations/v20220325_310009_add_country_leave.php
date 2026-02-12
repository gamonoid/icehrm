<?php
namespace Classes\Migration;

class v20220325_310009_add_country_leave extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
    ('Leave: Select Leave Period from Employee Department Country', '0', 'The leave period for the employee should be decided based on the country of the department which the employee is attached to','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]','Leave');
SQL;
        return $this->executeQuery($sql);

    }

    public function down(){

        return true;
    }

}
