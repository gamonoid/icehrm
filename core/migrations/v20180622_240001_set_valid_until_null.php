<?php
namespace Classes\Migration;

use Model\Setting;
use Modules\Common\Model\Module;

class v20180622_240001_set_valid_until_null extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        Alter table EmployeeDocuments modify column `valid_until` date NULL;
SQL;
        return $this->executeQuery($sql);

    }

    public function down(){

        return true;
    }

}
