<?php
namespace Connection\Common;

use Classes\BaseService;
use Classes\SettingsManager;
use Classes\StatsHelper;
use GuzzleHttp\Client;
use Users\Common\Model\User;

class ConnectionService
{
    public function getInstallationData()
    {
        $proKey = '';
        if (class_exists('\\Classes\\ProVersion')) {
            $data = \Classes\ProVersion::$data;
            $data = json_decode($data, true);
            $proKey = $data['key'];
        }

        return [
            'id' => BaseService::getInstance()->getInstanceId(),
            'secret' => md5(BaseService::getInstance()->getInstanceKey()),
            'external_ip' => $this->getExternalIP(),
            'internal_ip' => $_SERVER['SERVER_ADDR'],
            'employees' => StatsHelper::getEmployeeCount(),
            'users' => StatsHelper::getUserCount(),
            'version' => VERSION,
            'company' => SettingsManager::getInstance()->getSetting('Company: Name'),
            'pro_key' => $proKey,
            'url' => CLIENT_BASE_URL,
        ];
    }

    public function getSystemReport()
    {
        return [
            [
                'name' => 'Installation ID',
                'value' => BaseService::getInstance()->getInstanceId(),
            ],
            [
                'name' => 'PHP Version',
                'value' => phpversion(),
            ],
            [
                'name' => 'PHP Extensions',
                'value' => join(', ', get_loaded_extensions()),
            ],
            [
                'name' => 'Web Server',
                'value' => $_SERVER['SERVER_SOFTWARE'],
            ],
            [
                'name' => 'MySQL Server',
                //'value' => mysqli_get_server_info((new User())->DB()->_connectionID),
                'value' => BaseService::getInstance()->getDB()->getServerInfo(),
            ],
            [
                'name' => 'Modules Loaded',
                'value' => join(', ', array_keys(BaseService::getInstance()->getModuleManagerNames())),
            ]
        ];
    }

    public function getSystemErrors()
    {
        $errors = [];
        $res = fopen(BaseService::getInstance()->getDataDirectory().'connection_test.txt', "w");

        if (false === $res) {
            $errors[] = [
                'type' => 'error',
                'message' =>
                    'Data directory is not writable. Please make sure php can has write access to <icehrm>/app/data',
            ];
        } else {
            fwrite($res, date('Y-m-d'));
            $file = CLIENT_BASE_URL.'data/connection_test.txt';
            $file_headers = @get_headers($file);
            $status = explode(' ', $file_headers[0]);
            if ($file_headers && count($status) > 1 && $status[1] != '404' && $status[1] != '403') {
                $errors[] = [
                    'type' => 'error',
                    'link' => 'https://icehrm.gitbook.io/icehrm/getting-started/securing-icehrm-installation',
                    'linkText' => 'Learn how to fix',
                    'message' => 'Data directory is accessible from outside.',
                ];
            }
        }

        return $errors;
    }

    public function dispatchInstallationRequest()
    {
        $timeNow = time();
        $time = BaseService::getInstance()->getSystemData('sysDataTime');
        if (null === $time) {
            BaseService::getInstance()->setSystemData('sysDataTime', $timeNow);
            return true;
        }

        $time = intval($time);
        if ($timeNow > $time + 3600) {
            BaseService::getInstance()->setSystemData('sysDataTime', $timeNow);
            return true;
        }

        return false;
    }

    public function getExternalIP()
    {
        try {
            $externalContent = file_get_contents('http://checkip.dyndns.com/');
            preg_match('/Current IP Address: \[?([:.0-9a-fA-F]+)\]?/', $externalContent, $m);
            return $m[1];
        } catch (\Exception $e) {
        }

        return null;
    }

    public function reportInstallationData()
    {
        try {
            $client = new Client();
            $response = $client->request('POST', APP_WEB_URL . '/sapi/installtion-data', [
                'json' => $this->getInstallationData(),
            ]);
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }
}
