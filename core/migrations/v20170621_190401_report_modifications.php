<?php
namespace Classes\Migration;
class v20170621_190401_report_modifications extends AbstractMigration{

    public function up(){

        $sql = <<<'SQL'
        REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
    ('Employee Leave Entitlement', 'This report list employees leave entitlement for current leave period by department or by employee ',
     '[[ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true, "null-label":"All Departments","validation":"none"}],\r\n[ "employee", {"label":"Employee","type":"select2","allow-null":true, "null-label":"All Employees", "validation":"none","remote-source":["Employee","id","first_name+last_name"]}]]',
     'EmployeeLeaveEntitlementReport',
     '["department","employee"]', 'Class','Leave Management','CSV');

SQL;


        return $this->executeQuery($sql);
    }

    public function down(){

    }

}
