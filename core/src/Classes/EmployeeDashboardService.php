<?php

namespace Classes;

/**
 * EmployeeDashboardService — data for the native employee/manager personal
 * dashboard (modules::dashboard). Mirrors DashboardService' guarded-query style
 * (every query is wrapped so a missing table/extension degrades to empty rather
 * than failing the whole dashboard). PHP 7.3 compatible.
 *
 * Managers (and Admins, who see the manager view) additionally get a team
 * overview and team-on-leave count.
 */
class EmployeeDashboardService
{
    /** @param \Users\Common\Model\User $user */
    public function getData($user)
    {
        $db = BaseService::getInstance()->getDB();
        $empId = (int) (!empty($user->employee) ? $user->employee : 0);
        $isManager = in_array(
            $user->user_level,
            array('Admin', 'Manager', 'Restricted Admin', 'Restricted Manager'),
            true
        );

        $data = array(
            'isManager' => $isManager,
            'todo' => $this->systemTasks(),
            'attendance' => $this->attendanceToday($db, $empId),
            'celebrations' => $this->celebrations($db),
        );

        if ($this->leaveExtensionExists()) {
            $data['leave'] = $this->myLeave($db, $empId);
        }

        if ($isManager && $empId > 0) {
            // Direct reports = the line-management (solid-line) view: who this
            // person manages + approves for (Employees.supervisor).
            $data['directReports'] = $this->directReports($db, $empId);
            $data['teamStats'] = array(
                'reports' => (int) $this->scalar($db, "SELECT COUNT(*) FROM Employees WHERE supervisor = " . $empId . " AND status='Active'"),
                'onLeaveToday' => $this->leaveExtensionExists()
                    ? (int) $this->scalar($db, "SELECT COUNT(DISTINCT el.employee) FROM EmployeeLeaves el JOIN Employees e ON e.id = el.employee WHERE e.supervisor = " . $empId . " AND el.status='Approved' AND el.date_start <= CURDATE() AND el.date_end >= CURDATE()")
                    : 0,
            );
        }

        // Teams = the collaboration (matrix) view: teams this person belongs to,
        // independent of line management. Shown for everyone who is on a team.
        $data['teams'] = $this->myTeams($db, $empId);

        return $data;
    }

    // --- widgets -------------------------------------------------------------

    /**
     * The "My To-Do List": actionable system tasks (status not set, not checked in,
     * setup reminders, …) aggregated from every module's TaskCreator — the same
     * source the legacy dashboard widget used. Each task is {priority, text, link,
     * action, details}. RestEndPoint::process() has set the current user, which the
     * task creators rely on.
     */
    private function systemTasks()
    {
        try {
            $tasks = \Classes\SystemTasks\SystemTasksService::getInstance()->getAdminTasks();
            $out = array();
            foreach ($tasks as $t) {
                $out[] = ($t instanceof \JsonSerializable) ? $t->jsonSerialize() : $t;
            }
            return $out;
        } catch (\Throwable $e) {
            return array();
        }
    }

    private function attendanceToday($db, $empId)
    {
        if ($empId <= 0) {
            return null;
        }
        $hours = (float) $this->scalar(
            $db,
            "SELECT COALESCE(SUM(TIMESTAMPDIFF(MINUTE, in_time, COALESCE(out_time, NOW()))),0)/60
             FROM Attendance WHERE employee = " . $empId . " AND DATE(in_time) = CURDATE()"
        );
        $att = array('hoursToday' => round($hours, 2), 'punchedIn' => 0, 'punchedOutToday' => 0);
        if (class_exists('\\Attendance\\Admin\\Api\\AttendanceUtil')) {
            try {
                $util = new \Attendance\Admin\Api\AttendanceUtil();
                $today = date('Y-m-d');
                $att['punchedIn'] = $util->isEmployeeHasOpenPunch($today, $empId) ? 1 : 0;
                $att['punchedOutToday'] = $util->isEmployeePunchedOut($today, $empId) ? 1 : 0;
            } catch (\Throwable $e) {
                // leave defaults
            }
        }
        return $att;
    }

    private function myLeave($db, $empId)
    {
        if ($empId <= 0) {
            return array('pending' => 0, 'upcoming' => array());
        }
        $upcoming = $this->rows(
            $db,
            "SELECT el.date_start, el.date_end, lt.name AS leave_type
             FROM EmployeeLeaves el LEFT JOIN LeaveTypes lt ON lt.id = el.leave_type
             WHERE el.employee = " . $empId . " AND el.status='Approved' AND el.date_end >= CURDATE()
             ORDER BY el.date_start LIMIT 5"
        );
        $up = array();
        foreach ($upcoming as $r) {
            $up[] = array('type' => $r['leave_type'], 'start' => $r['date_start'], 'end' => $r['date_end']);
        }
        return array(
            'pending' => (int) $this->scalar($db, "SELECT COUNT(*) FROM EmployeeLeaves WHERE employee = " . $empId . " AND status='Pending'"),
            'upcoming' => $up,
        );
    }

