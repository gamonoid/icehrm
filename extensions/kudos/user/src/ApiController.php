<?php

namespace KudosUser;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Classes\SettingsManager;
use Employees\Common\Model\Employee;
use KudosUser\Common\Model\Kudos;
use KudosUser\Common\Model\KudosValue;
use KudosUser\Migrations\KudosSettings;
use Reactions\ReactionService;
use Utils\LogManager;

/**
 * The Kudos REST surface.
 *
 *   GET    kudos/me                 who the caller is (employee id, admin?) and their
 *                                   monthly quota {limit, used, remaining}
 *   GET    kudos/values             the company values a kudos can be tagged with
 *   GET    kudos/people?search=     active employees to pick a recipient from
 *   POST   kudos/post               give kudos {recipient, message, value_tag, audience, image}
 *   GET    kudos/feed/(:num)        page N of the feed the caller may see
 *   GET    kudos/received/(:num)    page N of kudos given TO the caller
 *   GET    kudos/given/(:num)       page N of kudos given BY the caller
 *   DELETE kudos/(:num)             author or admin only
 *
 * Who sees what is decided here, from the row's audience and the caller's own
 * employee record — never from anything the client sends:
 *   all         everyone
 *   department  people in the sender's department
 *   team        the sender's manager, peers (same manager) and direct reports
 * The sender and the recipient always see their own kudos; admins see everything.
 */
class ApiController extends IceApiController
{
    const PAGE_SIZE = 20;
    const MAX_MESSAGE_CHARS = 2000;
    const MAX_IMAGE_CHARS = 500;
    const PEOPLE_LIMIT = 20;
    const REACTION_TARGET = 'Kudos';
    const DEFAULT_MONTHLY_LIMIT = 5;
    const MIN_MONTHLY_LIMIT = 2;
    const MAX_MONTHLY_LIMIT = 10;

    /** @var array per-request cache of formatted people, keyed by employee id */
    private $people = array();

