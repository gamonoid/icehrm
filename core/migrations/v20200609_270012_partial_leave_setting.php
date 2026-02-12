<?php
namespace Classes\Migration;

use Model\Setting;

class v20200609_270012_partial_leave_setting extends AbstractMigration
{

    public function up()
    {

        $sql = <<<'SQL'
        REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) 
        VALUES (
        'Leave: Limit Allowed Partial Leave Days', 
        '',
        'Allow only a selected set of partial leave days (if empty we allow all partial leave day types)',
        '["value", {"label":"Value","type":"select2multi","source": [["Full Day","Full Day"], ["Half Day - Morning","Half Day - Morning"], ["Half Day - Afternoon","Half Day - Afternoon"]]}]',
        'Leave'
        );
SQL;

        return $this->executeQuery($sql);
    }

    public function down()
    {
        return true;
    }

}
