<?php
namespace Classes\Migration;

use Model\Report;

class v20210402_280006_modify_attendance_rep extends AbstractMigration {

    public function up(){

        $report = new Report();
        $report->Load('name = ?', ['Employee Time Tracking Report']);
        $report->parameters = <<<'JSON'
[
[ "employee", {"label":"Employee","type":"select2","allow-null":false,"remote-source":["Employee","id","first_name+last_name"]}],
[ "date_start", {"label":"Start Date","type":"date", "validation":"none"}],
[ "date_end", {"label":"End Date","type":"date","validation":"none"}],
["period", { "label": "Period", "type": "select", "source": [["Current Month", "Current Month"], ["Last Month", "Last Month"], ["Last Week", "Last Week"], ["Last 2 Weeks", "Last 2 Weeks"]], "validation":"none" }]
]
JSON;
        $report->paramOrder = <<<'JSON'
["employee","date_start","date_end","period"]
JSON;

        $report->Save();
        return true;
    }

    public function down(){
        return true;
    }

}
