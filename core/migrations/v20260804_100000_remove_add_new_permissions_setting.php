<?php
namespace Classes\Migration;

/**
 * Retire the meta.json module-permissions feature on existing installations.
 *
 * The permission blocks were removed from every module's meta.json, the
 * createPermissions() seeder in core/modules.php is gone, the Permission model class
 * and the admin/permissions module were deleted, and the remaining permission checks
 * in the app were collapsed to always-allowed. This migration cleans up the leftover
 * data for installs that already have it (fresh installs no longer seed any of it):
 *
 *   - the 'System: Add New Permissions' setting,
 *   - the admin/permissions Modules row,
 *   - the Permissions table itself.
 */
class v20260804_100000_remove_add_new_permissions_setting extends AbstractMigration
{
    public function up()
    {
        $ok = true;
        $ok = $this->executeQuery(
            "DELETE FROM Settings WHERE name = 'System: Add New Permissions';"
        ) && $ok;
        $ok = $this->executeQuery(
            "DELETE FROM Modules WHERE name = 'permissions' AND mod_group = 'admin';"
        ) && $ok;
        // No FK references Permissions, so it can be dropped outright.
        $ok = $this->executeQuery("DROP TABLE IF EXISTS `Permissions`;") && $ok;
        return $ok;
    }

    public function down()
    {
        return true;
    }
}