    private function directReports($db, $empId)
    {
        $rows = $this->rows(
            $db,
            "SELECT e.id, e.first_name, e.last_name, jt.name AS title
             FROM Employees e LEFT JOIN JobTitles jt ON jt.id = e.job_title
             WHERE e.supervisor = " . $empId . " AND e.status='Active'
             ORDER BY e.first_name, e.last_name LIMIT 16"
        );
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'id' => (int) $r['id'],
                'name' => trim($r['first_name'] . ' ' . $r['last_name']),
                'title' => $r['title'],
            );
        }
        return $out;
    }

    /**
     * Teams the employee belongs to (collaboration / matrix membership), with
     * their role and the team's active member count. Independent of the
     * supervisor line. Empty unless the team extension is installed.
     */
    private function myTeams($db, $empId)
    {
        if ($empId <= 0 || !$this->extensionExists('team')) {
            return array();
        }
        $rows = $this->rows(
            $db,
            "SELECT t.id, t.name, t.color, tm.role,
                (SELECT COUNT(*) FROM EmployeeTeamMembers x WHERE x.team = t.id AND x.status='Active') AS members
             FROM EmployeeTeamMembers tm JOIN EmployeeTeams t ON t.id = tm.team
             WHERE tm.member = " . $empId . " AND tm.status='Active' AND t.status='Active'
             ORDER BY t.name LIMIT 12"
        );
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'id' => (int) $r['id'],
                'name' => $r['name'],
                'role' => $r['role'],
                'members' => (int) $r['members'],
                'color' => !empty($r['color']) ? $r['color'] : null,
            );
        }
        return $out;
    }

    private function celebrations($db)
    {
        $rows = $this->rows($db, "SELECT first_name, last_name, birthday, joined_date
            FROM Employees WHERE status='Active'");
        $today = new \DateTime('today');
        $list = array();
        foreach ($rows as $r) {
            $name = trim($r['first_name'] . ' ' . $r['last_name']);
            if ($this->validDate($r['birthday'])) {
                $occ = $this->nextOccurrence($r['birthday'], $today);
                // Birthdays are only surfaced when they fall today or tomorrow
                // (days == 0 or 1); anything further out is not shown.
                if ($occ['days'] <= 1) {
                    $list[] = array('name' => $name, 'type' => 'birthday', 'date' => $occ['date'], 'days' => $occ['days'], 'years' => null);
                }
            }
            if ($this->validDate($r['joined_date'])) {
                $occ = $this->nextOccurrence($r['joined_date'], $today);
                $years = (int) $today->format('Y') - (int) substr($r['joined_date'], 0, 4);
                if ($occ['days'] <= 30 && $years > 0) {
                    $list[] = array('name' => $name, 'type' => 'anniversary', 'date' => $occ['date'], 'days' => $occ['days'], 'years' => $years);
                }
            }
        }
        usort($list, function ($a, $b) {
            return $a['days'] - $b['days'];
        });
        return array_slice($list, 0, 6);
    }

    // --- helpers (same contracts as DashboardService) ------------------------

    /**
     * Leave ships as a package extension: the free extensions/leave, or the
     * legacy combined leave_and_performance package.
     */
    private function leaveExtensionExists()
    {
        return $this->extensionExists('leave') || $this->extensionExists('leave_and_performance');
    }

    private function extensionExists($name)
    {
        if (!defined('APP_BASE_PATH')) {
            return false;
        }
        if (is_dir(APP_BASE_PATH . '../extensions/' . $name)) {
            return true;
        }
        return function_exists('iceProExtensionsEnabled') && iceProExtensionsEnabled()
            && is_dir(APP_BASE_PATH . '../extensions-pro/' . $name);
    }

    private function rows($db, $sql)
    {
        try {
            $r = $db->Execute($sql);
            return is_array($r) ? $r : array();
        } catch (\Throwable $e) {
            return array();
        }
    }

    private function scalar($db, $sql)
    {
        $r = $this->rows($db, $sql);
        if (empty($r) || !isset($r[0]) || !is_array($r[0])) {
            return 0;
        }
        return reset($r[0]);
    }

    private function validDate($d)
    {
        return !empty($d) && $d !== '0000-00-00' && substr($d, 0, 4) > '1900';
    }

    private function nextOccurrence($dateStr, \DateTime $today)
    {
        $md = substr($dateStr, 5, 5);
        $year = (int) $today->format('Y');
        $occ = \DateTime::createFromFormat('Y-m-d', $year . '-' . $md);
        if ($occ === false) {
            $occ = \DateTime::createFromFormat('Y-m-d', $year . '-03-01');
        }
        if ($occ < $today) {
            $occ->modify('+1 year');
        }
        $days = (int) $today->diff($occ)->format('%a');
        return array('date' => $occ->format('Y-m-d'), 'days' => $days);
    }
}
