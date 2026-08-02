<?php
namespace Classes;

use Utils\SessionUtils;

/**
 * Global authentication gate for the REST API.
 *
 * Historically only endpoints that called RestEndPoint::process() validated the
 * bearer token themselves; extension endpoints registered via IceApiController
 * ran their callbacks with no authentication at all. This gate is invoked from
 * core/api-url-based.php and core/api-rest.php BEFORE the router dispatches, so
 * every endpoint is protected by default — including ones added in the future.
 *
 * Authentication accepted:
 *   1. A valid bearer access token (same validation as RestEndPoint::process()).
 *   2. An already authenticated PHP session (legacy admin/user web UI calls).
 *
 * Endpoints that must stay public (login, careers page) are declared in
 * $publicRoutes below, or registered at runtime via allowPublic().
 */
class RestApiAuthGate extends RestEndPoint
{
    /**
     * Routes reachable without authentication: [HTTP method, route pattern].
     * Patterns support the router placeholders (:num), (:any) and (:all).
     * Keep this list as small as possible.
     */
    private static $publicRoutes = [
        ['GET', 'echo'],              // connectivity check, returns a random number
        ['POST', 'oauth/token'],      // token issuance (login) — must be public
        ['GET', 'jobs'],              // public careers page: list open positions
        ['GET', 'jobs/(:num)'],       // public careers page: job details
        ['POST', 'jobs/apply'],       // public careers page: submit application
    ];

    /**
     * Allow an additional route to be accessed without authentication.
     * Call during endpoint registration (setupRestEndPoints / registerEndPoints).
     */
    public static function allowPublic($httpMethod, $path)
    {
        self::$publicRoutes[] = [strtoupper($httpMethod), trim($path, '/')];
    }

    /**
     * Validate the request or terminate it with a 401 JSON response.
     *
     * @param string $uri raw request uri ($_GET['url'] or REQUEST_URI path)
     * @param string $method HTTP method of the request
     */
    public static function enforce($uri, $method)
    {
        if (self::isPublic($uri, $method)) {
            return;
        }

        $gate = new self();

        // 1) Bearer token — the standard REST authentication.
        // A malformed token must read as "invalid", not crash the request
        // (JWT::decode throws UnexpectedValueException on garbage input).
        try {
            $validation = $gate->validateAccessToken();
        } catch (\Throwable $e) {
            $validation = null;
        }
        if (!empty($validation)
            && $validation->getStatus() === IceResponse::SUCCESS
            && !empty($validation->getData()->id)
        ) {
            BaseService::getInstance()->setCurrentUser($validation->getData());
            SessionUtils::saveSessionObject('user', $validation->getData());
            return;
        }

        // 2) Fall back to an authenticated web session (legacy UI calls)
        $sessionUser = SessionUtils::getSessionObject('user');
        if (!empty($sessionUser) && !empty($sessionUser->id)) {
            BaseService::getInstance()->setCurrentUser($sessionUser);
            return;
        }

        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'error' => [[['code' => 401, 'message' => 'Authentication required']]],
        ]);
        exit;
    }

    /**
     * Check the request against the public route allowlist.
     */
    private static function isPublic($uri, $method)
    {
        $path = self::normalize($uri);
        $method = strtoupper($method);

        $searches = array_keys(IceRoute::$patterns);
        $replaces = array_values(IceRoute::$patterns);

        foreach (self::$publicRoutes as $route) {
            if ($route[0] !== $method) {
                continue;
            }
            $pattern = $route[1];
            if (strpos($pattern, ':') !== false) {
                $pattern = str_replace($searches, $replaces, $pattern);
            }
            if (preg_match('#^' . $pattern . '$#', $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Strip the script directory and REST_API_PATH prefixes so the same
     * allowlist works for both entry points (url-based and path-based).
     */
    private static function normalize($uri)
    {
        $uri = (string)parse_url($uri, PHP_URL_PATH);

        $scriptDir = dirname($_SERVER['PHP_SELF']);
        if ($scriptDir !== '/' && $scriptDir !== '' && strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }

        if (defined('REST_API_PATH') && REST_API_PATH !== '/' && strpos($uri, REST_API_PATH) === 0) {
            $uri = substr($uri, strlen(REST_API_PATH));
        }

        return trim($uri, '/');
    }
}
