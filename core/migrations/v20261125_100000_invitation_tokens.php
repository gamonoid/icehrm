<?php
namespace Classes\Migration;

use Model\Setting;

/**
 * Invitations get expiring, single-use tokens and the invitee sets their own password
 * (no password is emailed any more). Employees gain the 'Onboarding' status, used by
 * new hires between accepting an onboarding invitation and their start date.
 */
class v20261125_100000_invitation_tokens extends AbstractMigration
{
    public function up()
    {
        $columns = [
            'token_hash' => "ALTER TABLE `UserInvitations` ADD COLUMN `token_hash` char(64) NULL",
            'expires_at' => "ALTER TABLE `UserInvitations` ADD COLUMN `expires_at` DATETIME NULL",
            'accepted_at' => "ALTER TABLE `UserInvitations` ADD COLUMN `accepted_at` DATETIME NULL",
            'context' => "ALTER TABLE `UserInvitations` ADD COLUMN `context` varchar(30) NULL",
            'context_ref' => "ALTER TABLE `UserInvitations` ADD COLUMN `context_ref` bigint(20) NULL",
            'email_status' => "ALTER TABLE `UserInvitations` ADD COLUMN `email_status` int(4) NULL",
            'last_sent_at' => "ALTER TABLE `UserInvitations` ADD COLUMN `last_sent_at` DATETIME NULL",
        ];
        foreach ($columns as $name => $sql) {
            if (!$this->hasColumn('UserInvitations', $name)) {
                $this->executeQuery($sql);
            }
        }
        if (!$this->hasIndex('UserInvitations', 'KEY_UserInvitations_token_hash')) {
            $this->executeQuery("ALTER TABLE `UserInvitations` ADD INDEX `KEY_UserInvitations_token_hash` (`token_hash`)");
        }

        // Links already sent keep working: the old link carries the value stored in
        // `password`, so its hash becomes the token hash. They expire in 14 days.
        $this->executeQuery(
            "UPDATE `UserInvitations` SET `token_hash` = SHA2(`password`, 256),"
            . " `expires_at` = DATE_ADD(NOW(), INTERVAL 14 DAY)"
            . " WHERE `token_hash` IS NULL AND `invitation_status` <= 2"
        );

        $this->executeQuery(
            "ALTER TABLE `Employees` MODIFY `status` enum('Active','Terminated','Onboarding') default 'Active'"
        );

        $this->addSetting();

        return true;
    }

    private function hasColumn($table, $column)
    {
        // information_schema rather than "SHOW COLUMNS ... LIKE ?": SHOW statements
        // cannot take a bound parameter here, and Execute() returns an array of rows.
        $rows = $this->db()->Execute(
            'SELECT COUNT(*) AS c FROM information_schema.COLUMNS '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );
        return is_array($rows) && !empty($rows[0]['c']);
    }

    private function hasIndex($table, $index)
    {
        $rows = $this->db()->Execute(
            'SELECT COUNT(*) AS c FROM information_schema.STATISTICS '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$table, $index]
        );
        return is_array($rows) && !empty($rows[0]['c']);
    }

    private function addSetting()
    {
        $setting = new Setting();
        $setting->Load('name = ?', array('System: Invitation Link Validity (Days)'));
        if (!empty($setting->id)) {
            return;
        }
        $setting->name = 'System: Invitation Link Validity (Days)';
        $setting->value = '14';
        $setting->description = 'How many days an invitation link stays valid. After that, resend the invitation to send a new link.';
        $setting->meta = '';
        $setting->category = 'System';
        $setting->setting_group = null;
        $setting->setting_order = 0;
        $setting->Save();
    }

    public function down()
    {
        return true;
    }
}
