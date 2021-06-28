<?php

namespace Classes\Migration;

class v20210606_290001_update_s3_config extends AbstractMigration
{

    public function up()
    {

        $sql = <<<'SQL'
UPDATE `Settings`
    SET name = 'Files: Amazon S3 Secret for File Upload'
WHERE name = 'Files: Amazone S3 Secret for File Upload';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
UPDATE `Settings`
    SET category = 'System'
WHERE name like 'Files:%';
SQL;

        return $this->executeQuery($sql);
    }

    public function down()
    {
        return true;
    }
}
