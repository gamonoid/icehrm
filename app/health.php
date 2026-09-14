<?php
/**
 * Liveness probe for the container healthcheck (Dockerfile) and the Render
 * health check (render.yaml healthCheckPath).
 *
 * It answers one question: is this installation serving PHP? A 200 here means
 * nginx accepted the request and php-fpm executed a script, which is what the
 * orchestrator needs to know before it routes traffic to the container.
 *
 * Deliberately does NOT touch the database or config.php:
 *
 *   - A fresh deployment has no config.php until someone runs the installer, and
 *     on Render the health check has to pass before anyone can reach the
 *     installer at all. Requiring config would deadlock the deploy.
 *   - A brief database outage would otherwise mark the container unhealthy and
 *     have it restarted or pulled out of the load balancer, turning a recoverable
 *     blip into an outage. Database reachability belongs in monitoring, not in a
 *     liveness probe.
 *
 * Unauthenticated by necessity, so the response carries no version, path or
 * configuration detail that is not already public.
 */

header('Content-Type: application/json');
// Probes must never be answered from a cache, upstream or otherwise.
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

http_response_code(200);
echo json_encode(array('status' => 'ok'));
