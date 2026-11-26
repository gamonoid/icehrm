<?php

namespace InboxUser;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;
use InboxUser\Common\Model\InboxConversation;
use InboxUser\Common\Model\InboxMessage;
use Jobs\Common\Model\JobTitle;
use Model\File;
use Utils\LogManager;

/**
 * Direct messages between two employees.
 *
 * Identity is the EMPLOYEE profile, not the user account: a conversation is a pair of
 * employee ids, stored once with participant_a < participant_b. Every endpoint first
 * resolves the caller's profile and then checks they are one of the two participants
 * — nobody else, whatever their level, can read or write a conversation.
 *
 * Each participant's own state (hidden from their list; last message read) sits on
 * the conversation row in the _a / _b columns for their side.
 */
class ApiController extends IceApiController
{
    const PAGE_SIZE = 50;
    const MAX_BODY_CHARS = 5000;
    const SEARCH_LIMIT = 20;
    const SEARCH_MIN_CHARS = 2;

    const MAX_ATTACHMENTS = 5;
    const MAX_FILE_BYTES = 10485760; // 10 MB
    const FILE_GROUP = 'InboxAttachment';
    /** Same whitelist as Channels: nothing that can execute or render as HTML. */
    const ALLOWED_EXTENSIONS = array(
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'rtf', 'ppt', 'pptx',
        'jpg', 'jpeg', 'png', 'gif',
    );

    /** @var array employee id => formatted person, per request */
    private $people = array();

