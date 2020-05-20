<?php
namespace Classes\Migration;

class v20191024_270005_update_data_importers extends AbstractMigration {

    public function up(){

        $sql = <<<'SQL'
        Update DataImport set columns = '[{"name":"employee_id","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"Yes","id":"columns_7"},{"name":"first_name","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_3"},{"name":"middle_name","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_5"},{"name":"last_name","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_6"},{"name":"address1","title":"Address1","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_8"},{"name":"address2","title":"Address2","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_9"},{"name":"home_phone","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_14"},{"name":"mobile_phone","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_15"},{"name":"work_email","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_16"},{"name":"gender","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_17"},{"name":"marital_status","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_18"},{"name":"birthday","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_20"},{"name":"Nationality/nationality","title":"Nationality","type":"Reference","dependOn":"Nationality","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_22"},{"name":"Ethnicity/ethnicity","title":"Ethnicity","type":"Normal","dependOn":"Ethnicity","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_23"},{"name":"EmergencyContact/name","title":"","type":"Attached","dependOn":"EmergencyContact","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_24"},{"name":"EmergencyContact/relationship","title":"","type":"Attached","dependOn":"EmergencyContact","dependOnField":"relationship","isKeyField":"No","idField":"No","id":"columns_25"},{"name":"EmergencyContact/home_phone","title":"","type":"Attached","dependOn":"EmergencyContact","dependOnField":"home_phone","isKeyField":"No","idField":"No","id":"columns_26"},{"name":"ssn_num","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_31"},{"name":"job_title","title":"","type":"Reference","dependOn":"JobTitle","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_32"},{"name":"employment_status","title":"","type":"Reference","dependOn":"EmploymentStatus","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_33"},{"name":"joined_date","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_36"},{"name":"department","title":"","type":"Reference","dependOn":"CompanyStructure","dependOnField":"title","isKeyField":"Yes","idField":"No","id":"columns_38"}]' where name = 'Employee Data Import';
SQL;

        return $this->executeQuery($sql);
    }

    public function down(){

        return true;
    }

}
