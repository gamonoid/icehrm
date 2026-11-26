<?php

namespace DiscussionsUser;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use DiscussionsUser\Common\Model\DiscussionChannel;
use DiscussionsUser\Common\Model\DiscussionPost;
use DiscussionsUser\Common\Model\DiscussionReply;
use Employees\Common\Model\Employee;
use Model\File;
use Users\Common\Model\User;
use Utils\LogManager;

/**
 * The reader / writer side of Discussions.
 *
 * Every call re-derives what the caller may do from the channel's own level lists
 * (see ChannelAccess) — the client's idea of its permissions is never trusted. Posts
 * are EditorJS documents; replies are plain text, two levels deep at most.
 *
 * Attachments live in the core Files table. Download links are minted here, per
 * request, only for callers who can read the channel — the download handler itself
 * checks just the signature, so this is the access control.
 */
class ApiController extends IceApiController
{
    const PAGE_SIZE = 20;
    const MAX_CONTENT_BYTES = 200000;
    const MAX_REPLY_CHARS = 5000;
    const MAX_ATTACHMENTS = 10;
    /** How many people the mention picker offers at once. */
    const MENTION_LIMIT = 20;
    const MAX_FILE_BYTES = 10485760; // 10 MB

    /** Upload whitelist. Anything that can execute or render as HTML stays out. */
    const ALLOWED_EXTENSIONS = array(
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'rtf', 'ppt', 'pptx',
        'jpg', 'jpeg', 'png', 'gif',
    );

    /** Block types the post renderer knows how to draw; anything else is refused. */
    const ALLOWED_BLOCKS = array(
        'paragraph', 'header', 'list', 'checklist', 'quote', 'code', 'delimiter', 'table',
    );

    const FILE_GROUP = 'DiscussionAttachment';

    const SEARCH_MAX_RESULTS = 50;
    const SEARCH_MIN_CHARS = 2;

    /** @var array per-request cache of formatted authors, keyed "user:employee" */
    private $authors = array();

