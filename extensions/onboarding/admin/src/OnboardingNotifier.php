<?php

namespace OnboardingAdmin;

use Classes\BaseService;
use Classes\Email\EmailParts;
use Utils\LogManager;

/**
 * Emails and in-app notifications for onboarding. A failure here is logged and never
 * stops the action that triggered it.
 *
 * Email bodies are built with EmailParts and wrapped in the shared layout by
 * EmailSender.
 */
class OnboardingNotifier
{
    const TYPE = 'Onboarding';
    /** My Onboarding: a new hire's checklist and the "Onboarding tasks" of other owners. */
    const USER_URL = 'g=extension&n=onboarding|user';
    /** Onboarding admin, where HR works on its tasks. */
    const ADMIN_URL = 'g=extension&n=onboarding|admin';

    /** Tell the owners of the given types that they have onboarding tasks for this person. */
    public static function ownersAssigned($onboardingId, array $ownerTypes)
    {
        if (empty($ownerTypes)) {
            return;
        }
        $o = (new OnboardingService())->find($onboardingId);
        if ($o === null) {
            return;
        }
        $in = implode(',', array_fill(0, count($ownerTypes), '?'));
        $steps = Db::rows(
            "SELECT owner_employee, owner_type, title, due_date FROM OnboardingSteps WHERE onboarding = ? AND owner_employee IS NOT NULL AND owner_type IN ($in) AND status = 'Pending' ORDER BY due_date IS NULL, due_date, id",
            array_merge(array((int) $onboardingId), $ownerTypes)
        );
        $byOwner = array();
        foreach ($steps as $step) {
            $byOwner[(int) $step['owner_employee']][] = $step;
        }

        $person = trim($o['first_name'] . ' ' . $o['last_name']);
        $workforce = self::isWorkforce($o);
        $startDate = (!$workforce && !empty($o['start_date'])) ? EmailParts::date($o['start_date']) : '';

        foreach ($byOwner as $ownerId => $ownerSteps) {
            $count = count($ownerSteps);
            $tasks = $count === 1 ? 'an onboarding task' : $count . ' onboarding tasks';
            $text = 'You have ' . $tasks . ' for ' . $person
                . ($startDate === '' ? '.' : ', who starts on ' . $startDate . '.');

            $isHr = self::hasHrStep($ownerSteps);
            $route = $isHr ? self::ADMIN_URL : self::USER_URL;
            self::notify($ownerId, null, $text, $route);

            $items = array();
            foreach ($ownerSteps as $step) {
                $items[] = $step['title'] . (empty($step['due_date']) ? '' : ' (due ' . EmailParts::date($step['due_date']) . ')');
            }
            $url = EmailParts::appUrl($route);
            self::emailEmployee(
                $ownerId,
                ($count === 1 ? 'New onboarding task for ' : 'New onboarding tasks for ') . $person,
                $text,
                function ($firstName) use ($text, $person, $workforce, $startDate, $items, $url, $isHr) {
                    return EmailParts::greeting($firstName)
                        . EmailParts::text($text)
                        . EmailParts::details(array(
                            ($workforce ? 'Employee' : 'New hire') => $person,
                            'Start date' => $startDate,
                        ))
                        . EmailParts::paragraph(count($items) === 1 ? 'Your task:' : 'Your tasks:')
                        . EmailParts::bullets($items)
                        . EmailParts::button($url, $isHr ? 'Open onboarding' : 'Open my onboarding tasks')
                        . EmailParts::fallbackLink($url);
                }
            );
        }
    }

    public static function accepted($onboardingId)
    {
        $o = (new OnboardingService())->find($onboardingId);
        if ($o === null) {
            return;
        }
        $person = trim($o['first_name'] . ' ' . $o['last_name']);
        $text = sprintf('%s accepted the invitation and started onboarding.', $person);
        if (!empty($o['created_by'])) {
            self::notify(null, (int) $o['created_by'], $text, self::ADMIN_URL);
        }
        if (!empty($o['supervisor'])) {
            self::notify((int) $o['supervisor'], null, $text, self::USER_URL);
        }
    }

    public static function allDone($onboardingId)
    {
        $o = (new OnboardingService())->find($onboardingId);
        if ($o === null || empty($o['created_by'])) {
            return;
        }
        $person = trim($o['first_name'] . ' ' . $o['last_name']);
        self::notify(null, (int) $o['created_by'], sprintf('Onboarding for %s is complete.', $person), self::ADMIN_URL);
    }

    public static function documentSubmitted(array $o, array $step)
    {
        if (empty($o['created_by'])) {
            return;
        }
        $person = trim($o['first_name'] . ' ' . $o['last_name']);
        self::notify(null, (int) $o['created_by'], sprintf('%s uploaded "%s" for review.', $person, $step['title']), self::ADMIN_URL);
    }

