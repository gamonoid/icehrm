<?php
namespace Classes\Migration;

use Data\Common\Model\DataImport;

class v20191118_270006_update_data_importers extends AbstractMigration {

    public function up(){

        $dataImport = new DataImport();
        $dataImport->Load('name = ?', ['Employee Data Import']);
        if (!empty($dataImport->id)) {
            $dataImport->columns = '[{"name":"employee_id","title":"Employee ID","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"EMP05011","help":"Employee ID","id":"columns_7"},{"name":"first_name","title":"First name","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"Chris","help":"First name","id":"columns_3"},{"name":"last_name","title":"Last name","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"Doe","help":"Last name","id":"columns_6"},{"name":"address1","title":"Address line 1","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"Abc Street","help":"Address line 1","id":"columns_8"},{"name":"address2","title":"Address line 2","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"10","help":"Address line 2","id":"columns_9"},{"name":"home_phone","title":"Home phone","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"+409 782324434","help":"Home phone","id":"columns_14"},{"name":"mobile_phone","title":"Mobile phone","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"+49 176 4545454545","help":"Mobile phone","id":"columns_15"},{"name":"work_email","title":"Work email","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"user@icehrm.com","help":"Work email","id":"columns_16"},{"name":"gender","title":"Gender","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"Male","help":"Allowed values (Male, Female)","id":"columns_17"},{"name":"marital_status","title":"Marital status","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"Single","help":"Marital status","id":"columns_18"},{"name":"birthday","title":"Birthday","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"2003-12-15","help":"Birthday","id":"columns_20"},{"name":"nationality","title":"Nationality","type":"Reference","dependOn":"Nationality","dependOnField":"name","idField":"No","sampleValue":"Austrian","help":"Name of a Nationality defined under System->Metadata","id":"columns_22"},{"name":"ethnicity","title":"Ethnicity","type":"Reference","dependOn":"Ethnicity","dependOnField":"name","idField":"No","sampleValue":"Asian American","help":"Name of an Ethnicity defined under System -> Metadata","id":"columns_23"},{"name":"ssn_num","title":"Social security number","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"34324903955WS","help":"Social security number","id":"columns_31"},{"name":"job_title","title":"Job title","type":"Reference","dependOn":"JobTitle","dependOnField":"name","idField":"No","sampleValue":"Software Engineer","help":"A Job title defined under Admin -> Job Details Setup","id":"columns_32"},{"name":"employment_status","title":"Employment status","type":"Reference","dependOn":"EmploymentStatus","dependOnField":"name","idField":"No","sampleValue":"Full Time","help":"Employment status defined under Admin -> Job Details","id":"columns_33"},{"name":"joined_date","title":"Joined date","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"2015-04-17","help":"Joined date (YYYY-MM-DD format)","id":"columns_36"},{"name":"department","title":"Department","type":"Reference","dependOn":"CompanyStructure","dependOnField":"title","idField":"No","sampleValue":"Head Office","help":"Name of a Department","id":"columns_38"}]';
            $dataImport->updated = date('Y-m-d H:i:s');
            $dataImport->Save();
        }

        $dataImport = new DataImport();
        $dataImport->Load('name = ?', ['Attendance Data Import']);
        if (!empty($dataImport->id)) {
            $dataImport->columns = '[{"name":"employee","title":"Employee","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"Yes","sampleValue":"EMP050","help":"Employee id of the employee of the attendance record","id":"columns_1"},{"name":"in_time","title":"In time","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"2019-11-06 08:15:00","help":"Time in format YYYY-MM-DD hh:mm:ss (use 24 hour time)","id":"columns_2"},{"name":"out_time","title":"Out time","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"2019-11-06 15:18:00","help":"Time in format YYYY-MM-DD hh:mm:ss (use 24 hour time)","id":"columns_3"},{"name":"note","title":"Note","type":"Normal","dependOn":"NULL","dependOnField":"NULL","idField":"No","sampleValue":"Leaving a bit early today","help":"Free text (optional)","id":"columns_4"}]';
            $dataImport->updated = date('Y-m-d H:i:s');
            $dataImport->Save();
        }

        $dataImport = new DataImport();
        $dataImport->Load('name = ?', ['Supervisor and Approver Import']);
        if (!empty($dataImport->id)) {
            $dataImport->columns = '[{"name":"employee_id","title":"Employee","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"Yes","sampleValue":"EMP050","help":"Id of the employee to update approver details","id":"columns_1"},{"name":"supervisor","title":"Supervisor","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"No","sampleValue":"EMP004","help":"Employee id of the supervisor","id":"columns_6"},{"name":"approver1","title":"Approver 1","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"No","sampleValue":"EMP001","help":"Employee id of the first approver (can be empty)","id":"columns_4"},{"name":"approver2","title":"Approver 2","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"No","sampleValue":"EMP002","help":"Employee id of the second approver (can be empty)","id":"columns_3"},{"name":"approver3","title":"Approver 3","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"No","sampleValue":"EMP003","help":"Employee id of the third approver (can be empty)","id":"columns_5"}]';
            $dataImport->updated = date('Y-m-d H:i:s');
            $dataImport->Save();
        } else {
            $dataImport->columns = '[{"name":"employee_id","title":"Employee","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"Yes","sampleValue":"EMP050","help":"Id of the employee to update approver details","id":"columns_1"},{"name":"supervisor","title":"Supervisor","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"No","sampleValue":"EMP004","help":"Employee id of the supervisor","id":"columns_6"},{"name":"approver1","title":"Approver 1","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"No","sampleValue":"EMP001","help":"Employee id of the first approver (can be empty)","id":"columns_4"},{"name":"approver2","title":"Approver 2","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"No","sampleValue":"EMP002","help":"Employee id of the second approver (can be empty)","id":"columns_3"},{"name":"approver3","title":"Approver 3","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","idField":"No","sampleValue":"EMP003","help":"Employee id of the third approver (can be empty)","id":"columns_5"}]';
            $dataImport->name = 'Supervisor and Approver Import';
            $dataImport->dataType = 'EmployeeDataImporter';
            $dataImport->details = '';
            $dataImport->updated = date('Y-m-d H:i:s');
            $dataImport->created = date('Y-m-d H:i:s');
            $dataImport->Save();
        }

        return true;
    }

    public function down(){

        return true;
    }

}
