<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:44 AM
 */

namespace Classes\Migration;

abstract class AbstractMigration
{
    protected $file;

    private $db;

    protected $lastError;

    public function __construct($file)
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
            $this->db = NewADOConnection('mysqli');
            $res = $this->db->Connect(APP_HOST, APP_USERNAME, APP_PASSWORD, APP_DB);
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
        }
        return $ret;
    }
}
