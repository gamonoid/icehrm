<?php
namespace Classes\Migration;

use Model\Setting;
use Modules\Common\Model\Module;

class v20180615_230402_remove_eh_manager extends AbstractMigration{

    public function up(){

        $module = new Module();
        $module->Load('name = ?', array('employeehistory'));
        $module->user_levels = json_encode(['Admin']);
        $module->Save();

        return true;

    }

    public function down(){

        return true;
    }

}
