<?php
namespace MarketplaceAdmin;

use Classes\ConnectionService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;

class ApiController extends IceApiController
{
	public function registerEndPoints() {
        // Get marketplace modules
        self::register(
            REST_API_PATH . 'marketplace/modules', self::GET, function ($pathParams = null) {
                $response = MarketplaceService::getInstance()->getModules();
                // Extract just the data array from the response
                $modules = $response['data'] ?? [];
                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        $modules
                    )
                );
        });

        // Get installed extensions
        self::register(
            REST_API_PATH . 'marketplace/installed-extensions', self::GET, function ($pathParams = null) {
                $installedExtensions = InstalledExtensionsService::getInstance()->getInstalledExtensions();
                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        $installedExtensions
                    )
                );
        });

        // Get connection status
        self::register(
            REST_API_PATH . 'marketplace/connection', self::GET, function ($pathParams = null) {
                $connectionService = ConnectionService::getInstance();
                $isConnected = $connectionService->isConnected();
                $connectionData = $connectionService->getConnectionData();

                // Get core version and strip .PRO or .OS suffix
                $coreVersion = defined('VERSION') ? VERSION : '';
                $coreVersion = preg_replace('/\.(PRO|OS)$/', '', $coreVersion);

                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        [
                            'connected' => $isConnected,
                            'coreVersion' => $coreVersion,
                            'isPro' => (defined('IS_ICEHRM_PRO') && IS_ICEHRM_PRO),
                            'data' => $isConnected ? [
                                'client_name' => $connectionData['client_name'] ?? '',
                                'client_email' => $connectionData['client_email'] ?? '',
                            ] : null
                        ]
                    )
                );
        });

        // Debug/health stats for the connected icehrm.com server. Authenticated
        // by the stored connection credentials (Bearer access token + X-Signature),
        // NOT a user session — so it's allowlisted past the user auth gate and
        // self-authenticates in the handler.
        \Classes\RestApiAuthGate::allowPublic(self::GET, 'marketplace/debug-stats');
        self::register(
            REST_API_PATH . 'marketplace/debug-stats', self::GET, function ($pathParams = null) {
                $auth = self::readInboundAuthHeaders();
                if (!ConnectionService::getInstance()->verifyInboundRequest($auth['token'], $auth['signature'])) {
                    (new RestEndPoint())->sendResponse(
                        new IceResponse(IceResponse::ERROR, 'Unauthorized', 401)
                    );
                    return;
                }
                (new RestEndPoint())->sendResponse(
                    new IceResponse(IceResponse::SUCCESS, (new DebugStatsService())->getStats())
                );
        });

        // Get OAuth authorize URL
        self::register(
            REST_API_PATH . 'marketplace/connect', self::GET, function ($pathParams = null) {
                $connectionService = ConnectionService::getInstance();
                $authorizeUrl = $connectionService->getAuthorizeUrl();

                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        ['authorize_url' => $authorizeUrl]
                    )
                );
        });

        // Get my extensions
        self::register(
            REST_API_PATH . 'marketplace/my-extensions', self::GET, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();

                $connectionService = ConnectionService::getInstance();
                if (!$connectionService->isConnected()) {
                    $restEndpoint->sendResponse(
                        new IceResponse(IceResponse::ERROR, 'Not connected')
                    );
                    return;
                }

                $extensions = $connectionService->fetchMyExtensions(false);

                $restEndpoint->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        $extensions ?? []
                    )
                );
        });

        // Refresh my extensions (force refresh from server)
        self::register(
            REST_API_PATH . 'marketplace/my-extensions/refresh', self::GET, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();

                $connectionService = ConnectionService::getInstance();
                if (!$connectionService->isConnected()) {
                    $restEndpoint->sendResponse(
                        new IceResponse(IceResponse::ERROR, 'Not connected')
                    );
                    return;
                }

                $extensions = $connectionService->fetchMyExtensions(true);

                $restEndpoint->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        $extensions ?? []
                    )
                );
        });

        // Disconnect
        self::register(
            REST_API_PATH . 'marketplace/disconnect', self::POST, function ($pathParams = null) {
                $connectionService = ConnectionService::getInstance();
                $result = $connectionService->disconnect();

                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        [
                            'disconnected' => true,
                            'success' => $result['success'],
                            'message' => $result['message']
                        ]
                    )
                );
        });

        // Update extension
        // ---------------------------------------------------------------------
        // DISABLED: extension install / update over the API.
        //
        // Extensions are no longer installed or updated through the marketplace, so
        // this endpoint is switched off rather than left reachable. It was the only
        // route that fetched a remote archive and wrote executable PHP into the
        // application directory (ExtensionUpdateService::updateExtension ->
        // downloadExtension + extractAndInstall), which made it by far the highest-value
        // target in the API: anything able to influence the download or the archive
        // contents gets code execution. Removing the route removes that surface
        // entirely instead of relying on the TLS/zip-slip/checksum defences around it.
        //
        // The service class is intentionally left in place (unreferenced) so the
        // upgrade path can be restored deliberately if marketplace installs ever come
        // back. The admin UI still renders Install/Update buttons — see
        // web/js/view.js installOrUpdateExtension(), which posts here.
        // ---------------------------------------------------------------------
