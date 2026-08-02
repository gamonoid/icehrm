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

        // Step 1: Run core migrations first (from core/migrations/)
        BaseService::getInstance()->getMigrationManager()->ensureMigrations();

        // Step 2: Run extension migrations (registered via registerExtensionMigration)
        $migrations = BaseService::getInstance()->getExtensionMigrations();
        BaseService::getInstance()->getMigrationManager()->ensureExtensionMigrations($migrations);

        // Step 3: Run pro migrations AFTER core migrations (from extensions/leave_and_performance/migrations/)
        // Pro migrations may depend on tables/columns created by core migrations
        if (class_exists('ProModuleInitializer')) {
            \ProModuleInitializer::runMigrations();
        }

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
