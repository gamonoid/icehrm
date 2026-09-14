<?php

namespace Model;

use Classes\ModuleAccess;

/**
 * A hash-protected direct link to an internal HR resource/action, served by
 * app/secure/ without a login. `handler_class` is a fully-qualified class name
 * (core or extension) whose handle($resource) decides what the visitor sees.
 * Rows are created through Classes\SecureResourceService — not the generic API.
 */
class SecureResource extends BaseModel
{
    public $table = 'SecureResources';

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
            new ModuleAccess('settings', 'admin'),
        ];
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
