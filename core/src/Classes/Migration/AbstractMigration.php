<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:44 AM
 */

namespace Classes\Migration;

use Classes\BaseService;
use Utils\LogManager;

abstract class AbstractMigration
{
    protected $file;

    private $db;

    protected $lastError;

    public function __construct($file = null)
    {
        $this->file = $file;
    }

    public function up()
    {
        return true;
    }

    public function down()
    {
        return true;
    }

    protected function db()
    {
        if ($this->db == null) {
            $this->db = BaseService::getInstance()->getDB();
        }
        return $this->db;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function executeQuery($sql)
    {
        $ret = $this->db()->Execute($sql);
        if (!$ret) {
            $this->lastError =  $this->db()->ErrorMsg();
            LogManager::getInstance()->error('Error in migration: '.$this->lastError);
        }
        return $ret;
    }
}
