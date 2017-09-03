<?php
namespace Reports\Admin\Reports;

class OvertimeSummaryReport extends OvertimeReport
{
    protected function isAggregated()
    {
        return true;
    }
}
