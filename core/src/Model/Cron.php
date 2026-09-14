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
 *
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

    /**

     * No module grants Employee access to this model (module meta.json user_levels),

     * so no employee-facing screen reads it. The inherited BaseModel default

     * would expose the whole table on the generic service.php path.

     */

    public function getUserOnlyMeAccess()

    {

        return array();

    }

}
