<?php
namespace Reports\User\Reports;

class OvertimeSummaryReport extends OvertimeReport
{
    protected function isAggregated()
    {
        return true;
    }
}
