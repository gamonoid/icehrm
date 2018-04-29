<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:52 PM
 */

namespace Reports\Admin\Api;

interface CSVReportBuilderInterface
{
    public function getData($report, $request);
    public function createReportFile($report, $data);
    public function getMainQuery();
    public function getWhereQuery($request);
}
