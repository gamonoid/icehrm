<?php

namespace Classes\Migration;

class v20260704_100000_leave_reason_required extends AbstractProMigration
{
    public function up(): bool
    {
        // Idempotent guard: MySQL 5.7 has no "ADD COLUMN IF NOT EXISTS".
        $rows = $this->db()->Execute(
            "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'LeaveTypes'
               AND COLUMN_NAME = 'reason_required'"
        );

        if (!empty($rows) && (int) $rows[0]['cnt'] > 0) {
            return true;
        }

        $sql = "ALTER TABLE `LeaveTypes`
                ADD COLUMN `reason_required` enum('Yes','No') DEFAULT 'No' NULL
                AFTER `attachment_mandatory`";

        return $this->executeQuery($sql);
    }

    public function down(): bool
    {
        // We don't drop columns on down migration for safety
        return true;
    }
}
