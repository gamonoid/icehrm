<?php
namespace MarketplaceAdmin;

use Classes\BaseService;
use Classes\DatabaseCache;
use Classes\IceResponse;

class ExtensionData {

	const SYSTEM_DATA_KEY_MY_EXTENSIONS = 'marketplace:my_extensions';
	const CACHE_KEY_LICENSE_PREFIX = 'marketplace:license:';
	const CACHE_TTL_1_DAY = 86400;

	/**
	 * Verify that user has an active license for the given extension
	 *
	 * @param string $code The extension directory/code to verify
	 * @param string $message Custom message describing the blocked action
	 * @return IceResponse SUCCESS if licensed, ERROR with message if not
	 */
	public static function verify($code, $message) {
		if (defined('IS_CLOUD') && true === IS_CLOUD) {
			return new IceResponse(IceResponse::SUCCESS);
		}
		$res = self::hasActiveLicense($code);
		if (!$res) {
			$errorMessage = "License Required: {$message} Your license for this extension has expired or is not active. Please purchase a new license or renew your existing license to continue. View your licenses at System > Marketplace > My Purchases.";
			return new IceResponse(IceResponse::ERROR, $errorMessage);
		}
		return new IceResponse(IceResponse::SUCCESS);
	}

	/**
	 * Check if user has an active (non-expired) license for a given extension code
	 *
	 * @param string $code The extension directory/code to check
	 * @return bool Returns true if user has an active license, false otherwise
	 */
	public static function hasActiveLicense($code)
	{
		if (empty($code)) {
			return false;
		}

		$cache = DatabaseCache::getInstance();
		$cacheKey = self::CACHE_KEY_LICENSE_PREFIX . $code;

		// Check cache first for previously found active license
		$cached = $cache->get($cacheKey);
		if ($cached !== null) {
			return $cached === true;
		}

		// Get extensions from SystemData
		$extensions = BaseService::getInstance()->getSystemData(self::SYSTEM_DATA_KEY_MY_EXTENSIONS);


		if (empty($extensions) || !is_array($extensions)) {
			return true;
		}

		// Look through all licenses for an active one matching the code
		$hasActive = false;
		foreach ($extensions as $extension) {
			if (!is_array($extension)) {
				continue;
			}

			$directory = $extension['directory'] ?? '';
			$isExpired = $extension['is_expired'] ?? true;

			// Check if directory matches and license is not expired
			if ($directory === $code && $isExpired === false) {
				$hasActive = true;
				break;
			}
		}

		// Cache the result for 1 day
		if ( $hasActive ) {
			$cache->set($cacheKey, $hasActive, self::CACHE_TTL_1_DAY);
		}

		return $hasActive;
	}

	/**
	 * Get the active license details for a given extension code
	 *
	 * @param string $code The extension directory/code to check
	 * @return array|null Returns the active license data or null if not found
	 */
	public static function getActiveLicense($code)
	{
		if (empty($code)) {
			return null;
		}

		// Get extensions from SystemData
		$extensions = BaseService::getInstance()->getSystemData(self::SYSTEM_DATA_KEY_MY_EXTENSIONS);

		if (empty($extensions) || !is_array($extensions)) {
			return null;
		}

		// Look through all licenses for an active one matching the code
		foreach ($extensions as $extension) {
			if (!is_array($extension)) {
				continue;
			}

			$directory = $extension['directory'] ?? '';
			$isExpired = $extension['is_expired'] ?? true;

			// Check if directory matches and license is not expired
			if ($directory === $code && $isExpired === false) {
				return $extension;
			}
		}

		return null;
	}
}
