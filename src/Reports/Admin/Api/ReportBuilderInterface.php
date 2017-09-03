<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:52 PM
 */

namespace Reports\Admin\Api;

interface ReportBuilderInterface
{
    public function getData($report, $request);
    public function createReportFile($report, $data);
}
