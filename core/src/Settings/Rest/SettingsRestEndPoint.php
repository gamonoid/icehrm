<?php


namespace Settings\Rest;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Classes\SettingsManager;
use Classes\UIManager;
use Model\Setting;
use Users\Common\Model\User;
use Utils\SessionUtils;

class SettingsRestEndPoint extends RestEndPoint
{
    public function getMobileSettings()
    {
        $sm = SettingsManager::getInstance();
        $data = [
            'Company: Logo' => UIManager::getInstance()->getCompanyLogoUrl(),
            'Company: Name' => $sm->getSetting('Company: Name'),
            'Attendance: Request Attendance Location on Mobile' =>
                $sm->getSetting('Attendance: Request Attendance Location on Mobile'),
        ];

        return new IceResponse(IceResponse::SUCCESS, ['data' => $data]);
    }

    /**
     * Save a setting value
     * POST /api/settings/save
     * Body: { "id": 123, "value": "new value" }
     * Supports both token-based and session-based authentication
     */
    public function save(User $user = null)
    {
        // If user is not provided or doesn't have a valid id, try to get from session (for admin UI calls)
        if (!$user || empty($user->id) || !isset($user->user_level)) {
            $sessionUser = SessionUtils::getSessionObject('user');
            if ($sessionUser && !empty($sessionUser->id) && isset($sessionUser->user_level)) {
                $user = $sessionUser;
                BaseService::getInstance()->setCurrentUser($user);
            } else {
                return new IceResponse(IceResponse::ERROR, "Authentication required", 401);
            }
        }

        if ($user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }

        $body = $this->getRequestBody();
        
        if (empty($body['id'])) {
            return new IceResponse(IceResponse::ERROR, "Setting ID is required", 400);
        }

        if (!isset($body['value'])) {
            return new IceResponse(IceResponse::ERROR, "Setting value is required", 400);
        }

        // Use BaseService to save the setting
        $response = BaseService::getInstance()->addElement('Setting', [
            'id' => $body['id'],
            'value' => $body['value'],
        ]);

        if ($response->getStatus() === IceResponse::SUCCESS) {
            $setting = $response->getData();
            // Clean up the setting object for response
            $cleanSetting = BaseService::getInstance()->cleanUpAll($setting);
            return new IceResponse(IceResponse::SUCCESS, $cleanSetting, 200);
        }

        return new IceResponse(IceResponse::ERROR, $response->getData(), 400);
    }
}
