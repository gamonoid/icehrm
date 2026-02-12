<?php

namespace Classes;

use Utils\LogManager;

class ConnectionService
{
    private static $instance = null;
    const SYSTEM_DATA_KEY = 'marketplace:connection';
    const CACHE_KEY_MY_EXTENSIONS = 'marketplace:my_extensions';
    const SYSTEM_DATA_KEY_MY_EXTENSIONS = 'marketplace:my_extensions';
    const CACHE_TTL_3_HOURS = 10800;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the installation URL
     *
     * @return string
     */
    public function getInstallationUrl()
    {
        // Get the base URL without the path component
        $baseUrl = BASE_URL;
        // Remove 'web/' or similar endings to get the root URL
        $installationUrl = preg_replace('/web\/?$/', '', $baseUrl);

        return rtrim($installationUrl, '/');
    }

    /**
     * Get the OAuth authorization URL
     *
     * @return string
     */
    public function getAuthorizeUrl()
    {
        $installationUrl = $this->getInstallationUrl();
        return APP_WEB_URL . '/connect/authorize?installation_url=' . urlencode($installationUrl);
    }

    /**
     * Handle OAuth callback and exchange code for token (static method for external calls)
     *
     * @param string $code The authorization code
     * @return bool Returns true on success, false on failure
     */
    public static function handleOAuthCode($code)
    {
        $instance = self::getInstance();
        $result = $instance->exchangeCodeForToken($code);

        if ($result) {
            // Redirect to marketplace after successful connection
            header('Location: ' . $instance->getRedirectUrl());
            exit;
        }

        return false;
    }

    /**
     * Exchange authorization code for access token
     *
     * @param string $code The authorization code
     * @return array|null Returns connection data or null on failure
     */
    public function exchangeCodeForToken($code)
    {
        $installationUrl = $this->getInstallationUrl();
        $tokenUrl = APP_WEB_URL . '/sapi/connect/token?code=' . urlencode($code) . '&installation_url=' . urlencode($installationUrl);

        LogManager::getInstance()->info('ConnectionService: Requesting token from ' . $tokenUrl);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: IceHrm/1.0',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($response === false || $errno !== 0) {
            LogManager::getInstance()->error('ConnectionService: cURL error (' . $errno . ') - ' . $error);
            return null;
        }

        LogManager::getInstance()->info('ConnectionService: Response HTTP ' . $httpCode . ' - ' . substr($response, 0, 500));

        if ($httpCode < 200 || $httpCode >= 300) {
            LogManager::getInstance()->error('ConnectionService: HTTP error ' . $httpCode . ' - Response: ' . $response);
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            LogManager::getInstance()->error('ConnectionService: Failed to parse JSON response - ' . json_last_error_msg() . ' - Response: ' . $response);
            return null;
        }

        if (!isset($data['data'])) {
            LogManager::getInstance()->error('ConnectionService: Invalid response format - ' . $response);
            return null;
        }

        // Save connection data to system data
        $connectionData = $data['data'];
        $this->saveConnectionData($connectionData);

        LogManager::getInstance()->info('ConnectionService: Token exchange successful');

