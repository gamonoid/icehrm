<?php
/**
 * Data for the Home module — the landing page / entry point for every user level.
 * Served by GET appshell/home (see core/appshell-routes.php) and consumed by the
 * Home widget registry (web/modules/src/home). One call returns every lightweight
 * widget payload; heavier feeds (e.g. Kudos) lazy-load from their own endpoints.
 *
 * Home computes as little as possible itself: it composes what the two existing
 * dashboard services already produce and adds only the cross-module pieces the
 * entry point needs (action items, the launchpad). See docs/HOME_AND_KUDOS_PLAN.md.
 */

namespace Classes;

class HomeService
{
    /**
     * @param  \Users\Common\Model\User $user
     * @return array
     */
    public function getData($user)
    {
        $base = (new EmployeeDashboardService())->getData($user);
        if (!is_array($base)) {
            $base = array();
        }

        $isManager = !empty($base['isManager']);
        $isAdminOrManager = in_array($user->user_level, array('Admin', 'Manager'), true);

        $data = array(
            'isManager' => $isManager,
            'userLevel' => $user->user_level,
            'todo' => isset($base['todo']) ? $base['todo'] : array(),
            'attendance' => isset($base['attendance']) ? $base['attendance'] : null,
            'celebrations' => isset($base['celebrations']) ? $base['celebrations'] : array(),
            'leave' => isset($base['leave']) ? $base['leave'] : null,
            'teams' => isset($base['teams']) ? $base['teams'] : array(),
            'directReports' => isset($base['directReports']) ? $base['directReports'] : array(),
            'teamStats' => isset($base['teamStats']) ? $base['teamStats'] : null,
        );

        // Admin / Manager: a slim slice of the org dashboard (the full charts stay
        // on the Organization Overview app). DashboardService itself gates on level.
        $admin = null;
        if ($isAdminOrManager) {
            try {
                $admin = (new DashboardService())->getData($user);
            } catch (\Throwable $e) {
                $admin = null;
            }
            if (is_array($admin)) {
                $data['admin'] = array(
                    'kpis' => isset($admin['kpis']) ? $admin['kpis'] : null,
                    'recentHires' => isset($admin['recentHires']) ? $admin['recentHires'] : array(),
                    'leave' => isset($admin['leave']) ? $admin['leave'] : null,
                    'expenses' => isset($admin['expenses']) ? $admin['expenses'] : null,
                    'recruitment' => isset($admin['recruitment']) ? $admin['recruitment'] : null,
                );
            }
        }

        if (!empty($base['banners'])) {
            $data['banners'] = $base['banners'];
        }
        $data['actionItems'] = $this->actionItems($user, $base, $admin);
        $data['apps'] = $this->apps($user);
        $data['myTeam'] = $this->myTeam($user);
        $data['feed'] = $this->feedChannel($user);
        $data['announcements'] = $this->announcements($user);

        return $data;
    }

    // --- feed -----------------------------------------------------------------

