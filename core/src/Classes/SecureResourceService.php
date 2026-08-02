<?php

namespace Classes;

use Model\SecureResource;

/**
 * Create and verify hash-protected direct links to internal HR resources
 * (served by app/secure/?resource=<id>&hash=<hash> without a login).
 */
class SecureResourceService
{
    private static $instance = null;

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new SecureResourceService();
        }
        return self::$instance;
    }

    /**
     * Create a secure resource.
     *
     * @param string $handlerClass fully-qualified handler class name (must
     *        implement Classes\SecureResourceHandlerInterface)
     * @param array  $data parameters the handler needs (stored as JSON)
     * @return SecureResource|null the saved resource (with resource_id + hash)
     */
    public function createResource($handlerClass, $data = [])
    {
        $resource = new SecureResource();
        $resource->resource_id = bin2hex(random_bytes(16));
        $resource->hash = bin2hex(random_bytes(32));
        $resource->handler_class = $handlerClass;
        $resource->data = json_encode($data);
        $resource->status = 'Active';
        $resource->created = date('Y-m-d H:i:s');
        if (!$resource->Save()) {
            return null;
        }
        return $resource;
    }

    /**
     * The public URL for a resource, e.g.
     * http://host/app/secure/?resource=<id>&hash=<hash>
     */
    public function getUrl(SecureResource $resource)
    {
        return CLIENT_BASE_URL . 'secure/?resource=' . urlencode($resource->resource_id)
            . '&hash=' . urlencode($resource->hash);
    }

    /**
     * Load + verify a resource by id and hash. Returns the resource or null
     * when unknown, disabled, or the hash does not match.
     */
    public function verify($resourceId, $hash)
    {
        if (empty($resourceId) || empty($hash)) {
            return null;
        }
        $resource = new SecureResource();
        $resource->Load('resource_id = ?', [$resourceId]);
        if (empty($resource->id) || $resource->status !== 'Active') {
            return null;
        }
        if (!hash_equals((string) $resource->hash, (string) $hash)) {
            return null;
        }
        return $resource;
    }

    /**
     * Disable a resource so its link stops working.
     */
    public function disable(SecureResource $resource)
    {
        $resource->status = 'Disabled';
        $resource->Save();
    }
}
