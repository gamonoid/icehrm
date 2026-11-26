<?php

namespace Reactions\Rest;

use Classes\BaseService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Reactions\ReactionService;

/**
 * REST surface for generic reactions (see ReactionService).
 *
 *   POST reactions/toggle            {target_type, target_id, emoji}
 *   GET  reactions/(:any)/(:num)     counts per emoji + the caller's own emojis
 *
 * Both require a signed-in user. toggle only ever touches the CALLER's own rows —
 * the reactor is derived server-side, never taken from the body.
 */
class ReactionRestEndPoint extends IceApiController
{
    public function registerEndPoints()
    {
        self::register(REST_API_PATH . 'reactions/toggle', self::POST, function ($pathParams = null) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $body = $rest->getRequestBody();
            if (!is_array($body)) {
                $body = array();
            }
            $type = isset($body['target_type']) ? $body['target_type'] : null;
            $id = isset($body['target_id']) ? (int) $body['target_id'] : 0;
            $emoji = ReactionService::cleanEmoji(isset($body['emoji']) ? $body['emoji'] : null);

            if (!ReactionService::isValidTargetType($type)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Unknown reaction target', 400));
                return;
            }
            if ($id <= 0) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'A target is required', 400));
                return;
            }
            if ($emoji === null) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'That is not a usable reaction', 400));
                return;
            }
            $token = ReactionService::reactorToken($user);
            if ($token === null) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Not authenticated', 401));
                return;
            }
            $rest->sendResponse(new IceResponse(
                IceResponse::SUCCESS,
                ReactionService::toggle($type, $id, $emoji, $token)
            ));
        });

        self::register(REST_API_PATH . 'reactions/(:any)/(:num)', self::GET, function ($type, $id) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            if (!ReactionService::isValidTargetType($type)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Unknown reaction target', 400));
                return;
            }
            $summary = ReactionService::summarize($type, array((int) $id), ReactionService::reactorToken($user));
            $rest->sendResponse(new IceResponse(
                IceResponse::SUCCESS,
                isset($summary[(int) $id]) ? $summary[(int) $id] : array('counts' => new \stdClass(), 'mine' => array())
            ));
        });
    }

    /**
     * The signed-in user, or answer 401 and return null. Prefers the user the request
     * bootstrap already resolved; falls back to validating the bearer token directly
     * so the endpoint also works when reached before that step.
     */
    private function requireUser(RestEndPoint $rest)
    {
        $user = BaseService::getInstance()->getCurrentUser();
        if (empty($user) || empty($user->id)) {
            $user = $rest->getCurrentUser();
        }
        if (empty($user) || empty($user->id)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Not authenticated', 401));
            return null;
        }
        return $user;
    }
}
