<?php
namespace Classes\Migration;

use Classes\BaseService;

/**
 * Seed a strong (256-bit) random signing secret into the existing SystemData table.
 * It is used to derive APP_SEC (see server.includes.inc.php) when APP_SEC is not
 * already defined by a global config file — replacing the previous weak derivation
 * sha1(instanceId . instanceKey), where instanceId was a low-entropy md5(time()).
 *
 * Generated once and never overwritten. Seeding here (rather than lazily on first
 * request) makes creation deterministic and race-free. Existing access tokens are
 * invalidated when APP_SEC changes and are re-minted on next login.
 */
class v20260725_100000_seed_signing_secret extends AbstractMigration
{
    public function up()
    {
        $db = BaseService::getInstance()->getDB();

        $exists = $db->Execute(
            "SELECT COUNT(*) c FROM SystemData WHERE name = 'Instance: Signing Secret'"
        );
        if (empty($exists[0]['c'])) {
            // Hex only ([0-9a-f]) — safe to inline; stored raw as a string so
            // BaseService::getSystemData() returns it verbatim.
            $secret = bin2hex(random_bytes(32));
            $this->executeQuery(
                "INSERT INTO SystemData (name, value) VALUES ('Instance: Signing Secret', '" . $secret . "')"
            );
        }

        return true;
    }

    public function down()
    {
        return true;
    }
}
