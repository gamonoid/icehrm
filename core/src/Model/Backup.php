<?php

namespace Model;

use Classes\ModuleAccess;

/**
 * A backup record (database now; files later) created from the Connection
 * ("System Status") admin module. The backup file itself lives under
 * app/data/db_backups; this row holds its metadata. Rows are managed through
 * Connection\Common\BackupService — not the generic model API.
 */
class Backup extends BaseModel
{
    public $table = 'Backups';

    public function getAdminAccess()
    {
        return array("get", "element");
    }

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('connection', 'admin'),
        ];
    }
}