    public function registerEndPoints()
    {
        self::register(REST_API_PATH . 'kudos/me', self::GET, function ($pathParams = null) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $employeeId = $this->currentEmployeeId();
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
                'user_id' => (int) $user->id,
                'employee_id' => $employeeId,
                'is_admin' => $this->isAdmin($user),
                'can_give' => $employeeId !== null,
                'me' => $employeeId !== null ? $this->formatPerson($employeeId) : null,
                'quota' => $employeeId !== null ? $this->monthlyQuota($employeeId) : null,
            )));
        });

        self::register(REST_API_PATH . 'kudos/values', self::GET, function ($pathParams = null) {
            $rest = new RestEndPoint();
            if (!$this->requireUser($rest)) {
                return;
            }
            $model = new KudosValue();
            $rows = $model->Find('status = ? ORDER BY sort_order ASC, name ASC', array('Active'));
            $out = array();
            foreach ($rows as $row) {
                $out[] = array(
                    'id' => (int) $row->id,
                    'name' => $row->name,
                    'description' => $row->description,
                );
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $out));
        });

        // Recipient picker: id, name and image only — no other personal data leaves
        // through here, whatever the caller's level.
        self::register(REST_API_PATH . 'kudos/people', self::GET, function ($pathParams = null) {
            $rest = new RestEndPoint();
            if (!$this->requireUser($rest)) {
                return;
            }
            $term = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
            $me = $this->currentEmployeeId();
            $model = new Employee();
            if ($term !== '') {
                $like = '%' . $term . '%';
                $rows = $model->Find(
                    "status = ? AND (first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?) "
                    . 'ORDER BY first_name ASC, last_name ASC LIMIT ' . self::PEOPLE_LIMIT,
                    array('Active', $like, $like, $like)
                );
            } else {
                $rows = $model->Find(
                    'status = ? ORDER BY first_name ASC, last_name ASC LIMIT ' . self::PEOPLE_LIMIT,
                    array('Active')
                );
            }
            $out = array();
            foreach ($rows as $emp) {
                if ($me !== null && (int) $emp->id === $me) {
                    continue;
                }
                $out[] = $this->formatPerson((int) $emp->id, $emp);
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $out));
        });

        self::register(REST_API_PATH . 'kudos/post', self::POST, function ($pathParams = null) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $this->createKudos($rest, $user);
        });

        self::register(REST_API_PATH . 'kudos/feed/(:num)', self::GET, function ($page) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->listFeed($user, max(1, (int) $page))));
        });

        self::register(REST_API_PATH . 'kudos/received/(:num)', self::GET, function ($page) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $me = $this->currentEmployeeId();
            if ($me === null) {
                $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->emptyPage(max(1, (int) $page))));
                return;
            }
            $rest->sendResponse(new IceResponse(
                IceResponse::SUCCESS,
                $this->listWhere('status = ? AND recipient = ?', array('Active', $me), $user, max(1, (int) $page))
            ));
        });

        self::register(REST_API_PATH . 'kudos/given/(:num)', self::GET, function ($page) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $me = $this->currentEmployeeId();
            if ($me === null) {
                $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $this->emptyPage(max(1, (int) $page))));
                return;
            }
            $rest->sendResponse(new IceResponse(
                IceResponse::SUCCESS,
                $this->listWhere('status = ? AND sender = ?', array('Active', $me), $user, max(1, (int) $page))
            ));
        });

        self::register(REST_API_PATH . 'kudos/(:num)', self::DELETE, function ($id) {
            $rest = new RestEndPoint();
            $user = $this->requireUser($rest);
            if (!$user) {
                return;
            }
            $kudos = new Kudos();
            $kudos->Load('id = ?', array((int) $id));
            if (empty($kudos->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Kudos not found', 404));
                return;
            }
            if (!$this->canDelete($kudos, $user)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'You can only delete kudos you gave', 403));
                return;
            }
            $kudos->Delete();
            ReactionService::deleteForTarget(self::REACTION_TARGET, (int) $id);
            $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, array('deleted' => (int) $id)));
        });
    }

    // ---- create ------------------------------------------------------------

    private function createKudos(RestEndPoint $rest, $user)
    {
        $sender = $this->currentEmployeeId();
        if ($sender === null) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Only a user with an employee profile can give kudos', 400));
            return;
        }

        $quota = $this->monthlyQuota($sender);
        if ($quota['remaining'] <= 0) {
            $rest->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'You have given all ' . $quota['limit'] . ' kudos for this month. More become available on the 1st.',
                400
            ));
            return;
        }

        $body = $rest->getRequestBody();
        if (!is_array($body)) {
            $body = array();
        }

        $recipient = isset($body['recipient']) ? (int) $body['recipient'] : 0;
        if ($recipient <= 0) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Choose who the kudos is for', 400));
            return;
        }
        if ($recipient === $sender) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'You cannot give kudos to yourself', 400));
            return;
        }
        $emp = new Employee();
        $emp->Load('id = ? AND status = ?', array($recipient, 'Active'));
        if (empty($emp->id)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'That person could not be found', 404));
            return;
        }

        $message = isset($body['message']) ? trim((string) $body['message']) : '';
        if ($message === '') {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Say what the kudos is for', 400));
            return;
        }
        if (mb_strlen($message) > self::MAX_MESSAGE_CHARS) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Messages are limited to ' . self::MAX_MESSAGE_CHARS . ' characters', 400));
            return;
        }

        $audience = isset($body['audience']) ? (string) $body['audience'] : 'all';
        if (!in_array($audience, Kudos::AUDIENCES, true)) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Unknown audience', 400));
            return;
        }

        $valueTag = isset($body['value_tag']) ? trim((string) $body['value_tag']) : '';
        if ($valueTag !== '') {
            $value = new KudosValue();
            $value->Load('name = ? AND status = ?', array($valueTag, 'Active'));
            if (empty($value->id)) {
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Unknown company value', 400));
                return;
            }
        }

        $image = isset($body['image']) ? trim((string) $body['image']) : '';
        if ($image !== '' && (mb_strlen($image) > self::MAX_IMAGE_CHARS || !preg_match('#^https?://#i', $image))) {
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'The image must be an http(s) link', 400));
            return;
        }

        $kudos = new Kudos();
        $kudos->sender = $sender;
        $kudos->recipient = $recipient;
        $kudos->message = $message;
        $kudos->value_tag = $valueTag === '' ? null : $valueTag;
        $kudos->audience = $audience;
        $kudos->image = $image === '' ? null : $image;
        $kudos->status = 'Active';
        $kudos->created = date('Y-m-d H:i:s');
        if (!$kudos->Save()) {
            LogManager::getInstance()->error('Kudos save error: ' . $kudos->ErrorMsg());
            $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Could not save the kudos', 500));
            return;
        }

        $this->notifyRecipient($kudos, $sender);

        $summary = ReactionService::summarize(self::REACTION_TARGET, array((int) $kudos->id), ReactionService::reactorToken($user));
        $rest->sendResponse(new IceResponse(
            IceResponse::SUCCESS,
            $this->formatKudos($kudos, $user, $summary),
            201
        ));
    }

    /**
     * Tell the recipient. The action deep-links to the Kudos module in the SPA
     * (g=extension, n=kudos|user — the form Notifications.jsx routes on).
     */
    private function notifyRecipient(Kudos $kudos, $sender)
    {
        try {
            $from = $this->formatPerson((int) $sender);
            $name = !empty($from['name']) ? $from['name'] : 'A colleague';
            $url = (defined('CLIENT_BASE_URL') ? CLIENT_BASE_URL : '') . '?g=extension&n=kudos|user&m=user_Kudos';
            BaseService::getInstance()->notificationManager->addNotification(
                (int) $kudos->recipient,
                $name . ' gave you kudos: ' . mb_substr((string) $kudos->message, 0, 120),
                '{"type":"url","url":"' . $url . '"}',
                'Kudos'
            );
        } catch (\Exception $e) {
            LogManager::getInstance()->error('Kudos notification error: ' . $e->getMessage());
        }
    }

    // ---- read --------------------------------------------------------------

    private function listFeed($user, $page)
    {
        if ($this->isAdmin($user)) {
            return $this->listWhere('status = ?', array('Active'), $user, $page);
        }

        $me = $this->currentEmployeeId();
        if ($me === null) {
            return $this->listWhere('status = ? AND audience = ?', array('Active', 'all'), $user, $page);
        }

        $viewer = new Employee();
        $viewer->Load('id = ?', array($me));

        $where = 'status = ? AND (audience = ? OR sender = ? OR recipient = ?';
        $params = array('Active', 'all', $me, $me);

        $dept = $this->departmentMembers($viewer);
        if (!empty($dept)) {
            $where .= ' OR (audience = ? AND sender IN (' . implode(',', $dept) . '))';
            $params[] = 'department';
        }
        $team = $this->teamMembers($viewer);
        if (!empty($team)) {
            $where .= ' OR (audience = ? AND sender IN (' . implode(',', $team) . '))';
            $params[] = 'team';
        }
        $where .= ')';

        return $this->listWhere($where, $params, $user, $page);
    }

    /** Ids (ints) of active employees in the viewer's department, viewer included. */
    private function departmentMembers(Employee $viewer)
    {
        if (empty($viewer->id) || empty($viewer->department)) {
            return array();
        }
        $rows = BaseService::getInstance()->getDB()->Execute(
            'SELECT id FROM Employees WHERE department = ? AND status = ?',
            array((int) $viewer->department, 'Active')
        );
        return $this->idList($rows);
    }

    /**
     * Ids (ints) of the viewer's team: their manager, people with the same manager,
     * their own direct reports, and themselves.
     */
    private function teamMembers(Employee $viewer)
    {
        if (empty($viewer->id)) {
            return array();
        }
        $db = BaseService::getInstance()->getDB();
        $ids = array((int) $viewer->id);
        if (!empty($viewer->supervisor)) {
            $ids[] = (int) $viewer->supervisor;
            $peers = $db->Execute(
                'SELECT id FROM Employees WHERE supervisor = ? AND status = ?',
                array((int) $viewer->supervisor, 'Active')
            );
            $ids = array_merge($ids, $this->idList($peers));
        }
        $reports = $db->Execute(
            'SELECT id FROM Employees WHERE supervisor = ? AND status = ?',
            array((int) $viewer->id, 'Active')
        );
        $ids = array_merge($ids, $this->idList($reports));
        return array_values(array_unique($ids));
    }

    private function idList($rows)
    {
        $out = array();
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $out[] = (int) $row['id'];
            }
        }
        return $out;
    }

    private function listWhere($where, array $params, $user, $page)
    {
        $db = BaseService::getInstance()->getDB();
        $rows = $db->Execute('SELECT COUNT(*) AS n FROM KudosPosts WHERE ' . $where, $params);
        $total = (is_array($rows) && isset($rows[0]['n'])) ? (int) $rows[0]['n'] : 0;

        $offset = ($page - 1) * self::PAGE_SIZE;
        $model = new Kudos();
        $items = $model->Find(
            $where . ' ORDER BY id DESC LIMIT ' . (int) $offset . ', ' . self::PAGE_SIZE,
            $params
        );

        $ids = array();
        foreach ($items as $item) {
            $ids[] = (int) $item->id;
        }
        $reactions = ReactionService::summarize(self::REACTION_TARGET, $ids, ReactionService::reactorToken($user));

        $out = array();
        foreach ($items as $item) {
            $out[] = $this->formatKudos($item, $user, $reactions);
        }

        return array(
            'items' => $out,
            'page' => $page,
            'page_size' => self::PAGE_SIZE,
            'total' => $total,
            'has_more' => ($offset + count($out)) < $total,
        );
    }

    private function emptyPage($page)
    {
        return array('items' => array(), 'page' => $page, 'page_size' => self::PAGE_SIZE, 'total' => 0, 'has_more' => false);
    }

    // ---- monthly quota -----------------------------------------------------

    /**
     * The per-person monthly allowance, from the "Kudos: Max Kudos Per Month"
     * setting. Anything missing or outside the 2–10 range the setting offers falls
     * back to the default.
     */
    private function monthlyLimit()
    {
        $value = SettingsManager::getInstance()->getSetting(KudosSettings::MAX_PER_MONTH);
        $limit = is_numeric($value) ? (int) $value : self::DEFAULT_MONTHLY_LIMIT;
        if ($limit < self::MIN_MONTHLY_LIMIT || $limit > self::MAX_MONTHLY_LIMIT) {
            $limit = self::DEFAULT_MONTHLY_LIMIT;
        }
        return $limit;
    }

    /**
     * How many kudos the sender has given this calendar month (server time, the same
     * clock `created` is written with) against their allowance.
     */
    private function monthlyQuota($sender)
    {
        $limit = $this->monthlyLimit();
        $rows = BaseService::getInstance()->getDB()->Execute(
            'SELECT COUNT(*) AS n FROM KudosPosts WHERE sender = ? AND created >= ?',
            array((int) $sender, date('Y-m-01 00:00:00'))
        );
        $used = (is_array($rows) && isset($rows[0]['n'])) ? (int) $rows[0]['n'] : 0;
        return array(
            'limit' => $limit,
            'used' => $used,
            'remaining' => max(0, $limit - $used),
        );
    }

    // ---- formatting / helpers ---------------------------------------------

    private function formatKudos(Kudos $kudos, $user, array $reactions)
    {
        $id = (int) $kudos->id;
        return array(
            'id' => $id,
            'sender' => $this->formatPerson((int) $kudos->sender),
            'recipient' => $this->formatPerson((int) $kudos->recipient),
            'message' => (string) $kudos->message,
            'value_tag' => empty($kudos->value_tag) ? null : $kudos->value_tag,
            'audience' => $kudos->audience,
            'image' => empty($kudos->image) ? null : $kudos->image,
            'created' => $this->iso($kudos->created),
            'reactions' => isset($reactions[$id]) ? $reactions[$id] : array('counts' => new \stdClass(), 'mine' => array()),
            'can_delete' => $this->canDelete($kudos, $user),
        );
    }

    /** id, name, image (small profile picture) — nothing else. */
    private function formatPerson($employeeId, $loaded = null)
    {
        $employeeId = (int) $employeeId;
        if (isset($this->people[$employeeId])) {
            return $this->people[$employeeId];
        }
        $emp = $loaded;
        if ($emp === null) {
            $emp = new Employee();
            $emp->Load('id = ?', array($employeeId));
        }
        if (empty($emp->id)) {
            $person = array('employee_id' => $employeeId, 'name' => 'Former employee', 'image' => null);
        } else {
            $emp = FileService::getInstance()->updateSmallProfileImage($emp);
            $person = array(
                'employee_id' => (int) $emp->id,
                'name' => trim($emp->first_name . ' ' . $emp->last_name),
                'image' => empty($emp->image) ? null : $emp->image,
            );
        }
        $this->people[$employeeId] = $person;
        return $person;
    }

    private function canDelete(Kudos $kudos, $user)
    {
        if ($this->isAdmin($user)) {
            return true;
        }
        $me = $this->currentEmployeeId();
        return $me !== null && (int) $kudos->sender === $me;
    }

    private function isAdmin($user)
    {
        if (empty($user)) {
            return false;
        }
        $level = (string) $user->user_level;
        return $level === 'Admin' || $level === 'Restricted Admin';
    }

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

    private function currentEmployeeId()
    {
        $id = BaseService::getInstance()->getCurrentProfileId();
        return empty($id) ? null : (int) $id;
    }

    private function iso($dateTime)
    {
        if (empty($dateTime)) {
            return null;
        }
        $ts = strtotime($dateTime);
        return $ts === false ? null : date('c', $ts);
    }
}
