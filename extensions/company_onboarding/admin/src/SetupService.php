<?php

namespace CompanyOnboardingAdmin;

/**
 * The company setup wizard: its steps, their saved progress, and the prompts that push
 * an admin into it (dashboard banner, menu bubble) while the company has fewer than two
 * real employees.
 *
 * "Real" employees are Active or Onboarding employees that are not sample data (rows
 * the demo-mode tracker recorded in DemoDataEntries).
 */
class SetupService
{
    const ROUTE = 'extension::company_onboarding|admin';

    const NOT_STARTED = 'NotStarted';
    const DONE = 'Done';
    const SKIPPED = 'Skipped';

    /** In wizard order. Optional steps can be skipped. */
    const STEPS = array(
        array('key' => 'clean', 'required' => false),
        array('key' => 'company', 'required' => true),
        array('key' => 'structure', 'required' => false),
        array('key' => 'job_titles', 'required' => false),
        array('key' => 'employment', 'required' => false),
        array('key' => 'calendar', 'required' => false),
        array('key' => 'leave', 'required' => false),
        array('key' => 'team', 'required' => true),
    );

    /** Below this many real employees the company is treated as not set up yet. */
    const SET_UP_EMPLOYEES = 2;

    public static function isAdmin($user)
    {
        return !empty($user) && $user->user_level === 'Admin';
    }

    /** The steps that apply to this install, with their saved status. */
    public function steps()
    {
        $saved = array();
        foreach (Db::rows('SELECT step, status, updated FROM CompanySetupSteps') as $row) {
            $saved[$row['step']] = $row;
        }
        $demo = $this->demoDataCount();
        $steps = array();
        foreach (self::STEPS as $step) {
            $status = isset($saved[$step['key']]) ? $saved[$step['key']]['status'] : self::NOT_STARTED;
            // "Start clean" is only offered while sample data is loaded (or once handled).
            if ($step['key'] === 'clean' && $demo === 0 && $status === self::NOT_STARTED) {
                continue;
            }
            // Work week, holidays and leave types belong to the leave module.
            if ($step['key'] === 'calendar' && !CalendarService::available()) {
                continue;
            }
            if ($step['key'] === 'leave' && !LookupService::exists('leave_types')) {
                continue;
            }
            $steps[] = array(
                'key' => $step['key'],
                'required' => $step['required'],
                'status' => $status,
                'updated' => isset($saved[$step['key']]) ? $saved[$step['key']]['updated'] : null,
            );
        }
        return $steps;
    }

    /** Mark a step Done, Skipped (optional steps only) or back to NotStarted. */
    public function setStatus($key, $status, $user)
    {
        $step = null;
        foreach (self::STEPS as $s) {
            if ($s['key'] === $key) {
                $step = $s;
            }
        }
        if ($step === null) {
            return 'Unknown step.';
        }
        if (!in_array($status, array(self::DONE, self::SKIPPED, self::NOT_STARTED), true)) {
            return 'Unknown status.';
        }
        if ($status === self::SKIPPED && $step['required']) {
            return 'This step cannot be skipped.';
        }
        $exists = Db::rows('SELECT id FROM CompanySetupSteps WHERE step = ?', array($key));
        if (empty($exists)) {
            Db::db()->Execute(
                'INSERT INTO CompanySetupSteps (step, status, updated_by, updated) VALUES (?, ?, ?, ?)',
                array($key, $status, (int) $user->id, Db::now())
            );
        } else {
            Db::db()->Execute(
                'UPDATE CompanySetupSteps SET status = ?, updated_by = ?, updated = ? WHERE step = ?',
                array($status, (int) $user->id, Db::now(), $key)
            );
        }
        return null;
    }

    /** Steps neither done nor skipped. */
    public function remaining(array $steps = null)
    {
        $steps = $steps === null ? $this->steps() : $steps;
        $left = 0;
        foreach ($steps as $step) {
            if ($step['status'] === self::NOT_STARTED) {
                $left++;
            }
        }
        return $left;
    }

    public function demoDataCount()
    {
        if (!Db::tableExists('DemoDataEntries')) {
            return 0;
        }
        return (int) Db::value('SELECT COUNT(*) FROM DemoDataEntries');
    }

    public function realEmployeeCount()
    {
        $sql = "SELECT COUNT(*) FROM Employees e WHERE e.status IN ('Active', 'Onboarding')";
        if (Db::tableExists('DemoDataEntries')) {
            $sql .= " AND NOT EXISTS (SELECT 1 FROM DemoDataEntries d WHERE d.table_name = 'Employees' AND d.record_id = e.id)";
        }
        return (int) Db::value($sql);
    }

    public function needsSetup()
    {
        return $this->realEmployeeCount() < self::SET_UP_EMPLOYEES;
    }

    /** Dashboard banner (DashboardService::addBannerProvider). */
    public function banner($user)
    {
        if (!self::isAdmin($user) || !$this->needsSetup()) {
            return null;
        }
        $steps = $this->steps();
        $total = count($steps);
        $done = $total - $this->remaining($steps);
        if ($done === $total) {
            // Set up, but nobody has joined yet: point at inviting people.
            return array(
                'key' => 'company-onboarding-setup',
                'tone' => 'blue',
                'icon' => 'team',
                'title' => 'Your company is set up',
                'message' => 'People appear in Employees as they accept their invitations. Invite more people, or remind them to accept.',
                'actions' => array(
                    array(
                        'label' => 'Invite your team',
                        'g' => 'extension',
                        'n' => 'company_onboarding|admin',
                        'primary' => true,
                        'sessionHint' => array('key' => 'company-onboarding-open', 'value' => 'invite'),
                    ),
                ),
                'dismissible' => true,
                'replacesDemoPrompt' => true,
            );
        }
        return array(
            'key' => 'company-onboarding-setup',
            'tone' => 'blue',
            'icon' => 'rocket',
            'title' => 'Bring your company onto IceHrm',
            'message' => 'Set up your company, its departments and job titles, then invite your team. It only takes a few minutes.',
            'progress' => array('done' => $done, 'total' => $total),
            'actions' => array(
                array(
                    'label' => $done === 0 ? 'Start setup' : 'Continue setup',
                    'g' => 'extension',
                    'n' => 'company_onboarding|admin',
                    'primary' => true,
                ),
            ),
            'dismissible' => true,
            // The sample-data prompt is replaced: sample data stays under System.
            'replacesDemoPrompt' => true,
        );
    }

    /** Menu bubble (MenuService::addBadgeProvider): setup steps left. */
    public function badges($user)
    {
        if (!self::isAdmin($user) || !$this->needsSetup()) {
            return array();
        }
        return array(self::ROUTE => $this->remaining());
    }

    /** The state the wizard opens with. */
    public function summary()
    {
        $steps = $this->steps();
        return array(
            'steps' => $steps,
            'remaining' => $this->remaining($steps),
            'real_employees' => $this->realEmployeeCount(),
            'needs_setup' => $this->needsSetup(),
            'demo_data' => $this->demoDataCount(),
            'onboarding_available' => class_exists('\\OnboardingAdmin\\OnboardingService'),
        );
    }
}
