<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:31 AM
 */

namespace Classes;

interface HistoryManager
{
    public function addHistory($type, $refId, $field, $oldValue, $newValue);
}