    public function registerEndPoints()
    {
        self::register(REST_API_PATH . 'discussions/me', self::GET, function ($pathParams = null) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
                'user_id' => (int) $user->id,
                'user_level' => ChannelAccess::normalizeLevel($user->user_level),
                'is_admin' => ChannelAccess::isAdmin($user),
                'timezone' => $this->callerTimeZone(),
                'author' => $this->formatAuthor($user->id, $this->currentEmployeeId()),
                'allowed_extensions' => self::ALLOWED_EXTENSIONS,
                'max_file_bytes' => self::MAX_FILE_BYTES,
            )));
        });

        // The dashboard card: the newest post in the built-in Announcements channel,
        // as plain text. Nothing when the caller cannot read that channel.
        self::register(REST_API_PATH . 'discussions/dashboard', self::GET, function ($pathParams = null) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $channel = new DiscussionChannel();
            $channel->Load('is_system = 1');
            if (empty($channel->id) || !ChannelAccess::canRead($channel, $user)) {
                $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array('channel' => null, 'post' => null)));
                return;
            }
            $out = array(
                'channel' => array('id' => (int) $channel->id, 'name' => $channel->name, 'can_post' => ChannelAccess::canPost($channel, $user)),
                'post' => null,
            );
            $model = new DiscussionPost();
            $latest = $model->Find('channel = ? ORDER BY id DESC LIMIT 1', array($channel->id));
            if (!empty($latest)) {
                $post = $latest[0];
                $text = $this->plainText(json_decode((string) $post->content, true));
                $files = json_decode((string) $post->attachments, true);
                $replies = BaseService::getInstance()->getDB()->Execute(
                    'SELECT COUNT(*) AS n FROM DiscussionReplies WHERE post = ?', array($post->id)
                );
                $out['post'] = array(
                    'id' => (int) $post->id,
                    'author' => $this->formatAuthor($post->author_user, $post->author_employee),
                    'created' => $this->iso($post->created),
                    'excerpt' => mb_substr($text, 0, 220) . (mb_strlen($text) > 220 ? '…' : ''),
                    'attachments' => is_array($files) ? count($files) : 0,
                    'replies' => (is_array($replies) && isset($replies[0]['n'])) ? (int) $replies[0]['n'] : 0,
                );
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $out));
        });

        self::register(REST_API_PATH . 'discussions/channels', self::GET, function ($pathParams = null) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $model = new DiscussionChannel();
            $rows = $model->Find('1 = 1 ORDER BY is_system DESC, name ASC', array());
            $out = array();
            foreach ($rows as $row) {
                if (ChannelAccess::canRead($row, $user)) {
                    $out[] = $this->formatChannel($row, $user);
                }
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $out));
        });

        // The page number travels in the PATH (…/posts/2), not a query string: the
        // SPA's IceApiClient proxies through api/index.php?url=/<endpoint>, where a
        // query string inside the endpoint breaks the route match.
        $listPosts = function ($channelId, $page = 1) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $channel = $this->loadReadableChannel($rest, $channelId, $user);
            if (!$channel) {
                return;
            }
            $page = max(1, (int) $page);
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->listPosts($channel, $user, $page)));
        };
        self::register(REST_API_PATH . 'discussions/channels/(:num)/posts', self::GET, $listPosts);
        self::register(REST_API_PATH . 'discussions/channels/(:num)/posts/(:num)', self::GET, $listPosts);

        self::register(REST_API_PATH . 'discussions/channels/(:num)/posts', self::POST, function ($channelId) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $channel = $this->loadReadableChannel($rest, $channelId, $user);
            if (!$channel) {
                return;
            }
            if (!ChannelAccess::canPost($channel, $user)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'You cannot post in this channel', 403));
                return;
            }
            $post = new DiscussionPost();
            $post->channel = $channel->id;
            $post->author_user = $user->id;
            $post->author_employee = $this->currentEmployeeId();
            $post->created = date('Y-m-d H:i:s');
            $this->savePost($rest, $post, $user, $channel, true);
        });

        // One post with its replies — what a search result opens.
        self::register(REST_API_PATH . 'discussions/posts/(:num)', self::GET, function ($postId) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $post = new DiscussionPost();
            $post->Load('id = ?', array($postId));
            if (empty($post->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Post not found', 404));
                return;
            }
            $channel = $this->loadReadableChannel($rest, $post->channel, $user);
            if (!$channel) {
                return;
            }
            $replyModel = new DiscussionReply();
            $replies = $replyModel->Find('post = ? ORDER BY id ASC', array($post->id));
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
                'channel' => $this->formatChannel($channel, $user),
                'post' => $this->formatPost($post, $user, $replies),
            )));
        });

        // Search posts and replies across every channel the caller can read. POST with
        // a JSON body {q}: the SPA's API client cannot carry a query string.
        // Who can be @mentioned: active employees, searchable by name. Used by the
        // composer's mention picker; returns the same {employee_id, name, image}
        // shape the rest of the app uses for a person.
        // The search term travels in the PATH, not a query string: the SPA's
        // IceApiClient proxies through api/index.php?url=/<endpoint>, and a second
        // "?" inside the endpoint breaks the route match (same reason the post
        // pages are …/posts/2).
        $listPeople = function ($term = '') {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $term = trim(urldecode((string) $term));
            $model = new Employee();
            if ($term !== '') {
                $like = '%' . $term . '%';
                $rows = $model->Find(
                    "status = ? AND (first_name like ? OR last_name like ? OR CONCAT(first_name,' ',last_name) like ?) "
                    . 'ORDER BY first_name ASC, last_name ASC LIMIT ' . self::MENTION_LIMIT,
                    array('Active', $like, $like, $like)
                );
            } else {
                $rows = $model->Find(
                    'status = ? ORDER BY first_name ASC, last_name ASC LIMIT ' . self::MENTION_LIMIT,
                    array('Active')
                );
            }
            $out = array();
            foreach ($rows as $emp) {
                $out[] = $this->formatAuthorEmployee($emp);
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $out));
        };
        // One person, for the hover card on an @mention: enough to recognise a
        // colleague (picture, job title, department) without opening their profile.
        self::register(REST_API_PATH . 'discussions/person/(:num)', self::GET, function ($id) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $db = BaseService::getInstance()->getDB();
            $rows = $db->Execute(
                'SELECT e.id, e.first_name, e.last_name, jt.name AS job_title, cs.title AS department '
                . 'FROM Employees e '
                . 'LEFT JOIN JobTitles jt ON jt.id = e.job_title '
                . 'LEFT JOIN CompanyStructures cs ON cs.id = e.department '
                . 'WHERE e.id = ? AND e.status = ? LIMIT 1',
                array((int) $id, 'Active')
            );
            if (!is_array($rows) || count($rows) === 0) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Employee not found', 404));
                return;
            }
            $row = $rows[0];
            $emp = new Employee();
            $emp->Load('id = ?', array((int) $id));
            $emp = FileService::getInstance()->updateSmallProfileImage($emp);
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
                'employee_id' => (int) $row['id'],
                'name' => trim($row['first_name'] . ' ' . $row['last_name']),
                'image' => empty($emp->image) ? null : $emp->image,
                'job_title' => empty($row['job_title']) ? null : $row['job_title'],
                'department' => empty($row['department']) ? null : $row['department'],
            )));
        });

        self::register(REST_API_PATH . 'discussions/people', self::GET, $listPeople);
        self::register(REST_API_PATH . 'discussions/people/(:any)', self::GET, $listPeople);

        self::register(REST_API_PATH . 'discussions/search', self::POST, function ($pathParams = null) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $body = $rest->getRequestBody();
            $term = is_array($body) && isset($body['q']) ? trim((string) $body['q']) : '';
            if (mb_strlen($term) < self::SEARCH_MIN_CHARS) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Type at least ' . self::SEARCH_MIN_CHARS . ' characters', 400));
                return;
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->search($term, $user)));
        });

        // Update. Registered for POST as well as PUT: the SPA's IceApiClient only
        // speaks get/post/delete (it proxies through api/index.php?method=…).
        $updatePost = function ($postId) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $post = new DiscussionPost();
            $post->Load('id = ?', array($postId));
            if (empty($post->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Post not found', 404));
                return;
            }
            $channel = $this->loadReadableChannel($rest, $post->channel, $user);
            if (!$channel) {
                return;
            }
            if (!$this->ownsOrAdmin($post->author_user, $user)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'You can only edit your own posts', 403));
                return;
            }
            $this->savePost($rest, $post, $user, $channel, false);
        };
        self::register(REST_API_PATH . 'discussions/posts/(:num)', self::PUT, $updatePost);
        self::register(REST_API_PATH . 'discussions/posts/(:num)', self::POST, $updatePost);

        self::register(REST_API_PATH . 'discussions/posts/(:num)', self::DELETE, function ($postId) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $post = new DiscussionPost();
            $post->Load('id = ?', array($postId));
            if (empty($post->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Post not found', 404));
                return;
            }
            $channel = $this->loadReadableChannel($rest, $post->channel, $user);
            if (!$channel) {
                return;
            }
            if (!$this->ownsOrAdmin($post->author_user, $user)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'You can only delete your own posts', 403));
                return;
            }
            FileService::getInstance()->deleteFilesForObject('DiscussionPost', (int) $post->id);
            $post->Delete(); // replies cascade
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array('deleted' => (int) $postId)));
        });

        self::register(REST_API_PATH . 'discussions/posts/(:num)/replies', self::POST, function ($postId) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $post = new DiscussionPost();
            $post->Load('id = ?', array($postId));
            if (empty($post->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Post not found', 404));
                return;
            }
            $channel = $this->loadReadableChannel($rest, $post->channel, $user);
            if (!$channel) {
                return;
            }
            if (!ChannelAccess::canReply($channel, $user)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'You cannot reply in this channel', 403));
                return;
            }

            $body = $rest->getRequestBody();
            $text = is_array($body) && isset($body['body']) ? trim((string) $body['body']) : '';
            if ($text === '') {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'A reply cannot be empty', 400));
                return;
            }
            if (mb_strlen($text) > self::MAX_REPLY_CHARS) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Replies are limited to ' . self::MAX_REPLY_CHARS . ' characters', 400));
                return;
            }

            $parentId = is_array($body) && !empty($body['parent']) ? (int) $body['parent'] : null;
            if ($parentId !== null) {
                $parent = new DiscussionReply();
                $parent->Load('id = ?', array($parentId));
                if (empty($parent->id) || (int) $parent->post !== (int) $post->id) {
                    $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Reply not found', 404));
                    return;
                }
                // Two levels only: a reply to a reply cannot itself be replied to.
                if (!empty($parent->parent)) {
                    $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Replies can only go two levels deep', 400));
                    return;
                }
            }

            $reply = new DiscussionReply();
            $reply->post = $post->id;
            $reply->parent = $parentId;
            $reply->author_user = $user->id;
            $reply->author_employee = $this->currentEmployeeId();
            $reply->body = $text;
            $reply->created = date('Y-m-d H:i:s');
            if (!$reply->Save()) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Could not save the reply', 500));
                return;
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->formatReply($reply, $user), 201));
        });

        self::register(REST_API_PATH . 'discussions/replies/(:num)', self::DELETE, function ($replyId) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $reply = new DiscussionReply();
            $reply->Load('id = ?', array($replyId));
            if (empty($reply->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Reply not found', 404));
                return;
            }
            $post = new DiscussionPost();
            $post->Load('id = ?', array($reply->post));
            $channel = $this->loadReadableChannel($rest, empty($post->id) ? 0 : $post->channel, $user);
            if (!$channel) {
                return;
            }
            if (!$this->ownsOrAdmin($reply->author_user, $user)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'You can only delete your own replies', 403));
                return;
            }
            $reply->Delete(); // children cascade
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array('deleted' => (int) $replyId)));
        });

        self::register(REST_API_PATH . 'discussions/upload', self::POST, function ($pathParams = null) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $channelId = isset($_POST['channel']) ? (int) $_POST['channel'] : 0;
            $channel = $this->loadReadableChannel($rest, $channelId, $user);
            if (!$channel) {
                return;
            }
            if (!ChannelAccess::canPost($channel, $user)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'You cannot post in this channel', 403));
                return;
            }
            $this->handleUpload($rest, $user, $channel);
        });
    }

    // ------------------------------------------------------------------ auth helpers

    private function requireUser(RestEndPoint $rest)
    {
        $user = BaseService::getInstance()->getCurrentUser();
        if (empty($user) || empty($user->id)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Not authenticated', 401));
            return null;
        }
        return $user;
    }

    /** {employee_id, name, image} for a loaded Employee — the mention picker's shape. */
    private function formatAuthorEmployee($emp)
    {
        $emp = FileService::getInstance()->updateSmallProfileImage($emp);
        return array(
            'employee_id' => (int) $emp->id,
            'name' => trim($emp->first_name . ' ' . $emp->last_name),
            'image' => empty($emp->image) ? null : $emp->image,
        );
    }

    private function currentEmployeeId()
    {
        $id = BaseService::getInstance()->getCurrentProfileId();
        return empty($id) ? null : (int) $id;
    }

    /**
     * The value stored in Files.employee for the caller's uploads: the employee id,
     * or — for a user with no profile, as admins may be — the negated user id, which
     * is the convention the rest of the application uses for exactly that case.
     */
    private function ownerToken($user)
    {
        $employeeId = $this->currentEmployeeId();
        return $employeeId !== null ? $employeeId : -((int) $user->id);
    }

    private function ownsOrAdmin($authorUserId, $user)
    {
        return ChannelAccess::isAdmin($user) || (int) $authorUserId === (int) $user->id;
    }

    /** Load a channel the caller may read, or answer 404/403 and return null. */
    private function loadReadableChannel(RestEndPoint $rest, $channelId, $user)
    {
        $channel = new DiscussionChannel();
        $channel->Load('id = ?', array((int) $channelId));
        if (empty($channel->id)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Channel not found', 404));
            return null;
        }
        if (!ChannelAccess::canRead($channel, $user)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'You cannot view this channel', 403));
            return null;
        }
        return $channel;
    }

    // ------------------------------------------------------------------ formatting

    private function formatChannel($channel, $user)
    {
        return array(
            'id' => (int) $channel->id,
            'name' => $channel->name,
            'description' => $channel->description,
            'is_system' => !empty($channel->is_system),
            'can_post' => ChannelAccess::canPost($channel, $user),
            'can_reply' => ChannelAccess::canReply($channel, $user),
            'post_levels' => ChannelAccess::parseLevels($channel->post_levels),
            'reply_levels' => ChannelAccess::parseLevels($channel->reply_levels),
        );
    }

    private function formatAuthor($userId, $employeeId)
    {
        $key = (int) $userId . ':' . (int) $employeeId;
        if (isset($this->authors[$key])) {
            return $this->authors[$key];
        }

        $author = null;
        if (!empty($employeeId)) {
            $emp = new Employee();
            $emp->Load('id = ?', array((int) $employeeId));
            if (!empty($emp->id)) {
                $emp = FileService::getInstance()->updateSmallProfileImage($emp);
                $author = array(
                    'user_id' => (int) $userId,
                    'employee_id' => (int) $emp->id,
                    'name' => trim($emp->first_name . ' ' . $emp->last_name),
                    'image' => empty($emp->image) ? null : $emp->image,
                );
            }
        }
        if ($author === null) {
            $u = new User();
            $u->Load('id = ?', array((int) $userId));
            $author = array(
                'user_id' => (int) $userId,
                'employee_id' => null,
                'name' => empty($u->username) ? 'Unknown user' : $u->username,
                'image' => null,
            );
        }

        $this->authors[$key] = $author;
        return $author;
    }

    private function formatPost($post, $user, array $replies)
    {
        $content = json_decode((string) $post->content, true);
        return array(
            'id' => (int) $post->id,
            'channel' => (int) $post->channel,
            'author' => $this->formatAuthor($post->author_user, $post->author_employee),
            'content' => is_array($content) ? $content : array('blocks' => array()),
            'attachments' => $this->attachmentsWithUrls($post->attachments),
            'created' => $this->iso($post->created),
            'updated' => $this->iso($post->updated),
            'edited' => !empty($post->updated) && $post->updated !== $post->created,
            'can_edit' => $this->ownsOrAdmin($post->author_user, $user),
            'can_delete' => $this->ownsOrAdmin($post->author_user, $user),
            'reply_count' => count($replies),
            'replies' => $this->nestReplies($replies, $user),
        );
    }

    private function formatReply($reply, $user)
    {
        return array(
            'id' => (int) $reply->id,
            'post' => (int) $reply->post,
            'parent' => empty($reply->parent) ? null : (int) $reply->parent,
            'author' => $this->formatAuthor($reply->author_user, $reply->author_employee),
            'body' => $reply->body,
            'created' => $this->iso($reply->created),
            'can_delete' => $this->ownsOrAdmin($reply->author_user, $user),
            'children' => array(),
        );
    }

    /** Flat rows (ordered by id) -> top-level replies each carrying its children. */
    private function nestReplies(array $rows, $user)
    {
        $top = array();
        $byId = array();
        foreach ($rows as $row) {
            $item = $this->formatReply($row, $user);
            if ($item['parent'] === null) {
                $byId[$item['id']] = $item;
                $top[] = $item['id'];
            }
        }
        foreach ($rows as $row) {
            if (!empty($row->parent) && isset($byId[(int) $row->parent])) {
                $byId[(int) $row->parent]['children'][] = $this->formatReply($row, $user);
            }
        }
        $out = array();
        foreach ($top as $id) {
            $out[] = $byId[$id];
        }
        return $out;
    }

    /** Stored attachment list + a fresh signed download URL for each. */
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
                LogManager::getInstance()->error('Discussions: could not build file url - ' . $e->getMessage());
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

    // ------------------------------------------------------------------ posts

    private function listPosts($channel, $user, $page)
    {
        // MyORM's Execute() hands back an array of assoc rows, not a recordset.
        $db = BaseService::getInstance()->getDB();
        $rows = $db->Execute('SELECT COUNT(*) AS n FROM DiscussionPosts WHERE channel = ?', array($channel->id));
        $total = (is_array($rows) && isset($rows[0]['n'])) ? (int) $rows[0]['n'] : 0;

        $offset = ($page - 1) * self::PAGE_SIZE;
        $model = new DiscussionPost();
        $posts = $model->Find(
            'channel = ? ORDER BY id DESC LIMIT ' . (int) $offset . ', ' . self::PAGE_SIZE,
            array($channel->id)
        );

        // One query for every reply on the page, then group by post.
        $repliesByPost = array();
        if (!empty($posts)) {
            $ids = array();
            foreach ($posts as $p) {
                $ids[] = (int) $p->id;
                $repliesByPost[(int) $p->id] = array();
            }
            $replyModel = new DiscussionReply();
            $rows = $replyModel->Find('post IN (' . implode(',', $ids) . ') ORDER BY id ASC', array());
            foreach ($rows as $row) {
                $repliesByPost[(int) $row->post][] = $row;
            }
        }

        $out = array();
        foreach ($posts as $p) {
            $out[] = $this->formatPost($p, $user, $repliesByPost[(int) $p->id]);
        }

        return array(
            'channel' => $this->formatChannel($channel, $user),
            'posts' => $out,
            'page' => $page,
            'page_size' => self::PAGE_SIZE,
            'total' => $total,
            'has_more' => ($offset + count($out)) < $total,
        );
    }

    /**
     * Validate the body and persist a new or edited post. The document must decode,
     * be of a sane size, and contain only block types the renderer draws. Attachment
     * names must refer to files this caller uploaded (or that already belong to the
     * post being edited).
     */
    private function savePost(RestEndPoint $rest, DiscussionPost $post, $user, $channel, $isNew)
    {
        $body = $rest->getRequestBody();
        if (!is_array($body)) {
            $body = array();
        }

        $content = isset($body['content']) ? $body['content'] : null;
        $error = $this->validateContent($content);
        if ($error !== null) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, $error, 400));
            return;
        }

        $names = isset($body['attachments']) && is_array($body['attachments']) ? $body['attachments'] : array();
        if (count($names) > self::MAX_ATTACHMENTS) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'At most ' . self::MAX_ATTACHMENTS . ' files can be attached', 400));
            return;
        }
        $files = $this->resolveAttachments($names, $user, $isNew ? 0 : (int) $post->id);
        if ($files === null) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'One of the attachments could not be found', 400));
            return;
        }

        if (!$this->hasAnyText($content) && empty($files)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Write something or attach a file first', 400));
            return;
        }

        // @mentions: rebuild every mention from the database (never trusting the
        // markup the client sent) and note who is mentioned, so the people newly
        // tagged can be told after the post is saved.
        $mentionedBefore = array();
        if (!$isNew && !empty($post->content)) {
            $previous = json_decode($post->content, true);
            if (is_array($previous)) {
                $mentionedBefore = Mentions::extract($previous);
            }
        }
        $mentioned = Mentions::canonicalise($content);

        $now = date('Y-m-d H:i:s');
        // Unescaped so "café" is stored as café, not caf\u00e9 — search runs LIKE over
        // this column, and a reader's accented word has to be findable.
        $post->content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $post->updated = $now;
        if ($isNew) {
            $post->created = $now;
            $post->updated = $now;
        }

        // Files dropped from an edited post are removed for good.
        if (!$isNew) {
            $keep = array();
            foreach ($files as $f) {
                $keep[] = $f->name;
            }
            $this->deleteDetachedFiles((int) $post->id, $keep);
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
        $post->attachments = json_encode($stored);

        if (!$post->Save()) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Could not save the post', 500));
            return;
        }

        // Now the post exists, the files belong to it (see deleteFilesForObject).
        foreach ($files as $f) {
            $f->object_type = 'DiscussionPost';
            $f->object_id = $post->id;
            $f->Save();
        }

        // Tell whoever was newly tagged. Best effort: a notification failure must
        // never fail the post itself.
        Mentions::notify(
            $mentioned,
            $mentionedBefore,
            $channel,
            $post,
            $user,
            $this->currentEmployeeId()
        );

        $replies = array();
        if (!$isNew) {
            $replyModel = new DiscussionReply();
            $replies = $replyModel->Find('post = ? ORDER BY id ASC', array($post->id));
        }
        $rest->sendResponse(new IceResponse(
            IceResponse::SUCCESS,
            $this->formatPost($post, $user, $replies),
            $isNew ? 201 : null
        ));
    }

    /** @return string|null an error message, or null when the document is acceptable */
    private function validateContent($content)
    {
        if (!is_array($content) || !isset($content['blocks']) || !is_array($content['blocks'])) {
            return 'The post content is not valid';
        }
        if (strlen(json_encode($content)) > self::MAX_CONTENT_BYTES) {
            return 'The post is too large';
        }
        foreach ($content['blocks'] as $block) {
            if (!is_array($block) || !isset($block['type']) || !is_string($block['type'])) {
                return 'The post content is not valid';
            }
            if (!in_array($block['type'], self::ALLOWED_BLOCKS, true)) {
                return 'The post contains an unsupported block type: ' . $block['type'];
            }
            if (isset($block['data']) && !is_array($block['data'])) {
                return 'The post content is not valid';
            }
        }
        return null;
    }

    /** Is there anything a reader would actually see? (Blank paragraphs do not count.) */
    private function hasAnyText($content)
    {
        foreach ($content['blocks'] as $block) {
            $type = $block['type'];
            $data = isset($block['data']) ? $block['data'] : array();
            if ($type === 'delimiter') {
                return true;
            }
            if (in_array($type, array('paragraph', 'header', 'code'), true)) {
                $text = isset($data['text']) ? $data['text'] : (isset($data['code']) ? $data['code'] : '');
                if (trim(strip_tags((string) $text)) !== '') {
                    return true;
                }
            } elseif ($type === 'quote') {
                if (trim(strip_tags((string) (isset($data['text']) ? $data['text'] : ''))) !== '') {
                    return true;
                }
            } elseif ($type === 'list' || $type === 'checklist') {
                if (!empty($data['items'])) {
                    return true;
                }
            } elseif ($type === 'table') {
                if (!empty($data['content'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Map requested attachment names to Files rows the caller is allowed to attach:
     * their own fresh uploads, or files already on the post being edited.
     *
     * @return File[]|null null when any name is unknown or not theirs
     */
    private function resolveAttachments(array $names, $user, $postId)
    {
        $owner = $this->ownerToken($user);
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
            if (empty($file->id)) {
                return null;
            }
            $fresh = $file->object_type === self::FILE_GROUP && (int) $file->employee === (int) $owner;
            $onPost = $postId > 0 && $file->object_type === 'DiscussionPost' && (int) $file->object_id === $postId;
            if (!$fresh && !$onPost) {
                return null;
            }
            $out[] = $file;
        }
        return $out;
    }

    /** The upload's original filename, if the client sent it alongside the name. */
    private function originalNameFor($file, array $requested)
    {
        foreach ($requested as $entry) {
            if (is_array($entry) && isset($entry['name']) && $entry['name'] === $file->name
                && !empty($entry['original'])
            ) {
                return $this->cleanDisplayName($entry['original']);
            }
        }
        return $file->filename;
    }

    private function deleteDetachedFiles($postId, array $keepNames)
    {
        $model = new File();
        $rows = $model->Find('object_type = ? AND object_id = ?', array('DiscussionPost', $postId));
        $fs = FileService::getInstance();
        foreach ($rows as $row) {
            if (!in_array($row->name, $keepNames, true)) {
                $fs->deleteFileFromDisk($row);
                $row->Delete();
            }
        }
    }

    // ------------------------------------------------------------------ search

    /**
     * Case-insensitive substring search over post bodies and replies, newest first,
     * limited to channels the caller may read.
     *
     * Posts are matched with LIKE on the stored JSON and then RE-CHECKED against the
     * document's plain text — the JSON is full of words a reader never wrote
     * ("paragraph", "blocks", inline tag names), and none of those may count as a hit.
     */
    private function search($term, $user)
    {
        $channels = $this->readableChannels($user);
        if (empty($channels)) {
            return array('q' => $term, 'results' => array(), 'truncated' => false);
        }
        $ids = implode(',', array_map('intval', array_keys($channels)));
        $like = '%' . $this->escapeLike($term) . '%';
        $limit = self::SEARCH_MAX_RESULTS + 1; // one extra tells us whether we cut the list

        $results = array();

        // Raw prepared SQL rather than the models' Find(): the ORM prefixes column names
        // with a table alias when it builds a Find() query, which garbles anything
        // beyond a simple WHERE (a JOIN or a subquery). Execute() returns assoc rows.
        $db = BaseService::getInstance()->getDB();

        $posts = $db->Execute(
            'SELECT id, channel, author_user, author_employee, content, created '
            . 'FROM DiscussionPosts WHERE channel IN (' . $ids . ') AND content LIKE ? '
            . 'ORDER BY id DESC LIMIT ' . (int) $limit,
            array($like)
        );
        foreach (is_array($posts) ? $posts : array() as $row) {
            $text = $this->plainText(json_decode((string) $row['content'], true));
            if (mb_stripos($text, $term) === false) {
                continue; // matched only the JSON scaffolding
            }
            $chId = (int) $row['channel'];
            $results[] = array(
                'type' => 'post',
                'post_id' => (int) $row['id'],
                'reply_id' => null,
                'channel' => array('id' => $chId, 'name' => $channels[$chId]->name),
                'author' => $this->formatAuthor($row['author_user'], $row['author_employee']),
                'created' => $this->iso($row['created']),
                'snippet' => $this->snippetAround($text, $term),
            );
        }

        $replies = $db->Execute(
            'SELECT r.id, r.post, r.author_user, r.author_employee, r.body, r.created, p.channel '
            . 'FROM DiscussionReplies r JOIN DiscussionPosts p ON p.id = r.post '
            . 'WHERE r.body LIKE ? AND p.channel IN (' . $ids . ') '
            . 'ORDER BY r.id DESC LIMIT ' . (int) $limit,
            array($like)
        );
        foreach (is_array($replies) ? $replies : array() as $row) {
            $chId = (int) $row['channel'];
            $results[] = array(
                'type' => 'reply',
                'post_id' => (int) $row['post'],
                'reply_id' => (int) $row['id'],
                'channel' => array('id' => $chId, 'name' => $channels[$chId]->name),
                'author' => $this->formatAuthor($row['author_user'], $row['author_employee']),
                'created' => $this->iso($row['created']),
                'snippet' => $this->snippetAround((string) $row['body'], $term),
            );
        }

        usort($results, function ($a, $b) {
            return strcmp((string) $b['created'], (string) $a['created']);
        });
        $truncated = count($results) > self::SEARCH_MAX_RESULTS;
        if ($truncated) {
            $results = array_slice($results, 0, self::SEARCH_MAX_RESULTS);
        }

        return array('q' => $term, 'results' => $results, 'truncated' => $truncated);
    }

    /** @return array id => channel, for every channel the user may read */
    private function readableChannels($user)
    {
        $model = new DiscussionChannel();
        $out = array();
        foreach ($model->Find('1 = 1', array()) as $ch) {
            if (ChannelAccess::canRead($ch, $user)) {
                $out[(int) $ch->id] = $ch;
            }
        }
        return $out;
    }

    /** LIKE metacharacters in the user's term are literal, not wildcards. */
    private function escapeLike($term)
    {
        return str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $term);
    }

    /** The words a reader sees in an EditorJS document, in order, tags stripped. */
    private function plainText($content)
    {
        if (!is_array($content) || empty($content['blocks']) || !is_array($content['blocks'])) {
            return '';
        }
        $parts = array();
        foreach ($content['blocks'] as $block) {
            $data = isset($block['data']) && is_array($block['data']) ? $block['data'] : array();
            $type = isset($block['type']) ? $block['type'] : '';
            if (in_array($type, array('paragraph', 'header', 'quote'), true)) {
                $parts[] = isset($data['text']) ? $data['text'] : '';
                if ($type === 'quote' && !empty($data['caption'])) {
                    $parts[] = $data['caption'];
                }
            } elseif ($type === 'code') {
                $parts[] = isset($data['code']) ? $data['code'] : '';
            } elseif ($type === 'list' || $type === 'checklist') {
                $parts[] = $this->listItemsText(isset($data['items']) ? $data['items'] : array());
            } elseif ($type === 'table' && !empty($data['content']) && is_array($data['content'])) {
                foreach ($data['content'] as $row) {
                    if (is_array($row)) {
                        $parts[] = implode(' ', array_map('strval', $row));
                    }
                }
            }
        }
        $text = html_entity_decode(strip_tags(implode(' ', $parts)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** List items may be strings, or {content, items} with nesting; flatten them. */
    private function listItemsText($items)
    {
        $out = array();
        foreach ((array) $items as $item) {
            if (is_array($item)) {
                if (isset($item['content'])) {
                    $out[] = (string) $item['content'];
                } elseif (isset($item['text'])) {
                    $out[] = (string) $item['text'];
                }
                if (!empty($item['items'])) {
                    $out[] = $this->listItemsText($item['items']);
                }
            } else {
                $out[] = (string) $item;
            }
        }
        return implode(' ', $out);
    }

    /** A short window of text around the first match, with ellipses where it was cut. */
    private function snippetAround($text, $term)
    {
        $text = trim(preg_replace('/\s+/u', ' ', (string) $text));
        $pos = mb_stripos($text, $term);
        $before = 60;
        $width = 180;
        if ($pos === false) {
            $start = 0;
        } else {
            $start = max(0, $pos - $before);
        }
        $snippet = mb_substr($text, $start, $width);
        if ($start > 0) {
            $snippet = '…' . $snippet;
        }
        if ($start + $width < mb_strlen($text)) {
            $snippet .= '…';
        }
        return $snippet;
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

    // ------------------------------------------------------------------ uploads

    private function handleUpload(RestEndPoint $rest, $user, $channel)
    {
        if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'No file was uploaded', 400));
            return;
        }
        $upload = $_FILES['file'];
        if (!empty($upload['error'])) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'The upload failed (code ' . (int) $upload['error'] . ')', 400));
            return;
        }
        if (empty($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) {
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
            $rest->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'That file type is not allowed. Use: ' . implode(', ', self::ALLOWED_EXTENSIONS),
                400
            ));
            return;
        }

        $dataDir = BaseService::getInstance()->getDataDirectory();
        $name = 'disc_' . bin2hex(random_bytes(12));
        $target = $dataDir . $name . '.' . $ext;
        if (!@move_uploaded_file($upload['tmp_name'], $target)) {
            LogManager::getInstance()->error('Discussions: could not move upload to ' . $target);
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Could not store the file', 500));
            return;
        }

        // An image extension must carry real raster bytes: a renamed HTML/SVG file
        // would otherwise be served inline as an image. Other types are download-only.
        if (!$this->imageBytesOk($target, $ext)) {
            @unlink($target);
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'That file is not a valid image', 400));
            return;
        }

        $file = FileService::getInstance()->saveFile($name, $ext, false, self::FILE_GROUP, $this->ownerToken($user));
        // Parked against the channel until a post claims it (see savePost).
        $file->object_type = self::FILE_GROUP;
        $file->object_id = $channel->id;
        $file->Save();

        $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
            'name' => $file->name,
            'filename' => $file->filename,
            'original' => $original,
            'size_text' => $file->size_text,
        ), 201));
    }

    private function imageBytesOk($path, $ext)
    {
        if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif'), true)) {
            return true;
        }
        if (!function_exists('getimagesize')) {
            return true;
        }
        $info = @getimagesize($path);
        if ($info === false || !isset($info[2])) {
            return false;
        }
        return in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF), true);
    }

    /** A filename safe to show and to put in a Content-Disposition header. */
    private function cleanDisplayName($name)
    {
        $name = basename(str_replace('\\', '/', (string) $name));
        $name = preg_replace('/[^\pL\pN\s\.\-_\(\)]/u', '', $name);
        $name = trim((string) $name);
        if ($name === '') {
            $name = 'file';
        }
        return mb_substr($name, 0, 120);
    }
}
