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
        if (SettingsManager::getInstance()->getSetting("Api: REST Api Enabled") == "1") {
            $user = BaseService::getInstance()->getCurrentUser();
            if (empty($user)) {
                return;
            }
            $dbUser = new User();
            $dbUser->Load("id = ?", array($user->id));
            $resp = RestApiManager::getInstance()->getAccessTokenForUser($dbUser);
            if ($resp->getStatus() != IceResponse::SUCCESS) {
                LogManager::getInstance()->error(
                    "Error occurred while creating REST Api access token for ".$user->username
                );
            }
        }

        BaseService::getInstance()->getMigrationManager()->ensureMigrations();
    }
}
