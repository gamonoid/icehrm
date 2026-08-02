<?php

namespace Connection\Admin\Api;

use Classes\BaseService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Connection\Common\BackupService;

/**
 * REST endpoints for the Connection ("System Status") module — currently the
 * Backups tab. Registered from ConnectionAdminManager::setupRestEndPoints().
 * All endpoints are Admin-only.
 */
class ConnectionApiController extends IceApiController
{
    public function registerEndPoints()
    {
        // Server backup capabilities (e.g. whether file backups are possible).
        self::register(REST_API_PATH . 'connection/backup-capabilities', self::GET, function () {
            if (!self::requireAdmin()) {
                return;
            }
            (new RestEndPoint())->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
                // File backups require the PHP Zip extension.
                'zip_available' => class_exists('\\ZipArchive'),
                // Password-protected (AES-256) zips also need libzip encryption.
                'zip_encryption' => defined('\\ZipArchive::EM_AES_256'),
            )));
        });

        // List backups.
        self::register(REST_API_PATH . 'connection/backups', self::GET, function () {
            if (!self::requireAdmin()) {
                return;
            }
            $service = new BackupService();
            (new RestEndPoint())->sendResponse(
                new IceResponse(IceResponse::SUCCESS, $service->listBackups())
            );
        });

        // Create a backup. body: { type: 'database'|'files', name?, password? }.
        // 'database' requires a password (used to encrypt the JSON); 'files' does not.
        self::register(REST_API_PATH . 'connection/backups', self::POST, function () {
            if (!self::requireAdmin()) {
                return;
            }
            $user = BaseService::getInstance()->getCurrentUser();
            $body = json_decode(file_get_contents('php://input'), true);
            $type = is_array($body) && isset($body['type']) ? $body['type'] : 'database';
            $password = is_array($body) && isset($body['password']) ? $body['password'] : '';
            $name = is_array($body) && isset($body['name']) ? $body['name'] : '';

            $service = new BackupService();
            if ($type === 'files') {
                $resp = $service->createFilesBackup($name, $user->id, $password);
            } else {
                $resp = $service->createDatabaseBackup($password, $name, $user->id);
            }
            (new RestEndPoint())->sendResponse($resp);
        });

        // Download a backup file (raw stream — handled inside the service).
        self::register(REST_API_PATH . 'connection/backups/(:num)/download', self::GET, function ($id) {
            if (!self::requireAdmin(true)) {
                return;
            }
            (new BackupService())->streamBackup($id);
        });

        // Delete a backup.
        self::register(REST_API_PATH . 'connection/backups/(:num)', self::DELETE, function ($id) {
            if (!self::requireAdmin()) {
                return;
            }
            (new RestEndPoint())->sendResponse(
                (new BackupService())->deleteBackup($id)
            );
        });
    }

    /**
     * Ensure the caller is an Admin. On failure emits a 403 (JSON, or bare for
     * the download stream) and returns false.
     */
    private static function requireAdmin($raw = false)
    {
        $user = BaseService::getInstance()->getCurrentUser();
        if (!empty($user) && isset($user->user_level) && $user->user_level === 'Admin') {
            return true;
        }
        if ($raw) {
            http_response_code(403);
            exit;
        }
        (new RestEndPoint())->sendResponse(
            new IceResponse(IceResponse::ERROR, 'Admin access required.', 403)
        );
        return false;
    }
}