    /** The reviewer is the signed-in user (HR or the manager who reviewed the step). */
    public static function changesRequested(array $o, array $step, $note)
    {
        if (empty($o['employee'])) {
            return;
        }
        $reviewer = self::currentUserName();
        $sentence = sprintf(
            '%s asked for changes to your onboarding step "%s". Please update it and submit it again.',
            $reviewer === '' ? 'Your reviewer' : $reviewer,
            $step['title']
        );
        self::notify((int) $o['employee'], null, $sentence, self::USER_URL);

        $url = EmailParts::appUrl(self::USER_URL);
        self::emailEmployee(
            (int) $o['employee'],
            'Changes requested on your onboarding step: ' . $step['title'],
            $sentence,
            function ($firstName) use ($sentence, $note, $reviewer, $url) {
                return EmailParts::greeting($firstName)
                    . EmailParts::text($sentence)
                    . EmailParts::note($note, $reviewer === '' ? 'What to change' : 'What to change, from ' . $reviewer)
                    . EmailParts::button($url, 'Open My Onboarding')
                    . EmailParts::fallbackLink($url);
            }
        );
    }

    /**
     * One reminder listing the steps a person has due soon or overdue. $steps are
     * OnboardingSteps rows joined with the person's first_name / last_name.
     */
    public static function digest($employeeId, array $steps)
    {
        if (empty($steps)) {
            return;
        }
        $today = date('Y-m-d');
        $items = array();
        $overdue = 0;
        foreach ($steps as $step) {
            $late = $step['due_date'] < $today;
            $overdue += $late ? 1 : 0;
            $for = $step['owner_type'] === 'new_hire' ? '' : ' for ' . trim($step['first_name'] . ' ' . $step['last_name']);
            $items[] = $step['title'] . $for . ' (' . ($late ? 'was due ' : 'due ') . EmailParts::date($step['due_date']) . ')';
        }
        $count = count($steps);
        if ($count === 1) {
            $late = $steps[0]['due_date'] < $today;
            $text = sprintf(
                'Reminder: "%s" %s %s.',
                $steps[0]['title'],
                $late ? 'was due on' : 'is due on',
                EmailParts::date($steps[0]['due_date'])
            );
        } elseif ($overdue > 0) {
            $text = sprintf('Reminder: you have %d onboarding steps that are overdue or due soon.', $count);
        } else {
            $text = sprintf('Reminder: you have %d onboarding steps due soon.', $count);
        }

        $isHr = self::hasHrStep($steps);
        $route = $isHr ? self::ADMIN_URL : self::USER_URL;
        self::notify((int) $employeeId, null, $text, $route);

        if ($overdue > 0) {
            $subject = $count === 1 ? 'An onboarding step is overdue' : 'Some onboarding steps are overdue';
        } else {
            $subject = $count === 1 ? 'An onboarding step is due soon' : 'Onboarding steps due soon';
        }
        $url = EmailParts::appUrl($route);
        $ownSteps = $steps[0]['owner_type'] === 'new_hire';
        self::emailEmployee(
            (int) $employeeId,
            $subject,
            $text,
            function ($firstName) use ($items, $url, $isHr, $ownSteps) {
                return EmailParts::greeting($firstName)
                    . EmailParts::text(count($items) === 1
                        ? 'This onboarding step needs your attention:'
                        : 'These onboarding steps need your attention:')
                    . EmailParts::bullets($items)
                    . EmailParts::button(
                        $url,
                        $isHr ? 'Open onboarding' : ($ownSteps ? 'Open My Onboarding' : 'Open my onboarding tasks')
                    )
                    . EmailParts::fallbackLink($url);
            }
        );
    }

    // --- plumbing -----------------------------------------------------------------------

    private static function isWorkforce(array $o)
    {
        return isset($o['track']) && $o['track'] === 'workforce';
    }

    private static function hasHrStep(array $steps)
    {
        foreach ($steps as $step) {
            if (isset($step['owner_type']) && $step['owner_type'] === 'hr') {
                return true;
            }
        }
        return false;
    }

    /** The signed-in user's employee name, or '' when unknown. */
    private static function currentUserName()
    {
        try {
            $user = BaseService::getInstance()->getCurrentUser();
            if (empty($user) || empty($user->employee)) {
                return '';
            }
            $employee = new \Employees\Common\Model\Employee();
            $employee->Load('id = ?', array((int) $user->employee));
            return trim($employee->first_name . ' ' . $employee->last_name);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private static function notify($toEmployee, $toUserId, $message, $url)
    {
        try {
            $manager = BaseService::getInstance()->notificationManager;
            if (!empty($manager)) {
                $manager->addNotification(
                    $toEmployee,
                    $message,
                    json_encode(array('type' => 'url', 'url' => $url)),
                    self::TYPE,
                    $toUserId,
                    true
                );
            }
        } catch (\Throwable $e) {
            LogManager::getInstance()->error('Onboarding notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Email an employee through their login's address. $buildBody($firstName) returns
     * the body HTML (built with EmailParts, so values are escaped there); $preheader
     * is plain text.
     */
    private static function emailEmployee($employeeId, $subject, $preheader, $buildBody)
    {
        try {
            $user = BaseService::getInstance()->getUserFromProfileId($employeeId);
            if (empty($user) || empty($user->email)) {
                return;
            }
            $sender = BaseService::getInstance()->getEmailSender();
            if (empty($sender)) {
                return;
            }
            $employee = new \Employees\Common\Model\Employee();
            $employee->Load('id = ?', array($employeeId));
            $sender->sendEmailDelayed($subject, $user->email, $buildBody((string) $employee->first_name), array(
                'url' => EmailParts::appUrl(),
                'preheader' => EmailParts::esc($preheader),
            ));
        } catch (\Throwable $e) {
            LogManager::getInstance()->error('Onboarding email failed: ' . $e->getMessage());
        }
    }
}
