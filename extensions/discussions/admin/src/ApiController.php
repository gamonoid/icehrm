<?php

namespace DiscussionsAdmin;

use Classes\BaseService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use DiscussionsAdmin\Common\Model\DiscussionChannel;

/**
 * Channel administration. Every endpoint here is admin-only: the user levels a
 * channel admits are exactly what these calls edit, so nobody below Admin may touch
 * them. The module menu is admin-only too, but the REST surface is what actually
 * matters and is checked on every call.
 */
class ApiController extends IceApiController
{
    const LEVELS = array('Admin', 'Manager', 'Employee');

    public function registerEndPoints()
    {
        self::register(REST_API_PATH . 'discussions-admin/channels', self::GET, function ($pathParams = null) {
            $rest = new RestEndPoint();
            if (!$this->requireAdmin($rest)) {
                return;
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->listChannels()));
        });

        self::register(REST_API_PATH . 'discussions-admin/channels', self::POST, function ($pathParams = null) {
            $rest = new RestEndPoint();
            if (!$this->requireAdmin($rest)) {
                return;
            }
            $this->saveChannel($rest, new DiscussionChannel());
        });

        // Update. POST as well as PUT — the SPA's IceApiClient has no put().
        $updateChannel = function ($id) {
            $rest = new RestEndPoint();
            if (!$this->requireAdmin($rest)) {
                return;
            }
            $channel = new DiscussionChannel();
            $channel->Load('id = ?', array($id));
            if (empty($channel->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Channel not found', 404));
                return;
            }
            $this->saveChannel($rest, $channel);
        };
        self::register(REST_API_PATH . 'discussions-admin/channels/(:num)', self::PUT, $updateChannel);
        self::register(REST_API_PATH . 'discussions-admin/channels/(:num)', self::POST, $updateChannel);

        self::register(REST_API_PATH . 'discussions-admin/channels/(:num)', self::DELETE, function ($id) {
            $rest = new RestEndPoint();
            if (!$this->requireAdmin($rest)) {
                return;
            }
            $channel = new DiscussionChannel();
            $channel->Load('id = ?', array($id));
            if (empty($channel->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Channel not found', 404));
                return;
            }
            if (!empty($channel->is_system)) {
                $rest->sendResponse(new IceResponse(
                    IceResponse::ERROR,
                    'The ' . $channel->name . ' channel is built in and cannot be deleted',
                    400
                ));
                return;
            }
            // Posts, replies and attachment rows go with the channel (cascading FKs);
            // the attachment files on disk are cleaned up one post at a time.
            $this->deleteChannelFiles($channel->id);
            $channel->Delete();
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array('deleted' => (int) $id)));
        });
    }

    private function requireAdmin(RestEndPoint $rest)
    {
        $user = BaseService::getInstance()->getCurrentUser();
        $level = empty($user) ? '' : str_replace('Restricted ', '', (string) $user->user_level);
        if ($level !== 'Admin') {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Administrators only', 403));
            return false;
        }
        return true;
    }

    private function listChannels()
    {
        // MyORM's Execute() returns the rows as an array of assoc arrays (not an
        // ADOdb recordset), true for a statement with no result set, false on error.
        $db = BaseService::getInstance()->getDB();
        $counts = array();
        $rows = $db->Execute('SELECT channel, COUNT(*) AS n, MAX(created) AS last_post FROM DiscussionPosts GROUP BY channel');
        foreach (is_array($rows) ? $rows : array() as $row) {
            $counts[(int) $row['channel']] = array(
                'posts' => (int) $row['n'],
                'last_post' => $row['last_post'],
            );
        }

        $model = new DiscussionChannel();
        $rows = $model->Find('1 = 1 ORDER BY is_system DESC, name ASC', array());
        $out = array();
        foreach ($rows as $row) {
            $id = (int) $row->id;
            $out[] = $this->formatChannel($row, isset($counts[$id]) ? $counts[$id] : null);
        }
        return $out;
    }

    private function formatChannel($row, $stats)
    {
        return array(
            'id' => (int) $row->id,
            'name' => $row->name,
            'description' => $row->description,
            'is_system' => !empty($row->is_system),
            'read_levels' => $this->parseLevels($row->read_levels),
            'post_levels' => $this->parseLevels($row->post_levels),
            'reply_levels' => $this->parseLevels($row->reply_levels),
            'posts' => $stats ? $stats['posts'] : 0,
            'last_post' => $stats ? $this->iso($stats['last_post']) : null,
            'created' => $row->created,
            'updated' => $row->updated,
        );
    }

    /**
     * Create or update from the request body: {name, description, read_levels[],
     * post_levels[], reply_levels[]}. Admin is always present in every list.
     */
    private function saveChannel(RestEndPoint $rest, DiscussionChannel $channel)
    {
        $body = $rest->getRequestBody();
        if (!is_array($body)) {
            $body = array();
        }

        $name = isset($body['name']) ? trim(strip_tags((string) $body['name'])) : '';
        if ($name === '') {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'A channel name is required', 400));
            return;
        }
        if (mb_strlen($name) > 100) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Channel names are limited to 100 characters', 400));
            return;
        }

        // Unique, case-insensitively — two channels differing only by case would be
        // indistinguishable in the sidebar.
        $dup = new DiscussionChannel();
        $dup->Load('LOWER(name) = LOWER(?)', array($name));
        if (!empty($dup->id) && (empty($channel->id) || (int) $dup->id !== (int) $channel->id)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'A channel called "' . $name . '" already exists', 400));
            return;
        }

        $now = date('Y-m-d H:i:s');
        $isNew = empty($channel->id);
        $channel->name = $name;
        $channel->description = isset($body['description']) ? trim(strip_tags((string) $body['description'])) : '';
        $channel->read_levels = $this->toCsv(isset($body['read_levels']) ? $body['read_levels'] : self::LEVELS);
        $channel->post_levels = $this->toCsv(isset($body['post_levels']) ? $body['post_levels'] : self::LEVELS);
        $channel->reply_levels = $this->toCsv(isset($body['reply_levels']) ? $body['reply_levels'] : self::LEVELS);
        $channel->updated = $now;
        if ($isNew) {
            $channel->is_system = 0;
            $channel->created = $now;
            $user = BaseService::getInstance()->getCurrentUser();
            $channel->created_by = empty($user) ? null : $user->id;
        }

        if (!$channel->Save()) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Could not save the channel', 500));
            return;
        }

        $rest->sendResponse(new IceResponse(
            IceResponse::SUCCESS,
            $this->formatChannel($channel, null),
            $isNew ? 201 : null
        ));
    }

    private function parseLevels($csv)
    {
        $out = array();
        foreach (explode(',', (string) $csv) as $part) {
            $part = trim($part);
            if (in_array($part, self::LEVELS, true) && !in_array($part, $out, true)) {
                $out[] = $part;
            }
        }
        return $out;
    }

    /** Array of levels -> canonical CSV; Admin is always included. */
    private function toCsv($levels)
    {
        $wanted = array('Admin');
        foreach ((array) $levels as $level) {
            $level = str_replace('Restricted ', '', trim((string) $level));
            if (in_array($level, self::LEVELS, true) && !in_array($level, $wanted, true)) {
                $wanted[] = $level;
            }
        }
        $ordered = array();
        foreach (self::LEVELS as $level) {
            if (in_array($level, $wanted, true)) {
                $ordered[] = $level;
            }
        }
        return implode(',', $ordered);
    }

    /** A stored "Y-m-d H:i:s" (application default zone) as an ISO-8601 instant with offset. */
    private function iso($stored)
    {
        if (empty($stored)) {
            return null;
        }
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', (string) $stored, new \DateTimeZone(date_default_timezone_get()));
        return $dt ? $dt->format(\DateTime::ATOM) : null;
    }

    /** Remove the attachment files of every post in a channel before the rows cascade away. */
    private function deleteChannelFiles($channelId)
    {
        $db = BaseService::getInstance()->getDB();
        $rows = $db->Execute('SELECT id FROM DiscussionPosts WHERE channel = ?', array($channelId));
        if (!is_array($rows)) {
            return;
        }
        $fs = \Classes\FileService::getInstance();
        foreach ($rows as $row) {
            $fs->deleteFilesForObject('DiscussionPost', (int) $row['id']);
        }
    }
}