    /**
     * The company feed lives in the built-in "Public" discussions channel, which is
     * flagged is_system and cannot be deleted. Home only needs to know its id and
     * whether this user may post; the posts themselves are fetched lazily by the
     * feed widget from the discussions endpoints, which apply the channel rules.
     *
     * Returns null when the discussions extension is not installed, so the whole
     * feed simply disappears from Home rather than erroring.
     *
     * @return array|null {channelId, name, canPost}
     */
    private function feedChannel($user)
    {
        if (!class_exists('\\DiscussionsUser\\Common\\Model\\DiscussionChannel')
            || !class_exists('\\DiscussionsUser\\ChannelAccess')
        ) {
            return null;
        }
        try {
            $channel = new \DiscussionsUser\Common\Model\DiscussionChannel();
            $channel->Load('name = ? AND is_system = 1', array('Public'));
            if (empty($channel->id)) {
                return null;
            }
            if (!\DiscussionsUser\ChannelAccess::canRead($channel, $user)) {
                return null;
            }
            return array(
                'channelId' => (int) $channel->id,
                'name' => $channel->name,
                'canPost' => \DiscussionsUser\ChannelAccess::canPost($channel, $user),
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    // --- announcements ----------------------------------------------------------

    /** How many announcements Home shows, and how far back it looks. */
    const ANNOUNCEMENT_LIMIT = 3;
    const ANNOUNCEMENT_DAYS = 30;

    /**
     * Recent posts in the built-in "Announcements" discussions channel, as a title
     * and a short excerpt each. Null when discussions isn't installed, the user
     * can't read the channel, or nothing was announced recently — so the widget
     * only appears when there is something to show.
     *
     * @return array|null {channelId, posts: [{id, title, excerpt, created, author}]}
     */
    private function announcements($user)
    {
        if (!class_exists('\\DiscussionsUser\\Common\\Model\\DiscussionChannel')
            || !class_exists('\\DiscussionsUser\\ChannelAccess')
        ) {
            return null;
        }
        try {
            $channel = new \DiscussionsUser\Common\Model\DiscussionChannel();
            $channel->Load('name = ? AND is_system = 1', array('Announcements'));
            if (empty($channel->id) || !\DiscussionsUser\ChannelAccess::canRead($channel, $user)) {
                return null;
            }
            $rows = $this->teamRows(
                BaseService::getInstance()->getDB(),
                'SELECT id, author_employee, content, created FROM DiscussionPosts
                 WHERE channel = ? AND created >= DATE_SUB(NOW(), INTERVAL ' . self::ANNOUNCEMENT_DAYS . ' DAY)
                 ORDER BY created DESC, id DESC LIMIT ' . self::ANNOUNCEMENT_LIMIT,
                array((int) $channel->id)
            );
            if (empty($rows)) {
                return null;
            }
            $authorIds = array();
            foreach ($rows as $r) {
                if (!empty($r['author_employee'])) {
                    $authorIds[] = array('id' => (int) $r['author_employee']);
                }
            }
            $authors = array();
            foreach (EmployeeDashboardService::withAvatars($authorIds) as $a) {
                $authors[$a['id']] = $a['image'];
            }
            $posts = array();
            foreach ($rows as $r) {
                $text = $this->announcementText($r['content']);
                $empId = (int) $r['author_employee'];
                $posts[] = array(
                    'id' => (int) $r['id'],
                    'title' => $text['title'],
                    'excerpt' => $text['excerpt'],
                    'created' => $r['created'],
                    'author' => $empId > 0 ? array(
                        'id' => $empId,
                        'name' => $this->employeeName($empId),
                        'image' => isset($authors[$empId]) ? $authors[$empId] : null,
                    ) : null,
                );
            }
            return array('channelId' => (int) $channel->id, 'posts' => $posts);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * A title and an excerpt out of a post's EditorJS blocks: the first header is
     * the title, the first paragraph-like text the excerpt. With no header, the
     * first text becomes the title. Inline markup (bold, links, @mention spans) is
     * dropped — the widget shows plain text.
     */
    private function announcementText($content)
    {
        $doc = json_decode((string) $content, true);
        $blocks = (is_array($doc) && isset($doc['blocks']) && is_array($doc['blocks'])) ? $doc['blocks'] : array();
        $title = null;
        $excerpt = null;
        foreach ($blocks as $b) {
            $type = isset($b['type']) ? $b['type'] : '';
            $data = isset($b['data']) && is_array($b['data']) ? $b['data'] : array();
            if ($type === 'list' && isset($data['items']) && is_array($data['items'])) {
                $first = reset($data['items']);
                $raw = is_array($first) ? (isset($first['content']) ? $first['content'] : '') : $first;
            } else {
                $raw = isset($data['text']) ? $data['text'] : '';
            }
            $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $raw), ENT_QUOTES, 'UTF-8')));
            if ($plain === '') {
                continue;
            }
            if ($type === 'header' && $title === null) {
                $title = $plain;
            } elseif ($excerpt === null) {
                $excerpt = $plain;
            }
            if ($title !== null && $excerpt !== null) {
                break;
            }
        }
        if ($title === null) {
            $title = $excerpt !== null ? $excerpt : 'Announcement';
            $excerpt = null;
        }
        return array('title' => $this->clip($title, 90), 'excerpt' => $excerpt === null ? null : $this->clip($excerpt, 160));
    }

    private function clip($text, $max)
    {
        if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $max) {
            return rtrim(mb_substr($text, 0, $max - 1, 'UTF-8')) . '…';
        }
        return $text;
    }

