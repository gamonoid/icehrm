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

    public function getLastLogFileRows($limit = 1000)
    {
        $logFile = CLIENT_BASE_PATH . 'data/icehrm.log';
        $result = [];
        
        if (!file_exists($logFile) || !is_readable($logFile)) {
            return $result;
        }
        
        // Read the last N lines from the log file
        // For efficiency, read file in chunks from the end
        $handle = fopen($logFile, 'r');
        if (!$handle) {
            return $result;
        }
        
        // Get file size
        fseek($handle, 0, SEEK_END);
        $fileSize = ftell($handle);
        
        if ($fileSize == 0) {
            fclose($handle);
            return $result;
        }
        
        $lines = [];
        $currentLine = '';
        $chunkSize = 8192; // 8KB chunks
        $position = $fileSize;
        
        // Read backwards in chunks
        while ($position > 0 && count($lines) < $limit) {
            $readSize = min($chunkSize, $position);
            $position -= $readSize;
            
            fseek($handle, $position);
            $chunk = fread($handle, $readSize);
            
            // Process chunk backwards
            $chunkLines = explode("\n", $chunk);
            $chunkLines = array_reverse($chunkLines);
            
            // First chunk line might be incomplete, combine with currentLine
            if (!empty($currentLine)) {
                $chunkLines[0] = $chunkLines[0] . $currentLine;
            }
            
            // Last chunk line might be incomplete, save for next iteration
            $currentLine = array_pop($chunkLines);
            
            // Add complete lines
            foreach ($chunkLines as $line) {
                if (trim($line) !== '') {
                    array_unshift($lines, $line);
                    if (count($lines) >= $limit) {
                        break;
                    }
                }
            }
        }
        
        // Add the last line if exists
        if (!empty($currentLine) && count($lines) < $limit) {
            array_unshift($lines, $currentLine);
        }
        
        fclose($handle);
        
        // Take only the last $limit lines (these are the newest since we read backwards)
        $lines = array_slice($lines, -$limit);
        
        // Reverse array so newest lines appear first
        $lines = array_reverse($lines);
        
        // Format lines for display (newest first)
        foreach ($lines as $index => $line) {
            $result[] = [
                'id' => $index + 1,
                'line' => $line,
            ];
        }
        
        return $result;
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
            $response = $client->request(
                'POST',
                APP_WEB_URL . '/sapi/installtion-data',
                [
                'json' => $this->getInstallationData(),
                ]
            );
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }
}
