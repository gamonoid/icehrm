<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 10/3/17
 * Time: 5:54 PM
 */

namespace Data\Admin\Api;

interface DataImporter
{
    public function getResult();
    public function process($data, $dataImporterId);
}