    private function employeeName($id)
    {
        $rows = $this->teamRows(
            BaseService::getInstance()->getDB(),
            'SELECT first_name, last_name FROM Employees WHERE id = ?',
            array((int) $id)
        );
        return empty($rows) ? null : trim($rows[0]['first_name'] . ' ' . $rows[0]['last_name']);
    }

    // --- action items (Pending tasks) ------------------------------------------

    /**
     * The cross-module action-item registry, v1. Each provider turns data a module
     * already computes into {key, label, count, due, priority, intent}. Items are
     * inherently scoped: employee providers only ever see the caller's own data,
     * and the admin providers run only for Admin/Manager (DashboardService gates).
     * Add a provider here to surface a new module's work on Home.
     */
    private function actionItems($user, array $base, $admin)
    {
        $items = array();

        // System to-dos (module TaskCreators): "set your status", "not checked in", ...
        if (!empty($base['todo']) && is_array($base['todo'])) {
            $i = 0;
            foreach ($base['todo'] as $task) {
                $i++;
                $items[] = array(
                    'key' => 'todo-' . $i,
                    'label' => isset($task['text']) ? $task['text'] : '',
                    'details' => isset($task['details']) ? $task['details'] : '',
                    'count' => 1,
                    'due' => null,
                    'priority' => isset($task['priority']) ? $task['priority'] : 'normal',
                    // Legacy task links ("…?g=modules&n=attendance") are resolved to an
                    // in-app route by the client; a task without one is not clickable.
                    'intent' => empty($task['link']) ? null : array('link' => $task['link']),
                );
            }
        }

        // Pending approvals (leave, expenses, ...) are already emitted above by each
        // module's TaskCreator, scoped to the caller — so nothing is added here for
        // them; a second source only produced duplicates. New modules surface their
        // work on Home by registering a TaskCreator, the existing cross-module hook.

        // The task creators can emit the same to-do more than once (one per module
        // that registers it); show each distinct item once.
        $seen = array();
        $unique = array();
        foreach ($items as $item) {
            $sig = strtolower(trim($item['label'])) . '|' . (isset($item['count']) ? $item['count'] : '');
            if (isset($seen[$sig])) {
                continue;
            }
            $seen[$sig] = true;
            $unique[] = $item;
        }
        return $unique;
    }

    // --- launchpad ------------------------------------------------------------

    /**
     * Every enabled module this user may open, as launchpad tiles. Uses the SAME
     * rule as the menu (userMayAccessModuleLevels), so a tile never appears for a
     * module the user cannot actually reach. Home itself is excluded.
     */
    private function apps($user)
    {
        $apps = array();
        $modules = ModuleAccessService::getInstance()->getModules();
        if (!is_array($modules)) {
            return $apps;
        }
        foreach ($modules as $module) {
            if (empty($module->name) || $module->name === 'home') {
                continue;
            }
            if (isset($module->status) && $module->status !== 'Enabled') {
                continue;
            }
            $levels = empty($module->user_levels) ? array() : json_decode($module->user_levels, true);
            $roles = empty($module->user_roles) ? array() : json_decode($module->user_roles, true);
            if (!ModuleAccessService::getInstance()->userMayAccessModuleLevels($levels, $roles, $user)) {
                continue;
            }
            // Route key exactly as the shell's menu builds it: core modules are
            // "modules::name" / "admin::name"; extensions are "extension::name|part".
            $group = ($module->mod_group === 'user') ? 'modules' : $module->mod_group;
            $key = $group . '::' . $module->name;
            if (!empty($module->update_path) && strpos($module->update_path, 'extension>') === 0) {
                $key = 'extension::' . substr($module->update_path, strlen('extension>'));
            }
            $apps[] = array(
                'key' => $key,
                'group' => $group,
                'name' => $module->name,
                'label' => !empty($module->label) ? $module->label : $module->name,
                'icon' => isset($module->icon) ? $module->icon : '',
                'menu' => isset($module->menu) ? $module->menu : '',
                'order' => isset($module->mod_order) ? (int) $module->mod_order : 0,
            );
        }
        usort($apps, function ($a, $b) {
            if ($a['menu'] === $b['menu']) {
                return $a['order'] - $b['order'];
            }
            return strcmp($a['menu'], $b['menu']);
        });
        return $apps;
    }

    // --- my team ---------------------------------------------------------------

    /** Faces shown per group; the widget links to the rest via the Team module. */
    const TEAM_MEMBER_LIMIT = 12;

