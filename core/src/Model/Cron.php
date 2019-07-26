<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:36 AM
 */

namespace Model;

/**
 * Class Cron
 * @package Model
 *
 * @property int $id
 * @property string $name
 * @property string $class
 * @property int $frequency
 * @property int $time
 * @property string $type
 * @property string $status
 */
class Cron extends BaseModel
{
    public $table = 'Crons';

    public function getAdminAccess()
    {
        return array();
    }

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }
}
