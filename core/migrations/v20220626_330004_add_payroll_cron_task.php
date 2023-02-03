<?php
namespace Classes\Migration;

use Model\Cron;

class v20220626_330004_add_payroll_cron_task extends AbstractMigration {

    public function up(){

        $cron = new Cron();
        $cron->Load('class = ?', ['PayrollProcessTask']);
        if (empty($cron->id)) {
            $cron->name = 'Payroll Processor';
            $cron->class = 'PayrollProcessTask';
            $cron->frequency = 1;
            $cron->time = 1;
            $cron->type = 'Minutely';
            $cron->status = 'Enabled';

            $cron->Save();
        }

        $sql = <<<'SQL'
ALTER TABLE `Payroll` MODIFY COLUMN `status` varchar(20) null;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE `Payroll` ADD COLUMN `error` varchar(100) null;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        $cron = new Cron();
        $cron->Load('class = ?', ['PayrollProcessTask']);

        return $cron->Delete();
    }

}
