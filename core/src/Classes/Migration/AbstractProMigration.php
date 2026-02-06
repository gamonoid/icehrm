<?php
/**
 * Abstract Pro Migration
 *
 * Base class for all pro migrations.
 */

namespace Classes\Migration;

use Classes\BaseService;
use Utils\LogManager;

abstract class AbstractProMigration
{
    /** @var string|null */
    protected $file;

    /** @var mixed */
    private $db;

    /** @var string|null */
    protected $lastError = null;

    /**
     * @param string|null $file
     */
    public function __construct($file = null)
    {
        $this->file = $file;
    }

    /**
     * @return bool
     */
    public function up()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function down()
    {
        return true;
    }

    /**
     * @return mixed
     */
    protected function db()
    {
        if ($this->db === null) {
            $this->db = BaseService::getInstance()->getDB();
        }
        return $this->db;
    }

    /**
     * @return string|null
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * @param string $sql
     * @return bool
     */
    public function executeQuery($sql)
    {
        try {
            $ret = $this->db()->Execute($sql);
            if (!$ret) {
                $this->lastError = $this->db()->ErrorMsg();
                LogManager::getInstance()->error('Error in pro migration: ' . $this->lastError);
            }
            return (bool) $ret;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            LogManager::getInstance()->error('Error in pro migration: ' . $e->getMessage());
        }

        return false;
    }
}