//         self::register(
//             REST_API_PATH . 'marketplace/update-extension', self::POST, function ($pathParams = null) {
//                 $restEndpoint = new RestEndPoint();
//                 $data = $restEndpoint->getRequestBody();
//
//                 $extensionName = $data['extensionName'] ?? null;
//                 $licenseKey = $data['licenseKey'] ?? null;
//
//                 if (!$extensionName || !$licenseKey) {
//                     $restEndpoint->sendResponse(
//                         new IceResponse(IceResponse::ERROR, 'Extension name and license key are required')
//                     );
//                     return;
//                 }
//
//                 $updateService = ExtensionUpdateService::getInstance();
//                 $result = $updateService->updateExtension($extensionName, $licenseKey);
//
//                 if (!$result['success']) {
//                     $restEndpoint->sendResponse(
//                         new IceResponse(IceResponse::ERROR, $result['error'])
//                     );
//                     return;
//                 }
//
//                 $restEndpoint->sendResponse(
//                     new IceResponse(
//                         IceResponse::SUCCESS,
//                         [
//                             'success' => true,
//                             'message' => 'Extension updated successfully',
//                             'backupPath' => $result['backupPath']
//                         ]
//                     )
//                 );
//         });

        // REST Api get request
        self::register(
            REST_API_PATH . 'marketplace/echo', self::GET, function ($pathParams = null) {
                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                    'Hello from MarketplaceAdmin extension'
                    )
                );
        });

        // REST Api get request with path parameters
        self::register(
            REST_API_PATH . 'marketplace/echo/(:any)', self::GET, function ($parameter) {
                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        'Hello from MarketplaceAdmin extension with data:'.$parameter
                    )
                );
        });

        // REST Api get request with two path parameters
        self::register(
            REST_API_PATH . 'marketplace/echo/(:any)/(:num)', self::GET, function ($parameter1, $parameter2) {
                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        IceResponse::SUCCESS,
                        'Hello from MarketplaceAdmin extension with parameter1:'.$parameter1.' and parameter2:'.$parameter2
                    )
                );
        });

        // REST Api post request
        self::register(
            REST_API_PATH . 'marketplace/echo', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $restEndpoint->sendResponse(
                new IceResponse(
                    IceResponse::SUCCESS,
                    'Hello from MarketplaceAdmin extension (POST request) with body:'.json_encode($data)
                )
            );
        });
	}

	/**
	 * Extract the inbound auth credentials for connection-authenticated calls:
	 * the Bearer token from Authorization and the X-Signature header. Reads from
	 * getallheaders() when available, falling back to $_SERVER (some SAPIs only
	 * expose one or the other).
	 *
	 * @return array{token:string,signature:string}
	 */
	private static function readInboundAuthHeaders() {
		$auth = '';
		$signature = '';
		if (function_exists('getallheaders')) {
			foreach (getallheaders() as $name => $value) {
				if (strcasecmp($name, 'Authorization') === 0) {
					$auth = trim($value);
				} elseif (strcasecmp($name, 'X-Signature') === 0) {
					$signature = trim($value);
				}
			}
		}
		if ($auth === '' && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
			$auth = trim($_SERVER['HTTP_AUTHORIZATION']);
		}
		if ($auth === '' && !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
			$auth = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
		}
		if ($signature === '' && !empty($_SERVER['HTTP_X_SIGNATURE'])) {
			$signature = trim($_SERVER['HTTP_X_SIGNATURE']);
		}

		$token = '';
		if ($auth !== '' && preg_match('/Bearer\s+(\S+)/i', $auth, $m)) {
			$token = $m[1];
		}
		return array('token' => $token, 'signature' => $signature);
	}
}

