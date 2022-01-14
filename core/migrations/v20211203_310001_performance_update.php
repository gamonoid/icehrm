<?php

namespace Classes\Migration;

class v20211203_310001_performance_update extends AbstractMigration
{

    public function up()
    {
        $sql = <<<'SQL'
ALTER table `ReviewFeedbacks` add column rating int(11) default 0;
SQL;

        return $this->executeQuery($sql);
    }

    public function down()
    {
        return true;
    }
}
