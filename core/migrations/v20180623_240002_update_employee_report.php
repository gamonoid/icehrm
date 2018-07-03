<?php
namespace Classes\Migration;

use Model\Report;

class v20180623_240002_update_employee_report extends AbstractMigration{

    public function up(){

        $report = new Report();
        $report->Load('name = ?', array('Employee Details Report'));
        $report->parameters = '[["department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}],["employment_status", {"label":"Employment Status","type":"select2","remote-source":["EmploymentStatus","id","name"],"allow-null":true}],["job_title", {"label":"Job Title","type":"select2","remote-source":["JobTitle","id","name"],"allow-null":true}]]';
        $report->query = 'EmployeeDetailsReport';
        $report->type = 'Class';
        $report->paramOrder = '["department","employment_status","job_title"]';
        $report->report_group = 'Employee Information';
        $report->output = 'CSV';
        $ok = $report->Save();

        return true;

    }

    public function down(){

        return true;
    }

}
