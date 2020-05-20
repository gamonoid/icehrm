<?php
namespace Classes\Migration;

class v20200404_270006_password_rate_limit extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
ALTER TABLE Users ADD column `wrong_password_count` int(11) default 0;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Users ADD column `last_wrong_attempt_at` datetime default NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Users ADD column `last_password_requested_at` datetime default NULL;
SQL;
        return $this->executeQuery($sql);
    }

    public function down(){
        return true;
    }
}
