<?php
namespace Classes\Migration;

/**
 * UI wording: the "Users" admin menu is now "Logins & Permissions" (a user is the
 * login of an employee). Rename the menu label unless the customer changed it.
 */
class v20261124_100000_rename_users_to_logins extends AbstractMigration
{
    public function up()
    {
        $this->executeQuery(
            "UPDATE Modules SET label = 'Logins & Permissions' WHERE name = 'users' AND mod_group = 'admin' AND label = 'Users'"
        );
        return true;
    }

    public function down()
    {
        $this->executeQuery(
            "UPDATE Modules SET label = 'Users' WHERE name = 'users' AND mod_group = 'admin' AND label = 'Logins & Permissions'"
        );
        return true;
    }
}
