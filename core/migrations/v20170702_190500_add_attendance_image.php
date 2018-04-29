<?php
namespace Classes\Migration;

use Model\Setting;

class v20170702_190500_add_attendance_image extends AbstractMigration{

    public function up(){

        $setting = new Setting();
        $setting->Load("name = ?", array('Attendance: Photo Attendance'));
        if(empty($setting->id)){
            $setting->name =   'Attendance: Photo Attendance';
            $setting->value = 0;
            $setting->description = 'Require submitting a photo using web cam when marking attendance';
            $setting->meta = '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]';
            $setting->Save();
        }


        $sql = <<<'SQL'
        Alter table Attendance add column `image_in` longtext default null;
SQL;


        return $this->executeQuery($sql);
    }

    public function down(){

        $sql = <<<'SQL'
        Alter table Attendance drop column `image_in`;
SQL;

        return $this->executeQuery($sql);
    }

}
