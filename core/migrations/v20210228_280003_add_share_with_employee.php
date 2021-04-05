<?php

namespace Classes\Migration;

class v20210228_280003_add_share_with_employee extends AbstractMigration
{

  public function up()
  {

    $sql = <<<'SQL'
ALTER TABLE `Documents`
ADD COLUMN `share_with_employee` enum('Yes','No') NULL DEFAULT 'Yes' AFTER `updated`;
SQL;
    $this->executeQuery($sql);

    return $this->executeQuery($sql);
  }

  public function down()
  {
    return true;
  }
}
