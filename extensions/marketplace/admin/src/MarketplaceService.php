<?php

namespace MarketplaceAdmin;

use Classes\DatabaseCache;
use Utils\LogManager;

class MarketplaceService
{
    const CACHE_KEY_MODULES = 'marketplace:modules';
    const CACHE_TTL_3_HOURS = 10800;
    private static $instance = null;

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
     * Get modules from the marketplace API
     *
     * @param bool $forceRefresh Force refresh from API, bypassing cache
     * @return array|null Returns array of modules or null on failure
     */
    public function getModules($forceRefresh = false)
    {
        $cache = DatabaseCache::getInstance();

        // Check cache first (unless force refresh)
        if (!$forceRefresh) {
            $cached = $cache->get(self::CACHE_KEY_MODULES);
            if ($cached !== null) {
                return $cached;
            }
        }

        // Fetch from API
        $url = APP_WEB_URL . '/sapi/modules';
        $response = $this->makeRequest($url);

        if ($response === null) {
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            LogManager::getInstance()->error('MarketplaceService: Failed to parse JSON response');
            return null;
        }

        // Cache the response for 3 hours
        $cache->set(self::CACHE_KEY_MODULES, $data, self::CACHE_TTL_3_HOURS);

        return $data;
    }

    /**
     * Get a specific module by ID from the marketplace API
     *
     * @param string $moduleId The module ID
     * @return array|null Returns module data or null on failure
     */
    public function getModule($moduleId)
    {
        $url = APP_WEB_URL . 'sapi/modules/' . urlencode($moduleId);
        $response = $this->makeRequest($url);

        if ($response === null) {
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            LogManager::getInstance()->error('MarketplaceService: Failed to parse JSON response for module: ' . $moduleId);
            return null;
        }

        return $data;
    }

    /**
     * Make an HTTP GET request to the API
     *
     * @param string $url The URL to request
     * @return string|null Returns response body or null on failure
     */
    private function makeRequest($url)
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => \Classes\BaseService::shouldVerifyOutboundTls(),
            CURLOPT_SSL_VERIFYHOST => \Classes\BaseService::shouldVerifyOutboundTls() ? 2 : 0,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            LogManager::getInstance()->error('MarketplaceService: cURL error - ' . $error);
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            LogManager::getInstance()->error('MarketplaceService: HTTP error ' . $httpCode . ' for URL: ' . $url);
            return null;
        }

        return $response;
    }
}
