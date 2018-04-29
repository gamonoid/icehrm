<?php
namespace Classes\Migration;

use Model\Setting;

class v20170310_190401_add_timesheet_changes extends AbstractMigration{

    public function up(){
        $setting = new Setting();
        $setting->Load("name = ?", array('System: Time-sheet Entry Start and End time Required'));
        if(empty($setting->id)){
            $setting->name =   'System: Time-sheet Entry Start and End time Required';
            $setting->value = 1;
            $setting->description = 'Select 0 if you only need to store the time spend in time sheets';
            $setting->meta = '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]';
            $setting->Save();
        }
    }

    public function down(){

    }

}
