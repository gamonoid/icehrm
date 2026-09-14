<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:37 AM
 */

namespace Settings\Admin\Api;

use Classes\AbstractInitialize;
use Classes\BaseService;
use Classes\Migration\MigrationRunner;
use Classes\IceResponse;
use Classes\RestApiManager;
use Classes\SettingsManager;
use Users\Common\Model\User;
use Utils\LogManager;

class SettingsInitialize extends AbstractInitialize
{

    public function init()
    {
        // Run migrations BEFORE any feature code that reads migrated schema. On a
        // fresh install the base schema (icehrmdb.sql) predates later columns
        // (e.g. RestAccessTokens.type), and the REST token setup below queries
        // that column — so migrations must be applied first, otherwise the first
        // authenticated request fatals with "Unknown column 'type'".
        //
        // The core/extension/pro sequence now lives in MigrationRunner, which is also
        // called on every successful login regardless of user level — this used to be
        // the ONLY trigger, so a Manager or Employee logging in after an upgrade met
        // the new code against the old schema. Both paths share one implementation and
        // one advisory lock, and it is a no-op if the other has already run.
        MigrationRunner::runAll();

        // REST API token setup — uses RestAccessTokens.type added by the migration above.
        if (SettingsManager::getInstance()->getSetting("Api: REST Api Enabled") == "1") {
            $user = BaseService::getInstance()->getCurrentUser();
            if (empty($user)) {
                return;
            }
            $dbUser = new User();
            $dbUser->Load("id = ?", array($user->id));
            if (!empty($dbUser->id) && $dbUser->id == $user->id) {
                $resp = RestApiManager::getInstance()->getAccessTokenForUser($dbUser);
                if ($resp->getStatus() != IceResponse::SUCCESS) {
                    LogManager::getInstance()->error(
                        "Error occurred while creating REST Api access token for ".$user->username
                    );
                }
            }
        }
    }
}
