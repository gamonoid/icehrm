<?php

namespace Classes;

/**
 * DashboardService — aggregates data for the native React admin dashboard
 * (SPA migration). Sections tied to an extension are only included when that
 * extension's directory exists under /extensions/, so removing the extension
 * removes its dashboard section automatically (directory-driven, like the rest
 * of the app). PHP 7.3 compatible.
 */
class DashboardService
{
    /** @param \Users\Common\Model\User $user */
    public function getData($user)
    {
        $db = BaseService::getInstance()->getDB();

        $data = array(
            'greetingName' => !empty($user->first_name) ? $user->first_name : $user->email,
            'kpis' => $this->coreKpis($db),
            'headcountByDept' => $this->headcountByDept($db),
            'genderDist' => $this->genderDist($db),
            'employmentTypeDist' => $this->employmentTypeDist($db),
            'headcountTrend' => $this->headcountTrend($db),
            'celebrations' => $this->celebrations($db),
            'recentHires' => $this->recentHires($db),
        );

        // --- extension-tied sections (only when the extension dir exists) ---
        if ($this->leaveExtensionExists()) {
            $data['leave'] = $this->leaveData($db);
        }
        if ($this->extensionExists('expenses')) {
            $data['expenses'] = $this->expensesData($db);
        }
        if ($this->extensionExists('recruitment')) {
            $rec = $this->recruitmentData($db);
            if (!empty($rec) && $rec['totalCandidates'] > 0) {
                $data['recruitment'] = $rec;
            }
        }

        // Unpaid-invoice payment reminder (cloud-hosted installations only;
        // mirrors the legacy admin dashboard banner in admin/dashboard/index.php).
        if (class_exists('\\Billing\\Admin\\Api\\BillingActionManager')) {
            $billing = $this->billingReminder();
            if ($billing !== null) {
                $data['billing'] = $billing;
            }
        }

        // Fresh-install sample data prompt (mirrors the legacy banner in
        // core/header.php: fresh install with only the default employee and no
        // demo data loaded).
        if ($this->showDemoPrompt($db, $user)) {
            $data['demoPrompt'] = true;
        }

        // Trial upgrade ad (mirrors the legacy admin dashboard banner gated by
        // the show_upgrade_ad session flag set at login).
        $upgradeAd = $this->upgradeAd();
        if ($upgradeAd !== null) {
            $data['upgradeAd'] = $upgradeAd;
        }

        return $data;
    }

    // --- core sections -------------------------------------------------------

    private function coreKpis($db)
    {
        return array(
            'totalEmployees' => (int) $this->scalar($db, "SELECT COUNT(*) FROM Employees WHERE status='Active'"),
            'departments' => (int) $this->scalar($db, "SELECT COUNT(DISTINCT department) FROM Employees WHERE status='Active' AND department > 0"),
            'newHires' => (int) $this->scalar($db, "SELECT COUNT(*) FROM Employees WHERE status='Active' AND joined_date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)"),
            'activeProjects' => (int) $this->scalar($db, "SELECT COUNT(*) FROM Projects"),
            'jobTitles' => (int) $this->scalar($db, "SELECT COUNT(*) FROM JobTitles"),
        );
    }

