<?php
namespace Classes\Migration;

class v20191024_270002_report_migrations extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
  ('Employee Leaves Report', 'This report list all employee leaves by employee, date range and leave status', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}],\r\n[ "status", {"label":"Leave Status","type":"select","source":[["NULL","All Statuses"],["Approved","Approved"],["Pending","Pending"],["Rejected","Rejected"],["Cancellation Requested","Cancellation Requested"],["Cancelled","Cancelled"]]}]\r\n]', 'EmployeeLeavesReport', '["employee","date_start","date_end","status"]', 'Class','Leave Management','CSV'),
  ('Employee Leave Entitlement', 'This report list employees leave entitlement for current leave period by department or by employee ',
   '[[ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true,"validation":"none"}],\r\n[ "employee", {"label":"Employee","type":"select2","allow-null":true,"validation":"none","remote-source":["Employee","id","first_name+last_name"]}]]',
   'EmployeeLeaveEntitlementReport',
   '["department","employee"]', 'Class','Leave Management','CSV'),
    ('Expense Report', 'This report list employees expenses for a specified period',
   '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}],\r\n[ "status", {"label":"Status","type":"select","source":[["NULL","All Statuses"],["Approved","Approved"],["Pending","Pending"],["Rejected","Rejected"],["Cancellation Requested","Cancellation Requested"],["Cancelled","Cancelled"]]}]\r\n]',
   'ExpenseReport',
   '["employee","date_start","date_end","status"]', 'Class','Travel and Expense Management','CSV');
SQL;

        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
