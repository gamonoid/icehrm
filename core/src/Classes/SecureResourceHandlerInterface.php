<?php

namespace Classes;

use Model\SecureResource;

/**
 * A handler for a hash-protected direct link (app/secure/). The full
 * application (core + every extension) is bootstrapped before handle() is
 * called, and the resource's hash has already been verified. handle() decides
 * what the anonymous visitor sees — it should echo its output (HTML, a file
 * stream, a redirect, ...) directly.
 */
interface SecureResourceHandlerInterface
{
    /**
     * @param SecureResource $resource the verified resource row; use
     *        json_decode($resource->data, true) for the stored parameters.
     */
    public function handle(SecureResource $resource);
}
