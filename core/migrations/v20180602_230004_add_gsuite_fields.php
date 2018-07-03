<?php
namespace Classes\Migration;

use Model\Setting;

class v20180602_230004_add_gsuite_fields extends AbstractMigration{

    public function up(){

        $setting = new Setting();
        $setting->Load("name = ?", array('System: G Suite Enabled'));
        if(empty($setting->id)){
            $setting->name = 'System: G Suite Enabled';
            $setting->value = 0;
            $setting->description = 'If you want to allow users to login via G Suite accounts';
            $setting->meta = '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]';
            $setting->Save();
        }

        $setting = new Setting();
        $setting->Load("name = ?", array('System: G Suite Disable Password Login'));
        if(empty($setting->id)){
            $setting->name = 'System: G Suite Disable Password Login';
            $setting->value = 0;
            $setting->description = 'If you want to allow users to login only via G Suite accounts';
            $setting->meta = '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]';
            $setting->Save();
        }


        $sql = <<<'SQL'
        Alter table Users add column `googleUserData` TEXT default NULL;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
