<?php
if(!interface_exists('OvertimeReport')){
    include_once APP_BASE_PATH.'admin/reports/reportClasses/OvertimeReport.php';
}

class OvertimeSummaryReport extends OvertimeReport{
    protected function isAggregated(){
        return true;
    }
}