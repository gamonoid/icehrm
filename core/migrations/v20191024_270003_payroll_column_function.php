<?php
namespace Classes\Migration;

class v20191024_270003_payroll_column_function extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
Alter Table PayrollColumns modify column calculation_function TEXT null;
SQL;

        $this->executeQuery($sql);

        $sql = <<<'SQL'
Alter Table PayrollColumns add column function_type ENUM('Simple', 'Advanced') DEFAULT 'Simple';
SQL;

        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
