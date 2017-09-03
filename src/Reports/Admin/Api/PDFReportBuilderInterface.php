<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:53 PM
 */

namespace Reports\Admin\Api;

interface PDFReportBuilderInterface
{
    public function getData($report, $request);
    public function createReportFile($report, $data);
    public function getTemplate();
}
