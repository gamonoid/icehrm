<?php

namespace OnboardingAdmin\Cron;

use Classes\Cron\IceTask;
use OnboardingAdmin\Db;
use OnboardingAdmin\OnboardingNotifier;
use OnboardingAdmin\OnboardingService;
use OnboardingAdmin\StepService;
use Utils\LogManager;

/**
 * Hourly onboarding upkeep:
 *  - copy the status of linked steps (Forms, E-Sign, Learn) onto the onboarding,
 *  - make new hires active on their start date,
 *  - remind people about steps due within two days or overdue (at most once a day).
 */
class OnboardingTask implements IceTask
{
    const DAYS_BEFORE = 2;
    const MAX_PER_RUN = 200;

    public function execute($cron)
    {
        try {
            $steps = new StepService();
            foreach (Db::rows("SELECT id FROM Onboardings WHERE status = 'InProgress' LIMIT 500") as $row) {
                $steps->sync((int) $row['id']);
            }
            (new OnboardingService())->activateDue();
            $this->remind();
        } catch (\Throwable $e) {
            LogManager::getInstance()->error('Onboarding cron failed: ' . $e->getMessage());
        }
    }

    /**
     * One digest per person: the new hire for their own steps, and each owner for the
     * tasks assigned to them. Form steps are left to the Forms reminders, and nothing is
     * sent for steps created in the last 20 hours (the invitation just told them).
     */
    private function remind()
    {
        $rows = Db::rows(
            "SELECT s.*, o.id AS o_id, o.employee AS hire_employee, o.first_name, o.last_name FROM OnboardingSteps s JOIN Onboardings o ON o.id = s.onboarding
             WHERE o.status IN ('Invited', 'InProgress')
               AND s.status IN ('Pending', 'ChangesRequested')
               AND s.type <> 'form'
               AND s.due_date IS NOT NULL
               AND s.due_date <= DATE_ADD(CURDATE(), INTERVAL " . self::DAYS_BEFORE . " DAY)
               AND s.created < DATE_SUB(NOW(), INTERVAL 20 HOUR)
               AND (s.last_reminder_at IS NULL OR s.last_reminder_at < DATE_SUB(NOW(), INTERVAL 20 HOUR))
             ORDER BY s.due_date ASC LIMIT " . self::MAX_PER_RUN
        );
        $byPerson = array();
        foreach ($rows as $step) {
            if ($step['owner_type'] === 'new_hire') {
                // The new hire's steps can only be done once they have accepted.
                $to = empty($step['hire_employee']) ? null : (int) $step['hire_employee'];
            } else {
                $to = empty($step['owner_employee']) ? null : (int) $step['owner_employee'];
            }
            if ($to === null) {
                continue;
            }
            $byPerson[$to][] = $step;
        }
        foreach ($byPerson as $employeeId => $steps) {
            OnboardingNotifier::digest($employeeId, $steps);
            foreach ($steps as $step) {
                Db::db()->Execute('UPDATE OnboardingSteps SET last_reminder_at = ? WHERE id = ?', array(Db::now(), (int) $step['id']));
            }
        }
    }
}
