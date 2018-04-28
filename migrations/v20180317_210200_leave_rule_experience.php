<?php

namespace Classes\Migration;

class v20180317_210200_leave_rule_experience extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
        alter table LeaveRules add column `exp_days` int(11) NULL;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        $sql = <<<'SQL'
        alter table LeaveRules drop column `exp_days`;
SQL;

        return $this->executeQuery($sql);
    }

}
{

}