        return $connectionData;
    }

    /**
     * Save connection data to system data
     *
     * @param array $data Connection data
     * @return bool
     */
    public function saveConnectionData($data)
    {
        return BaseService::getInstance()->setSystemData(self::SYSTEM_DATA_KEY, $data);
    }

    /**
     * Get saved connection data
     *
     * @return array|null
     */
    public function getConnectionData()
    {
        return BaseService::getInstance()->getSystemData(self::SYSTEM_DATA_KEY);
    }

    /**
     * Check if installation is connected
     *
     * @return bool
     */
    public function isConnected()
    {
        $data = $this->getConnectionData();
        return !empty($data) && !empty($data['access_token']);
    }

    /**
     * Fetch my extensions from the server
     *
     * @param bool $forceRefresh Force refresh from server, bypassing cache
     * @return array|null Returns extensions data or null on failure
     */
    public function fetchMyExtensions($forceRefresh = false)
    {
        if (!$this->isConnected()) {
            return null;
        }

        $cache = DatabaseCache::getInstance();

        // Check cache first (unless force refresh)
        if (!$forceRefresh) {
            $cached = $cache->get(self::CACHE_KEY_MY_EXTENSIONS);
            if ($cached !== null) {
                return $cached;
            }
        }

        $connectionData = $this->getConnectionData();
        $accessToken = $connectionData['access_token'];
        $secret = $connectionData['secret'];

        $requestUrl = APP_WEB_URL . '/sapi/connect/my-extensions';
        $signature = hash_hmac('sha256', $requestUrl, $secret);

        LogManager::getInstance()->info('ConnectionService: Fetching my-extensions from ' . $requestUrl);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: IceHrm/1.0',
                'Authorization: Bearer ' . $accessToken,
                'X-Signature: ' . $signature,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($response === false || $errno !== 0) {
            LogManager::getInstance()->error('ConnectionService: my-extensions cURL error (' . $errno . ') - ' . $error);
            return null;
        }

        LogManager::getInstance()->info('ConnectionService: my-extensions response HTTP ' . $httpCode);

        if ($httpCode === 401) {
            LogManager::getInstance()->error('ConnectionService: my-extensions authentication failed');
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            LogManager::getInstance()->error('ConnectionService: my-extensions HTTP error ' . $httpCode);
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            LogManager::getInstance()->error('ConnectionService: my-extensions failed to parse JSON');
            return null;
        }

		$data = $data['data'];

        if (!isset($data['success']) || !$data['success']) {
            LogManager::getInstance()->error('ConnectionService: my-extensions request failed');
            return null;
        }

        $extensions = $data['extensions'] ?? [];

        // Cache the response for 3 hours
        $cache->set(self::CACHE_KEY_MY_EXTENSIONS, $extensions, self::CACHE_TTL_3_HOURS);

        // Also save to SystemData for persistence
        BaseService::getInstance()->setSystemData(self::SYSTEM_DATA_KEY_MY_EXTENSIONS, $extensions);

        return $extensions;
    }

    /**
     * Disconnect the installation
     *
     * @return array Result with 'success' boolean and 'message' string
     */
    public function disconnect()
    {
        $connectionData = $this->getConnectionData();
        $result = ['success' => false, 'message' => 'Disconnected from IceHrm'];

        // Try to notify the server about disconnection
        if ($connectionData && !empty($connectionData['access_token']) && !empty($connectionData['secret'])) {
            $serverResult = $this->notifyServerDisconnect($connectionData);
            $result = $serverResult;
        }

        // Always clear local connection data regardless of server response
        BaseService::getInstance()->setSystemData(self::SYSTEM_DATA_KEY, null);

        return $result;
    }

    /**
     * Notify the server about disconnection
     *
     * @param array $connectionData The stored connection data
     * @return array Result with 'success' boolean and 'message' string
     */
    private function notifyServerDisconnect($connectionData)
    {
        $requestUrl = APP_WEB_URL . '/sapi/connect/disconnect';
        $accessToken = $connectionData['access_token'];
        $secret = $connectionData['secret'];

        // Generate HMAC-SHA256 signature
        $signature = hash_hmac('sha256', $requestUrl, $secret);

        LogManager::getInstance()->info('ConnectionService: Disconnecting from ' . $requestUrl);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $requestUrl,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: IceHrm/1.0',
                'Authorization: Bearer ' . $accessToken,
                'X-Signature: ' . $signature,
            ],
            CURLOPT_POSTFIELDS => '{}',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($response === false || $errno !== 0) {
            LogManager::getInstance()->error('ConnectionService: Disconnect cURL error (' . $errno . ') - ' . $error);
            return ['success' => false, 'message' => 'Disconnected locally. Could not reach server.'];
        }

        LogManager::getInstance()->info('ConnectionService: Disconnect response HTTP ' . $httpCode . ' - ' . $response);

        if ($httpCode === 401) {
            return ['success' => false, 'message' => 'Disconnected locally. Server authentication failed.'];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['success' => false, 'message' => 'Disconnected locally. Server returned an error.'];
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => true, 'message' => 'Disconnected successfully.'];
        }

        if (isset($data['success']) && $data['success']) {
            return ['success' => true, 'message' => $data['message'] ?? 'Disconnected successfully.'];
        }

        return ['success' => true, 'message' => 'Disconnected successfully.'];
    }

    /**
     * Get the redirect URL after OAuth completion
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->getInstallationUrl() . '/app/?g=extension&n=marketplace|admin&m=admin_System';
    }
}
