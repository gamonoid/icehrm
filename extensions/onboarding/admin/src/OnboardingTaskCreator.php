<?php

namespace OnboardingAdmin;

use Classes\BaseService;
use Classes\LanguageManager;
use Classes\SystemTasks\DTO\Task;
use Classes\SystemTasks\TaskCreator;

/**
 * Home "Pending tasks": the new hire's open onboarding steps, the onboarding tasks
 * assigned to the signed-in person, and for HR the documents waiting for review.
 */
class OnboardingTaskCreator implements TaskCreator
{
    public function getTasksCreators()
    {
        $creators = array();
        $employeeId = BaseService::getInstance()->getCurrentProfileId();
        $user = BaseService::getInstance()->getCurrentUser();
        $isHr = !empty($user) && OnboardingAccess::isHr($user);

        if (!empty($employeeId)) {
            $creators[] = function () use ($employeeId) {
                $rows = Db::rows(
                    "SELECT COUNT(*) AS c, SUM(s.due_date < CURDATE()) AS overdue FROM OnboardingSteps s JOIN Onboardings o ON o.id = s.onboarding
                     WHERE o.employee = ? AND o.status = 'InProgress' AND s.owner_type = 'new_hire' AND s.status IN ('Pending', 'ChangesRequested')",
                    array((int) $employeeId)
                );
                $count = empty($rows) ? 0 : (int) $rows[0]['c'];
                if ($count === 0) {
                    return null;
                }
                $text = $count === 1
                    ? LanguageManager::tran('You have an onboarding step to complete')
                    : sprintf(LanguageManager::tran('You have %s onboarding steps to complete'), $count);
                return (new Task((int) $rows[0]['overdue'] > 0 ? Task::PRIORITY_WARNING : Task::PRIORITY_INFO, $text))
                    ->setLink(CLIENT_BASE_URL . '?g=extension&n=onboarding|user', LanguageManager::tran('Open My Onboarding'));
            };
        }

        if (!empty($employeeId) || $isHr) {
            $creators[] = function () use ($employeeId, $isHr) {
                $mine = array();
                $params = array();
                if (!empty($employeeId)) {
                    $mine[] = 's.owner_employee = ?';
                    $params[] = (int) $employeeId;
                }
                if ($isHr) {
                    $mine[] = "s.owner_type = 'hr'";
                }
                $rows = Db::rows(
                    "SELECT COUNT(*) AS c FROM OnboardingSteps s JOIN Onboardings o ON o.id = s.onboarding
                     WHERE o.status IN ('Invited', 'InProgress') AND s.owner_type <> 'new_hire' AND s.status IN ('Pending', 'ChangesRequested')
                       AND (" . implode(' OR ', $mine) . ')',
                    $params
                );
                $count = empty($rows) ? 0 : (int) $rows[0]['c'];
                if ($count === 0) {
                    return null;
                }
                $text = $count === 1
                    ? LanguageManager::tran('You have an onboarding task for a new hire')
                    : sprintf(LanguageManager::tran('You have %s onboarding tasks for new hires'), $count);
                return (new Task(Task::PRIORITY_INFO, $text))
                    ->setLink(CLIENT_BASE_URL . '?g=extension&n=onboarding|user', LanguageManager::tran('Open onboarding tasks'));
            };
        }

        if ($isHr) {
            $creators[] = function () {
                $rows = Db::rows(
                    "SELECT COUNT(*) AS c FROM OnboardingSteps s JOIN Onboardings o ON o.id = s.onboarding
                     WHERE o.status IN ('Invited', 'InProgress') AND s.type = 'document' AND s.status = 'Submitted'"
                );
                $count = empty($rows) ? 0 : (int) $rows[0]['c'];
                if ($count === 0) {
                    return null;
                }
                $text = $count === 1
                    ? LanguageManager::tran('A new hire document is waiting for your review')
                    : sprintf(LanguageManager::tran('%s new hire documents are waiting for your review'), $count);
                return (new Task(Task::PRIORITY_INFO, $text))
                    ->setLink(CLIENT_BASE_URL . '?g=extension&n=onboarding|admin', LanguageManager::tran('Review onboarding'));
            };
        }
        return $creators;
    }
}
