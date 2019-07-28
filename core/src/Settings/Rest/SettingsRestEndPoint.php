<?php


namespace Settings\Rest;

use Classes\IceResponse;
use Classes\RestEndPoint;
use Classes\SettingsManager;
use Classes\UIManager;

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
}
