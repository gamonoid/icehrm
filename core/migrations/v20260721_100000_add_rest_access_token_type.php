<?php
namespace Classes\Migration;

use Classes\BaseService;

/**
 * Add a `type` to RestAccessTokens so a user can hold separate tokens for the
 * web SPA session ('Web', revoked on logout) and long-lived API/mobile access
 * ('FullAPI', unaffected by web logout). Existing tokens become 'FullAPI'.
 * The previous UNIQUE(userId) becomes UNIQUE(userId, type).
 */
class v20260721_100000_add_rest_access_token_type extends AbstractMigration
{
    public function up()
    {
        $db = BaseService::getInstance()->getDB();

        $hasType = $db->Execute(
            "SELECT COUNT(*) c FROM information_schema.COLUMNS "
            . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'RestAccessTokens' AND COLUMN_NAME = 'type'"
        );
        if (empty($hasType[0]['c'])) {
            $this->executeQuery(
                "ALTER TABLE RestAccessTokens ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'FullAPI'"
            );
        }
        // Every pre-existing token is a full API token.
        $this->executeQuery("UPDATE RestAccessTokens SET type = 'FullAPI' WHERE type IS NULL OR type = ''");

        // Replace UNIQUE(userId) with UNIQUE(userId, type) so both token types
        // can coexist for a user.
        $hasUserIdx = $db->Execute(
            "SELECT COUNT(*) c FROM information_schema.STATISTICS "
            . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'RestAccessTokens' AND INDEX_NAME = 'userId'"
        );
        if (!empty($hasUserIdx[0]['c'])) {
            $this->executeQuery("ALTER TABLE RestAccessTokens DROP INDEX userId");
        }
        $hasComposite = $db->Execute(
            "SELECT COUNT(*) c FROM information_schema.STATISTICS "
            . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'RestAccessTokens' AND INDEX_NAME = 'userId_type'"
        );
        if (empty($hasComposite[0]['c'])) {
            $this->executeQuery("ALTER TABLE RestAccessTokens ADD UNIQUE KEY userId_type (userId, type)");
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