    /**
     * The people this user works with, as faces to message. Teams they belong to
     * (the Team extension) come first, one group per team; with no teams, it falls
     * back to the reporting line — their manager, peers under the same manager, and
     * their direct reports. Null when there is nobody, so the widget hides.
     */
    private function myTeam($user)
    {
        $empId = (int) (!empty($user->employee) ? $user->employee : 0);
        if ($empId <= 0) {
            return null;
        }
        $db = BaseService::getInstance()->getDB();
        $groups = array();

        if ((new EmployeeDashboardService())->extensionExists('team')) {
            $teams = $this->teamRows(
                $db,
                "SELECT t.id, t.name, t.color FROM EmployeeTeamMembers tm
                 JOIN EmployeeTeams t ON t.id = tm.team
                 WHERE tm.member = ? AND tm.status = 'Active' AND t.status = 'Active'
                 ORDER BY t.name LIMIT 6",
                array($empId)
            );
            foreach ($teams as $t) {
                $members = $this->people(
                    $db,
                    "SELECT e.id, e.first_name, e.last_name, jt.name AS title, tm.role
                     FROM EmployeeTeamMembers tm JOIN Employees e ON e.id = tm.member
                     LEFT JOIN JobTitles jt ON jt.id = e.job_title
                     WHERE tm.team = ? AND tm.status = 'Active' AND e.status = 'Active' AND e.id <> ?
                     ORDER BY e.first_name, e.last_name LIMIT " . self::TEAM_MEMBER_LIMIT,
                    array((int) $t['id'], $empId)
                );
                if (empty($members)) {
                    continue;
                }
                $groups[] = array(
                    'id' => (int) $t['id'],
                    'name' => $t['name'],
                    'color' => !empty($t['color']) ? $t['color'] : null,
                    'members' => $members,
                );
            }
        }

        if (empty($groups)) {
            $me = $this->teamRows($db, 'SELECT supervisor FROM Employees WHERE id = ?', array($empId));
            $supervisor = !empty($me[0]['supervisor']) ? (int) $me[0]['supervisor'] : 0;
            $select = "SELECT e.id, e.first_name, e.last_name, jt.name AS title
                 FROM Employees e LEFT JOIN JobTitles jt ON jt.id = e.job_title
                 WHERE e.status = 'Active' AND e.id <> ? AND ";
            $manager = $supervisor > 0
                ? $this->people($db, $select . 'e.id = ?', array($empId, $supervisor), 'Manager')
                : array();
            $peers = $supervisor > 0
                ? $this->people($db, $select . 'e.supervisor = ? ORDER BY e.first_name, e.last_name LIMIT '
                    . self::TEAM_MEMBER_LIMIT, array($empId, $supervisor))
                : array();
            $reports = $this->people($db, $select . 'e.supervisor = ? ORDER BY e.first_name, e.last_name LIMIT '
                . self::TEAM_MEMBER_LIMIT, array($empId, $empId), 'Reports to you');
            // One entry per person: two employees can supervise each other, which
            // puts the manager in "reports to you" as well. The first role wins.
            $members = array();
            foreach (array_merge($manager, $peers, $reports) as $person) {
                if (!isset($members[$person['id']])) {
                    $members[$person['id']] = $person;
                }
            }
            $members = array_values($members);
            if (!empty($members)) {
                $groups[] = array('id' => null, 'name' => 'Your team', 'color' => null, 'members' => $members);
            }
        }

        return empty($groups) ? null : array('groups' => $groups);
    }

    /** People rows → {id, name, title, role, image}. $role overrides the row's role. */
    private function people($db, $sql, array $params, $role = null)
    {
        $out = array();
        foreach ($this->teamRows($db, $sql, $params) as $r) {
            $out[] = array(
                'id' => (int) $r['id'],
                'name' => trim($r['first_name'] . ' ' . $r['last_name']),
                'title' => isset($r['title']) ? $r['title'] : null,
                'role' => $role !== null ? $role : (isset($r['role']) ? $r['role'] : null),
            );
        }
        return EmployeeDashboardService::withAvatars($out);
    }

    private function teamRows($db, $sql, array $params)
    {
        try {
            $r = $db->Execute($sql, $params);
            return is_array($r) ? $r : array();
        } catch (\Throwable $e) {
            return array();
        }
    }
}