    public function registerEndPoints()
    {
        self::register(REST_API_PATH . 'inbox/me', self::GET, function ($p = null) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $out = $this->person($me);
            $out['timezone'] = $this->callerTimeZone();
            $out['allowed_extensions'] = self::ALLOWED_EXTENSIONS;
            $out['max_file_bytes'] = self::MAX_FILE_BYTES;
            $out['max_attachments'] = self::MAX_ATTACHMENTS;
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $out));
        });

        self::register(REST_API_PATH . 'inbox/conversations', self::GET, function ($p = null) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->listConversations($me)));
        });

        // The dashboard card: how much is unread, and up to five people, unread
        // first, then most recent.
        self::register(REST_API_PATH . 'inbox/summary', self::GET, function ($p = null) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $all = $this->listConversations($me);
            $unreadTotal = 0;
            foreach ($all as $c) {
                $unreadTotal += (int) $c['unread'];
            }
            usort($all, function ($a, $b) {
                if (($a['unread'] > 0) !== ($b['unread'] > 0)) {
                    return $a['unread'] > 0 ? -1 : 1;
                }
                return strcmp((string) $b['last_message_at'], (string) $a['last_message_at']);
            });
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
                'unread_total' => $unreadTotal,
                'conversations_total' => count($all),
                'conversations' => array_slice($all, 0, 5),
            )));
        });

        // Small and cheap: polled to keep the unread bubbles current.
        self::register(REST_API_PATH . 'inbox/unread', self::GET, function ($p = null) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $counts = $this->unreadCounts($me);
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
                'total' => array_sum($counts),
                'by_conversation' => $counts,
            )));
        });

        self::register(REST_API_PATH . 'inbox/employees/search', self::POST, function ($p = null) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $body = $rest->getRequestBody();
            $q = is_array($body) && isset($body['q']) ? trim((string) $body['q']) : '';
            if (mb_strlen($q) < self::SEARCH_MIN_CHARS) {
                $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array()));
                return;
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->searchEmployees($q, $me)));
        });

        // Open (or start) the conversation with an employee. Also un-hides it for me.
        self::register(REST_API_PATH . 'inbox/conversations', self::POST, function ($p = null) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $body = $rest->getRequestBody();
            $other = is_array($body) && isset($body['employee']) ? (int) $body['employee'] : 0;
            if ($other <= 0 || $other === $me) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Pick a colleague to message', 400));
                return;
            }
            $emp = new Employee();
            $emp->Load('id = ? AND status = ?', array($other, 'Active'));
            if (empty($emp->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'That employee was not found', 404));
                return;
            }
            $conv = $this->findOrCreateConversation($me, $other);
            $this->setHidden($conv, $me, false);
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->formatConversation($conv, $me, $this->unreadCounts($me))));
        });

        self::register(REST_API_PATH . 'inbox/conversations/(:num)/hide', self::POST, function ($id) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $conv = $this->loadMyConversation($rest, $id, $me);
            if ($conv === null) {
                return;
            }
            $this->setHidden($conv, $me, true);
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array('hidden' => (int) $id)));
        });

        // Messages, newest page first: /messages for the latest, /messages/before/{id}
        // for the ones older than a message already on screen. Fetching the latest page
        // marks the conversation read up to its newest message.
        $listMessages = function ($id, $beforeId = 0) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $conv = $this->loadMyConversation($rest, $id, $me);
            if ($conv === null) {
                return;
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->listMessages($conv, $me, (int) $beforeId)));
        };
        self::register(REST_API_PATH . 'inbox/conversations/(:num)/messages', self::GET, $listMessages);
        self::register(REST_API_PATH . 'inbox/conversations/(:num)/messages/before/(:num)', self::GET, $listMessages);

        // A file for a message about to be sent in this conversation. Parked against
        // the conversation until the message claims it.
        self::register(REST_API_PATH . 'inbox/conversations/(:num)/upload', self::POST, function ($id) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $conv = $this->loadMyConversation($rest, $id, $me);
            if ($conv === null) {
                return;
            }
            $this->handleUpload($rest, $me, $conv);
        });

        self::register(REST_API_PATH . 'inbox/conversations/(:num)/messages', self::POST, function ($id) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $conv = $this->loadMyConversation($rest, $id, $me);
            if ($conv === null) {
                return;
            }
            $body = $rest->getRequestBody();
            $text = $this->readBody($rest, true);
            if ($text === null) {
                return;
            }
            $names = is_array($body) && isset($body['attachments']) && is_array($body['attachments']) ? $body['attachments'] : array();
            if (count($names) > self::MAX_ATTACHMENTS) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'At most ' . self::MAX_ATTACHMENTS . ' files per message', 400));
                return;
            }
            $files = $this->resolveAttachments($names, $me, (int) $conv->id);
            if ($files === null) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'One of the attachments could not be found', 400));
                return;
            }
            if ($text === '' && empty($files)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'A message cannot be empty', 400));
                return;
            }

            $stored = array();
            foreach ($files as $f) {
                $stored[] = array(
                    'name' => $f->name,
                    'filename' => $f->filename,
                    'original' => $this->originalNameFor($f, $names),
                    'size_text' => $f->size_text,
                );
            }

            $now = date('Y-m-d H:i:s');
            $msg = new InboxMessage();
            $msg->conversation = $conv->id;
            $msg->sender = $me;
            $msg->body = $text;
            $msg->attachments = empty($stored) ? null : json_encode($stored, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $msg->created = $now;
            $msg->updated = $now;
            if (!$msg->Save()) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Could not send the message', 500));
                return;
            }
            // The files now belong to the message (see deleteFilesForObject on delete).
            foreach ($files as $f) {
                $f->object_type = 'InboxMessage';
                $f->object_id = $msg->id;
                $f->Save();
            }
            // The conversation surfaces for both sides again, and I have read my own message.
            $conv->last_message_id = $msg->id;
            $conv->last_message_at = $now;
            $conv->hidden_a = 0;
            $conv->hidden_b = 0;
            if ((int) $conv->participant_a === $me) {
                $conv->last_read_a = $msg->id;
            } else {
                $conv->last_read_b = $msg->id;
            }
            $conv->Save();
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->formatMessage($msg), 201));
        });

        self::register(REST_API_PATH . 'inbox/messages/(:num)', self::POST, function ($id) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $msg = $this->loadMyMessage($rest, $id, $me);
            if ($msg === null) {
                return;
            }
            $text = $this->readBody($rest);
            if ($text === null) {
                return;
            }
            $msg->body = $text;
            $msg->updated = date('Y-m-d H:i:s');
            $msg->Save();
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->formatMessage($msg)));
        });

        self::register(REST_API_PATH . 'inbox/messages/(:num)', self::DELETE, function ($id) {
            $rest = new RestEndPoint();
            $me = $this->requireEmployee($rest);
            if ($me === null) {
                return;
            }
            $msg = $this->loadMyMessage($rest, $id, $me);
            if ($msg === null) {
                return;
            }
            $convId = (int) $msg->conversation;
            FileService::getInstance()->deleteFilesForObject('InboxMessage', (int) $msg->id);
            $msg->Delete();
            // Keep the conversation's "latest" pointer honest if the last message went.
            $conv = new InboxConversation();
            $conv->Load('id = ?', array($convId));
            if (!empty($conv->id) && (int) $conv->last_message_id === (int) $id) {
                $rows = BaseService::getInstance()->getDB()->Execute(
                    'SELECT id, created FROM InboxMessages WHERE conversation = ? ORDER BY id DESC LIMIT 1',
                    array($convId)
                );
                $conv->last_message_id = (is_array($rows) && !empty($rows)) ? (int) $rows[0]['id'] : 0;
                $conv->last_message_at = (is_array($rows) && !empty($rows)) ? $rows[0]['created'] : null;
                $conv->Save();
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array('deleted' => (int) $id)));
        });
    }

    // ------------------------------------------------------------------ identity & access

    /** The caller's employee id, or a 401/403 and null. */
    private function requireEmployee(RestEndPoint $rest)
    {
        $user = BaseService::getInstance()->getCurrentUser();
        if (empty($user) || empty($user->id)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Not authenticated', 401));
            return null;
        }
        $me = BaseService::getInstance()->getCurrentProfileId();
        if (empty($me)) {
            $rest->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'Your account is not linked to an employee profile, so it cannot send or receive messages',
                403
            ));
            return null;
        }
        return (int) $me;
    }

    private function isParticipant($conv, $me)
    {
        return (int) $conv->participant_a === $me || (int) $conv->participant_b === $me;
    }

    private function otherParticipant($conv, $me)
    {
        return (int) $conv->participant_a === $me ? (int) $conv->participant_b : (int) $conv->participant_a;
    }

    private function loadMyConversation(RestEndPoint $rest, $id, $me)
    {
        $conv = new InboxConversation();
        $conv->Load('id = ?', array((int) $id));
        if (empty($conv->id) || !$this->isParticipant($conv, $me)) {
            // 404 either way: a non-participant is not told the conversation exists.
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Conversation not found', 404));
            return null;
        }
        return $conv;
    }

    /** A message the caller wrote (edit/delete are the author's alone). */
    private function loadMyMessage(RestEndPoint $rest, $id, $me)
    {
        $msg = new InboxMessage();
        $msg->Load('id = ?', array((int) $id));
        if (empty($msg->id)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Message not found', 404));
            return null;
        }
        $conv = new InboxConversation();
        $conv->Load('id = ?', array((int) $msg->conversation));
        if (empty($conv->id) || !$this->isParticipant($conv, $me)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Message not found', 404));
            return null;
        }
        if ((int) $msg->sender !== $me) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'You can only change your own messages', 403));
            return null;
        }
        return $msg;
    }

    /**
     * @param bool $allowEmpty a new message may be files only; an edit may not be blank
     * @return string|null the trimmed body, or null after sending a 400
     */
    private function readBody(RestEndPoint $rest, $allowEmpty = false)
    {
        $body = $rest->getRequestBody();
        $text = is_array($body) && isset($body['body']) ? trim((string) $body['body']) : '';
        if ($text === '' && !$allowEmpty) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'A message cannot be empty', 400));
            return null;
        }
        if (mb_strlen($text) > self::MAX_BODY_CHARS) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Messages are limited to ' . self::MAX_BODY_CHARS . ' characters', 400));
            return null;
        }
        return $text;
    }

    // ------------------------------------------------------------------ conversations

    private function findOrCreateConversation($me, $other)
    {
        $a = min($me, $other);
        $b = max($me, $other);
        $conv = new InboxConversation();
        $conv->Load('participant_a = ? AND participant_b = ?', array($a, $b));
        if (!empty($conv->id)) {
            return $conv;
        }
        $conv->participant_a = $a;
        $conv->participant_b = $b;
        $conv->hidden_a = 0;
        $conv->hidden_b = 0;
        $conv->last_read_a = 0;
        $conv->last_read_b = 0;
        $conv->last_message_id = 0;
        $conv->created = date('Y-m-d H:i:s');
        $conv->Save();
        return $conv;
    }

    private function setHidden($conv, $me, $hidden)
    {
        $col = (int) $conv->participant_a === $me ? 'hidden_a' : 'hidden_b';
        if ((int) $conv->$col !== ($hidden ? 1 : 0)) {
            $conv->$col = $hidden ? 1 : 0;
            $conv->Save();
        }
    }

    private function listConversations($me)
    {
        $model = new InboxConversation();
        // Most recent message first, whoever sent it. A conversation with no
        // message yet (just opened) has a NULL time, which MySQL sorts last in DESC.
        $rows = $model->Find(
            '((participant_a = ? AND hidden_a = 0) OR (participant_b = ? AND hidden_b = 0)) '
            . 'ORDER BY last_message_at DESC, id DESC',
            array($me, $me)
        );
        $unread = $this->unreadCounts($me);
        $out = array();
        foreach ($rows as $conv) {
            $out[] = $this->formatConversation($conv, $me, $unread);
        }
        return $out;
    }

    /** @return array conversation id => unread count, for every conversation I am in */
    private function unreadCounts($me)
    {
        $rows = BaseService::getInstance()->getDB()->Execute(
            'SELECT m.conversation, COUNT(*) AS n '
            . 'FROM InboxMessages m JOIN InboxConversations c ON c.id = m.conversation '
            . 'WHERE (c.participant_a = ? OR c.participant_b = ?) AND m.sender <> ? '
            . 'AND m.id > IF(c.participant_a = ?, c.last_read_a, c.last_read_b) '
            . 'GROUP BY m.conversation',
            array($me, $me, $me, $me)
        );
        $out = array();
        foreach (is_array($rows) ? $rows : array() as $row) {
            $out[(int) $row['conversation']] = (int) $row['n'];
        }
        return $out;
    }

    private function formatConversation($conv, $me, array $unread)
    {
        $id = (int) $conv->id;
        $preview = null;
        if (!empty($conv->last_message_id)) {
            $last = new InboxMessage();
            $last->Load('id = ?', array((int) $conv->last_message_id));
            if (!empty($last->id)) {
                $preview = array(
                    'mine' => (int) $last->sender === $me,
                    'text' => mb_substr((string) $last->body, 0, 80),
                );
            }
        }
        return array(
            'id' => $id,
            'other' => $this->person($this->otherParticipant($conv, $me)),
            'unread' => isset($unread[$id]) ? $unread[$id] : 0,
            'last_message_at' => $this->iso($conv->last_message_at),
            'preview' => $preview,
        );
    }

    // ------------------------------------------------------------------ messages

    private function listMessages($conv, $me, $beforeId)
    {
        $model = new InboxMessage();
        $where = 'conversation = ?';
        $params = array((int) $conv->id);
        if ($beforeId > 0) {
            $where .= ' AND id < ?';
            $params[] = $beforeId;
        }
        $rows = $model->Find($where . ' ORDER BY id DESC LIMIT ' . (self::PAGE_SIZE + 1), $params);
        $hasMore = count($rows) > self::PAGE_SIZE;
        if ($hasMore) {
            array_pop($rows);
        }
        $rows = array_reverse($rows); // oldest first, as the chat reads

        $out = array();
        foreach ($rows as $msg) {
            $out[] = $this->formatMessage($msg);
        }

        // The latest page is what the reader is looking at: everything up to its
        // newest message is now read.
        if ($beforeId === 0 && !empty($conv->last_message_id)) {
            $col = (int) $conv->participant_a === $me ? 'last_read_a' : 'last_read_b';
            if ((int) $conv->$col < (int) $conv->last_message_id) {
                $conv->$col = (int) $conv->last_message_id;
                $conv->Save();
            }
        }

        return array(
            'conversation' => $this->formatConversation($conv, $me, array()),
            'messages' => $out,
            'has_more' => $hasMore,
        );
    }

    private function formatMessage($msg)
    {
        return array(
            'id' => (int) $msg->id,
            'conversation' => (int) $msg->conversation,
            'sender' => $this->person((int) $msg->sender),
            'body' => $msg->body,
            'attachments' => $this->attachmentsWithUrls($msg->attachments),
            'created' => $this->iso($msg->created),
            'edited' => !empty($msg->updated) && $msg->updated !== $msg->created,
        );
    }


    // ------------------------------------------------------------------ time

    /**
     * The caller's time zone, resolved the way the profile's "Time now" is: the
     * employee's own zone, else their department's. Null when there is no profile.
     */
    private function callerTimeZone()
    {
        try {
            $tz = BaseService::getInstance()->getCurrentEmployeeTimeZone();
            return empty($tz) ? null : $tz;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * A stored "Y-m-d H:i:s" — written in the application's default zone — as an
     * ISO-8601 instant with its offset, so the client can render it in whatever zone
     * the reader is in without knowing where the server is.
     */
    private function iso($stored)
    {
        if (empty($stored)) {
            return null;
        }
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', (string) $stored, new \DateTimeZone(date_default_timezone_get()));
        return $dt ? $dt->format(\DateTime::ATOM) : null;
    }

    // ------------------------------------------------------------------ attachments

    /**
     * Stored attachment list plus a fresh signed download URL for each. Minted only
     * here, i.e. only for a participant who has already passed the conversation check
     * — the download handler itself verifies just the signature.
     */
    private function attachmentsWithUrls($json)
    {
        $list = json_decode((string) $json, true);
        if (!is_array($list)) {
            return array();
        }
        $out = array();
        foreach ($list as $item) {
            if (!is_array($item) || empty($item['name'])) {
                continue;
            }
            $url = null;
            try {
                $url = FileService::getInstance()->getFileUrl($item['name']);
            } catch (\Throwable $e) {
                LogManager::getInstance()->error('Inbox: could not build file url - ' . $e->getMessage());
            }
            $out[] = array(
                'name' => $item['name'],
                'filename' => isset($item['filename']) ? $item['filename'] : $item['name'],
                'original' => isset($item['original']) ? $item['original'] : $item['name'],
                'size_text' => isset($item['size_text']) ? $item['size_text'] : '',
                'url' => $url,
            );
        }
        return $out;
    }

    /**
     * Requested attachment names -> Files rows the caller may attach: their own fresh
     * uploads parked against THIS conversation.
     *
     * @return File[]|null null when any name is unknown or not theirs
     */
    private function resolveAttachments(array $names, $me, $convId)
    {
        $out = array();
        $seen = array();
        foreach ($names as $entry) {
            $name = is_array($entry) ? (isset($entry['name']) ? $entry['name'] : '') : $entry;
            $name = trim((string) $name);
            if ($name === '' || isset($seen[$name]) || !preg_match('/^[A-Za-z0-9_\-]+$/', $name)) {
                return null;
            }
            $seen[$name] = true;
            $file = new File();
            $file->Load('name = ? AND file_group = ?', array($name, self::FILE_GROUP));
            if (empty($file->id) || $file->object_type !== self::FILE_GROUP
                || (int) $file->object_id !== $convId || (int) $file->employee !== $me
            ) {
                return null;
            }
            $out[] = $file;
        }
        return $out;
    }

    private function originalNameFor($file, array $requested)
    {
        foreach ($requested as $entry) {
            if (is_array($entry) && isset($entry['name']) && $entry['name'] === $file->name && !empty($entry['original'])) {
                return $this->cleanDisplayName($entry['original']);
            }
        }
        return $file->filename;
    }

    private function handleUpload(RestEndPoint $rest, $me, $conv)
    {
        if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'No file was uploaded', 400));
            return;
        }
        $upload = $_FILES['file'];
        if (!empty($upload['error']) || empty($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'The upload failed', 400));
            return;
        }
        $size = (int) $upload['size'];
        if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Files are limited to ' . (self::MAX_FILE_BYTES / 1048576) . ' MB', 400));
            return;
        }
        $original = $this->cleanDisplayName(isset($upload['name']) ? $upload['name'] : 'file');
        $ext = strtolower((string) pathinfo($original, PATHINFO_EXTENSION));
        if ($ext === '' || !in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'That file type is not allowed. Use: ' . implode(', ', self::ALLOWED_EXTENSIONS), 400));
            return;
        }

        $name = 'dm_' . bin2hex(random_bytes(12));
        $target = BaseService::getInstance()->getDataDirectory() . $name . '.' . $ext;
        if (!@move_uploaded_file($upload['tmp_name'], $target)) {
            LogManager::getInstance()->error('Inbox: could not move upload to ' . $target);
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Could not store the file', 500));
            return;
        }
        // An image extension must carry real raster bytes: a renamed HTML/SVG file
        // would otherwise be served inline as an image.
        if (in_array($ext, array('jpg', 'jpeg', 'png', 'gif'), true) && function_exists('getimagesize')) {
            $info = @getimagesize($target);
            if ($info === false || !isset($info[2]) || !in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF), true)) {
                @unlink($target);
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'That file is not a valid image', 400));
                return;
            }
        }

        $file = FileService::getInstance()->saveFile($name, $ext, false, self::FILE_GROUP, $me);
        $file->object_type = self::FILE_GROUP;
        $file->object_id = $conv->id;
        $file->Save();

        $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
            'name' => $file->name,
            'filename' => $file->filename,
            'original' => $original,
            'size_text' => $file->size_text,
        ), 201));
    }

    /** A filename safe to show and to put in a Content-Disposition header. */
    private function cleanDisplayName($name)
    {
        $name = basename(str_replace('\\', '/', (string) $name));
        $name = preg_replace('/[^\pL\pN\s\.\-_\(\)]/u', '', $name);
        $name = trim((string) $name);
        return $name === '' ? 'file' : mb_substr($name, 0, 120);
    }

    // ------------------------------------------------------------------ people

    /** Name, photo and job title for an employee id, cached per request. */
    private function person($employeeId)
    {
        $employeeId = (int) $employeeId;
        if (isset($this->people[$employeeId])) {
            return $this->people[$employeeId];
        }
        $emp = new Employee();
        $emp->Load('id = ?', array($employeeId));
        if (empty($emp->id)) {
            return $this->people[$employeeId] = array('id' => $employeeId, 'name' => 'Former employee', 'image' => null, 'job_title' => null);
        }
        $emp = FileService::getInstance()->updateSmallProfileImage($emp);
        $title = null;
        if (!empty($emp->job_title)) {
            $jt = new JobTitle();
            $jt->Load('id = ?', array((int) $emp->job_title));
            $title = empty($jt->id) ? null : $jt->name;
        }
        return $this->people[$employeeId] = array(
            'id' => (int) $emp->id,
            'name' => trim($emp->first_name . ' ' . $emp->last_name),
            'image' => empty($emp->image) ? null : $emp->image,
            'job_title' => $title,
        );
    }

    private function searchEmployees($q, $me)
    {
        $like = '%' . str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $q) . '%';
        $rows = BaseService::getInstance()->getDB()->Execute(
            'SELECT id FROM Employees WHERE status = ? AND id <> ? '
            . "AND (first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?) "
            . 'ORDER BY first_name, last_name LIMIT ' . self::SEARCH_LIMIT,
            array('Active', $me, $like, $like, $like)
        );
        $out = array();
        foreach (is_array($rows) ? $rows : array() as $row) {
            $out[] = $this->person((int) $row['id']);
        }
        return $out;
    }
}
