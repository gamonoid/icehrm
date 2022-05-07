<?php

namespace Classes\Migration;

class v20210228_280004_add_visible_to_document extends AbstractMigration
{

  public function up()
  {

    $sql = <<<'SQL'
ALTER TABLE `EmployeeDocuments`
ADD COLUMN `visible_to`  enum('Owner','Manager','Admin') NULL DEFAULT 'Owner' AFTER `expire_notification_last`;
SQL;

    return $this->executeQuery($sql);
  }

  public function down()
  {
    return true;
  }
}