    private function headcountByDept($db)
    {
        $rows = $this->rows($db, "SELECT cs.title AS name, COUNT(e.id) AS value
            FROM Employees e JOIN CompanyStructures cs ON cs.id = e.department
            WHERE e.status='Active' GROUP BY e.department, cs.title ORDER BY value DESC");
        return $this->intValue($rows);
    }

    private function genderDist($db)
    {
        $rows = $this->rows($db, "SELECT COALESCE(NULLIF(gender,''),'Unspecified') AS type, COUNT(*) AS value
            FROM Employees WHERE status='Active' GROUP BY type ORDER BY value DESC");
        return $this->intValue($rows);
    }

    private function employmentTypeDist($db)
    {
        $rows = $this->rows($db, "SELECT es.name AS type, COUNT(e.id) AS value
            FROM Employees e JOIN EmploymentStatus es ON es.id = e.employment_status
            WHERE e.status='Active' GROUP BY e.employment_status, es.name ORDER BY value DESC");
        return $this->intValue($rows);
    }

    private function headcountTrend($db)
    {
        $rows = $this->rows($db, "SELECT YEAR(joined_date) AS y, COUNT(*) AS c
            FROM Employees WHERE status='Active' AND joined_date IS NOT NULL AND joined_date > '1990-01-01'
            GROUP BY y ORDER BY y");
        $out = array();
        $cum = 0;
        foreach ($rows as $r) {
            $cum += (int) $r['c'];
            $out[] = array('year' => (string) $r['y'], 'value' => $cum);
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
                if ($occ['days'] <= 60) {
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
        return array_slice($list, 0, 8);
    }

    private function recentHires($db)
    {
        $rows = $this->rows($db, "SELECT e.first_name, e.last_name, e.joined_date, jt.name AS title
            FROM Employees e LEFT JOIN JobTitles jt ON jt.id = e.job_title
            WHERE e.status='Active' AND e.joined_date IS NOT NULL AND e.joined_date > '1990-01-01'
            ORDER BY e.joined_date DESC LIMIT 6");
        $out = array();
        foreach ($rows as $r) {
            $out[] = array(
                'name' => trim($r['first_name'] . ' ' . $r['last_name']),
                'title' => $r['title'],
                'date' => $r['joined_date'],
            );
        }
        return $out;
    }

    // --- extension-tied sections --------------------------------------------

    private function leaveData($db)
    {
        $upcoming = $this->rows($db, "SELECT e.first_name, e.last_name, el.date_start, el.date_end, lt.name AS leave_type
            FROM EmployeeLeaves el JOIN Employees e ON e.id = el.employee
            LEFT JOIN LeaveTypes lt ON lt.id = el.leave_type
            WHERE el.status='Approved' AND el.date_end >= CURDATE()
            ORDER BY el.date_start LIMIT 6");
        $up = array();
        foreach ($upcoming as $r) {
            $up[] = array(
                'name' => trim($r['first_name'] . ' ' . $r['last_name']),
                'type' => $r['leave_type'],
                'start' => $r['date_start'],
                'end' => $r['date_end'],
            );
        }
        $pending = $this->rows($db, "SELECT e.first_name, e.last_name, el.date_start, el.date_end, lt.name AS leave_type
            FROM EmployeeLeaves el JOIN Employees e ON e.id = el.employee
            LEFT JOIN LeaveTypes lt ON lt.id = el.leave_type
            WHERE el.status='Pending' ORDER BY el.date_start DESC LIMIT 6");
        $pendingList = array();
        foreach ($pending as $r) {
            $pendingList[] = array(
                'name' => trim($r['first_name'] . ' ' . $r['last_name']),
                'type' => $r['leave_type'],
                'start' => $r['date_start'],
                'end' => $r['date_end'],
            );
        }
        return array(
            'onLeaveToday' => (int) $this->scalar($db, "SELECT COUNT(DISTINCT employee) FROM EmployeeLeaves WHERE status='Approved' AND date_start <= CURDATE() AND date_end >= CURDATE()"),
            'pendingRequests' => (int) $this->scalar($db, "SELECT COUNT(*) FROM EmployeeLeaves WHERE status='Pending'"),
            'upcoming' => $up,
            'pendingList' => $pendingList,
        );
    }

    private function expensesData($db)
    {
        $rows = $this->rows($db, "SELECT status, COUNT(*) AS c, COALESCE(SUM(amount),0) AS amt FROM EmployeeExpenses GROUP BY status");
        $pendingCount = 0; $pendingAmount = 0.0; $approvedAmount = 0.0;
        foreach ($rows as $r) {
            if ($r['status'] === 'Pending') { $pendingCount = (int) $r['c']; $pendingAmount = (float) $r['amt']; }
            if ($r['status'] === 'Approved') { $approvedAmount = (float) $r['amt']; }
        }
        $byCat = $this->rows($db, "SELECT ec.name AS name, COALESCE(SUM(ee.amount),0) AS value
            FROM EmployeeExpenses ee JOIN ExpensesCategories ec ON ec.id = ee.category
            GROUP BY ee.category, ec.name ORDER BY value DESC LIMIT 6");
        return array(
            'pendingCount' => $pendingCount,
            'pendingAmount' => round($pendingAmount, 2),
            'approvedAmount' => round($approvedAmount, 2),
            'byCategory' => $this->floatValue($byCat),
        );
    }

    private function recruitmentData($db)
    {
        $byStage = $this->rows($db, "SELECT COALESCE(hs.name,'New') AS name, COUNT(c.id) AS value
            FROM Candidates c LEFT JOIN HiringStages hs ON hs.id = c.hiringStage
            GROUP BY c.hiringStage, hs.name ORDER BY value DESC");
        return array(
            'totalCandidates' => (int) $this->scalar($db, "SELECT COUNT(*) FROM Candidates"),
            'byStage' => $this->intValue($byStage),
        );
    }

    /**
     * Count of unpaid ("Sent") invoices and their total, from the central
     * billing server — the same figures the legacy dashboard banner uses.
     */
    private function billingReminder()
    {
        try {
            $billingActionManager = new \Billing\Admin\Api\BillingActionManager();
            $invoiceResponse = $billingActionManager->getInvoices(null)->getData();
            $invoices = isset($invoiceResponse['invoices']) ? $invoiceResponse['invoices'] : array();
            $count = 0;
            $total = 0.0;
            foreach ($invoices as $inv) {
                $inv = (array) $inv;
                if (isset($inv['status']) && $inv['status'] === 'Sent') {
                    $count++;
                    $total += (float) (isset($inv['amountf']) ? $inv['amountf'] : 0);
                }
            }
            return array('unpaidInvoices' => $count, 'unpaidTotal' => round($total, 2));
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Same conditions as the legacy demo-mode banner in core/header.php: an
     * Admin on a fresh install (exactly one active employee, the default
     * "IceHrm Employee") with the demo-mode extension available and no demo
     * data loaded yet.
     */
    private function showDemoPrompt($db, $user)
    {
        try {
            if ($user->user_level !== 'Admin') {
                return false;
            }
            if (!defined('APP_BASE_PATH')
                || !file_exists(APP_BASE_PATH . '../extensions/demo-mode/admin/demo-mode.php')) {
                return false;
            }
            $rows = $this->rows($db, "SELECT COUNT(*) as cnt, MIN(first_name) as fname, MIN(last_name) as lname
                FROM Employees WHERE status = 'Active'");
            if (empty($rows[0])) {
                return false;
            }
            $row = $rows[0];
            if ((int) $row['cnt'] !== 1) {
                return false;
            }
            $tableCheck = $this->rows($db, "SHOW TABLES LIKE 'DemoDataEntries'");
            if (empty($tableCheck)) {
                return true;
            }
            return ((int) $this->scalar($db, "SELECT COUNT(*) FROM DemoDataEntries")) === 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Trial upgrade ad data — shown when login set the show_upgrade_ad session
     * flag (trial plan still active), with remaining trial days from the
     * central billing server. Same gate as the legacy dashboard banner.
     */
    private function upgradeAd()
    {
        try {
            if (\Utils\SessionUtils::getSessionObject('show_upgrade_ad') != '1') {
                return null;
            }
            if (!class_exists('\\Billing\\Admin\\Api\\BillingActionManager') || !defined('CLIENT_NAME')) {
                return null;
            }
            $req = new \stdClass();
            $req->name = CLIENT_NAME;
            $planData = (new \Billing\Admin\Api\BillingActionManager())->getPlan($req)->getData();
            return array('days' => isset($planData['days']) ? (int) $planData['days'] : null);
        } catch (\Throwable $e) {
            return null;
        }
    }

    // --- helpers -------------------------------------------------------------

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

    // The DB handle is a MyORM\MySqlActiveRecord; Execute() runs raw SQL and
    // returns an array of associative rows (mysqli fetch_all(MYSQLI_ASSOC)).
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
        return reset($r[0]); // first column of the first row
    }

    private function intValue($rows)
    {
        $out = array();
        foreach ($rows as $r) {
            $r['value'] = (int) $r['value'];
            $out[] = $r;
        }
        return $out;
    }

    private function floatValue($rows)
    {
        $out = array();
        foreach ($rows as $r) {
            $r['value'] = round((float) $r['value'], 2);
            $out[] = $r;
        }
        return $out;
    }

    private function validDate($d)
    {
        return !empty($d) && $d !== '0000-00-00' && substr($d, 0, 4) > '1900';
    }

    /**
     * Next occurrence (this year or next) of a date's month-day, and days from today.
     */
    private function nextOccurrence($dateStr, \DateTime $today)
    {
        $md = substr($dateStr, 5, 5); // MM-DD
        $year = (int) $today->format('Y');
        $occ = \DateTime::createFromFormat('Y-m-d', $year . '-' . $md);
        if ($occ === false) {
            // e.g. Feb 29 in a non-leap year — fall back to Mar 1
            $occ = \DateTime::createFromFormat('Y-m-d', $year . '-03-01');
        }
        if ($occ < $today) {
            $occ->modify('+1 year');
        }
        $days = (int) $today->diff($occ)->format('%a');
        return array('date' => $occ->format('Y-m-d'), 'days' => $days);
    }
}
