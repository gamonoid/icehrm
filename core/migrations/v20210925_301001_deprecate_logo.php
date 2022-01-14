<?php

namespace Classes\Migration;

class v20210925_301001_deprecate_logo extends AbstractMigration
{

    public function up()
    {

        $sql = <<<'SQL'
DELETE FROM Settings where name = 'Company: Logo';
SQL;

        $this->executeQuery($sql);

        $sql = <<<'SQL'
UPDATE Settings set description = 'Update your company name here. For updating company logo copy a file named logo.png to icehrm_root/app/ folder' where name = 'Company: Name';
SQL;

        return $this->executeQuery($sql);
    }

    public function down()
    {
        return true;
    }
}