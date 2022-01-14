<?php

namespace Classes\Migration;

class v20210718_300001_add_data_dir_setting extends AbstractMigration
{

    public function up()
    {

        $sql = <<<'SQL'
REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
  ('System: Data Directory', '',  'Path to store data files on your server. e.g /user/local/data/','', 'System');
SQL;

        return $this->executeQuery($sql);
    }

    public function down()
    {
        return true;
    }
}