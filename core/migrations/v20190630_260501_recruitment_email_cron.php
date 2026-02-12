<?php
namespace Classes\Migration;

use Model\Cron;

class v20190630_260501_recruitment_email_cron extends AbstractMigration {

    public function up(){

        $cron = new Cron();
        $cron->name = 'Recruitment Email Sender';
        $cron->class = 'RecruitmentEmailTask';
        $cron->frequency = 1;
        $cron->time = 1;
        $cron->type = 'Minutely';
        $cron->status = 'Enabled';

        return $cron->Save();
    }

    public function down(){
        $cron = new Cron();
        $cron->Load('name = ?', ['Recruitment Email Sender']);

        return $cron->Delete();
    }

}
