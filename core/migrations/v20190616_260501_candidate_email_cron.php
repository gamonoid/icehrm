<?php
namespace Classes\Migration;

use Model\Cron;

class v20190616_260501_candidate_email_cron extends AbstractMigration {

    public function up(){

        $cron = new Cron();
        $cron->name = 'Candidate Email Sender';
        $cron->class = 'NewCandidateEmailTask';
        $cron->frequency = 1;
        $cron->time = 1;
        $cron->type = 'Minutely';
        $cron->status = 'Enabled';

        return $cron->Save();
    }

    public function down(){
        $cron = new Cron();
        $cron->Load('name = ?', ['Candidate Email Sender']);

        return $cron->Delete();
    }

}
