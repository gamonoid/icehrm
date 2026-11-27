<?php

namespace OnboardingAdmin;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\SettingsManager;
use Employees\Services\UserInvitationService;
use OnboardingAdmin\Common\Model\Onboarding;
use OnboardingAdmin\Common\Model\OnboardingActivity;
use OnboardingAdmin\Common\Model\OnboardingStep;
use Utils\LogManager;

/**
 * The life of one new hire's onboarding:
 *
 *   invite      HR enters the basics and picks a workflow. An Onboardings row and a
 *               snapshot of the workflow's steps are created, then the standard
 *               invitation (core UserInvitationService, context 'onboarding') emails
 *               the "set up your account" link. No employee exists yet.
 *   accept      The new hire sets a password. Core creates the employee (status
 *               Onboarding) and the login, and calls onAccepted() here: the onboarding
 *               is linked to the employee, and steps that need an employee (forms,
 *               signing, courses) are started.
 *   steps       The new hire, HR, the manager, the buddy or a named person complete
 *               their steps (StepService).
 *   activation  On the start date (cron) or when HR completes the onboarding, the
 *               employee becomes Active: full app, directory, payroll, leave.
 *   complete    When every required step is done and the employee is active, the
 *               onboarding is Completed.
 */
class OnboardingService
{
    const OPEN_STATUSES = array('Invited', 'InProgress');
    /** HR can still review submitted documents and forms after the onboarding is completed. */
    const REVIEW_STATUSES = array('InProgress', 'Completed');

    private $workflows;

    public function __construct()
    {
        $this->workflows = new WorkflowService();
    }

    // --- invite -------------------------------------------------------------------------

    /**
     * @return array [onboardingId|null, errors[], acceptUrl|null, emailSent(bool)]
     */
    public function invite(array $input, $user)
    {
        $errors = array();
        $first = $this->str($input, 'first_name', 100);
        $last = $this->str($input, 'last_name', 100);
        $email = $this->str($input, 'email', 100);
        $track = WorkflowService::track(isset($input['track']) ? $input['track'] : null);
        // Existing staff (workforce) already work here: their onboarding starts today, and
        // HR may give their real joined date.
        $start = $track === 'workforce' ? date('Y-m-d') : $this->str($input, 'start_date', 10);
        $joined = $this->str($input, 'joined_date', 10);
        if ($track === 'workforce' && empty($input['supervisor']) && !empty($user->employee)) {
            // Until HR sets the real manager, the admin who sent the invitation manages them.
            $input['supervisor'] = (int) $user->employee;
        }
        if ($first === '') {
            $errors['first_name'] = 'Enter the first name.';
        }
        if ($last === '') {
            $errors['last_name'] = 'Enter the last name.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        $d = \DateTime::createFromFormat('Y-m-d', $start);
        if (!$d || $d->format('Y-m-d') !== $start) {
            $errors['start_date'] = 'Enter the start date.';
        }
        if ($joined !== '') {
            $jd = \DateTime::createFromFormat('Y-m-d', $joined);
            if (!$jd || $jd->format('Y-m-d') !== $joined) {
                $errors['joined_date'] = 'Enter a valid joined date.';
            }
        }
        $resolved = null;
        if (!empty($input['workflow'])) {
            $resolved = $this->workflows->resolve($input['workflow']);
            if ($resolved === null || $resolved[1]['track'] !== $track) {
                $resolved = null;
                $errors['workflow'] = 'Choose a workflow.';
            }
        } else {
            $errors['workflow'] = 'Choose a workflow.';
        }
        $level = isset($input['user_level']) && in_array($input['user_level'], array('Employee', 'Manager', 'Admin'), true)
            ? $input['user_level'] : 'Employee';
        if ($email !== '' && empty($errors['email'])) {
            if (!empty(Db::rows('SELECT id FROM Users WHERE email = ? LIMIT 1', array($email)))) {
                $errors['email'] = 'Someone already has a login with this email address.';
            } elseif (!empty(Db::rows(
                "SELECT id FROM Onboardings WHERE email = ? AND status IN ('Invited', 'InProgress') LIMIT 1",
                array($email)
            ))) {
                $errors['email'] = 'This person is already being onboarded.';
            }
        }
        foreach (array('department' => 'CompanyStructures', 'job_title' => 'JobTitles', 'supervisor' => 'Employees', 'buddy' => 'Employees', 'employment_status' => 'EmploymentStatus') as $field => $table) {
            if (!empty($input[$field]) && empty(Db::rows("SELECT id FROM $table WHERE id = ?", array((int) $input[$field])))) {
                $errors[$field] = 'Choose a valid value.';
            }
        }
        if (!empty($errors)) {
            return array(null, $errors, null, false);
        }
        list($templateId, $workflow) = $resolved;

        try {
            $onboardingId = Db::transaction(function () use ($input, $user, $first, $last, $email, $start, $joined, $track, $level, $templateId, $workflow) {
                $now = Db::now();
                $o = new Onboarding();
                $o->kind = 'onboarding';
                $o->track = $track;
                $o->joined_date = $joined !== '' ? $joined : null;
                $o->template = $templateId;
                $o->template_name = $workflow['name'];
                $o->first_name = $first;
                $o->last_name = $last;
                $o->email = $email;
                $o->start_date = $start;
                $o->user_level = $level;
                foreach (array('department', 'job_title', 'supervisor', 'buddy', 'employment_status') as $field) {
                    $o->$field = empty($input[$field]) ? null : (int) $input[$field];
                }
                $o->country = !empty($input['country']) && preg_match('/^[A-Z]{2}$/', (string) $input['country']) ? $input['country'] : null;
                $message = $this->str($input, 'welcome_message', 5000);
                $o->welcome_message = $message !== '' ? $message : $workflow['welcome_message'];
                $o->welcome_video_url = $workflow['welcome_video_url'];
                $o->first_day_info = $workflow['first_day_info'];
                $o->status = 'Invited';
                $o->created_by = (int) $user->id;
                $o->created = $now;
                $o->updated = $now;
                if (!$o->Save()) {
                    throw new \RuntimeException('Could not save the onboarding: ' . $o->ErrorMsg());
                }
                $this->snapshotSteps($o, $workflow['steps']);
                return (int) $o->id;
            });
        } catch (\Throwable $e) {
            LogManager::getInstance()->error('Onboarding invite failed: ' . $e->getMessage());
            return array(null, array('_' => 'The onboarding could not be created.'), null, false);
        }

        // The invitation (and its email) uses the same flow as every other invitation.
        $resp = (new UserInvitationService())->createInvitation(array(
            'email' => $email,
            'first_name' => $first,
            'last_name' => $last,
            'user_level' => $level,
            'joined_date' => $track === 'workforce' ? ($joined !== '' ? $joined : null) : $start,
            'department' => empty($input['department']) ? null : (int) $input['department'],
            'job_title' => empty($input['job_title']) ? null : (int) $input['job_title'],
            'supervisor' => empty($input['supervisor']) ? null : (int) $input['supervisor'],
            'employment_status' => empty($input['employment_status']) ? null : (int) $input['employment_status'],
            'country' => empty($input['country']) ? null : $input['country'],
            'context' => 'onboarding',
            'context_ref' => $onboardingId,
        ));
        if ($resp->getStatus() !== IceResponse::SUCCESS) {
            Db::db()->Execute('DELETE FROM Onboardings WHERE id = ?', array($onboardingId));
            return array(null, array('email' => (string) $resp->getData()), null, false);
        }
        $data = $resp->getData();
        $invitation = $data['invitation'];
        Db::db()->Execute('UPDATE Onboardings SET invitation = ? WHERE id = ?', array((int) $invitation->id, $onboardingId));
        $sent = (int) $invitation->invitation_status === UserInvitationService::INVITATION_EMAIL_SENT;
        $this->log($onboardingId, null, $user->id, 'invited', array('email' => $email, 'sent' => $sent));

        // Steps for HR, the manager or a named person can start before the hire accepts.
        OnboardingNotifier::ownersAssigned($onboardingId, array('hr', 'manager', 'buddy', 'employee'));

        return array($onboardingId, array(), $data['url'], $sent);
    }

    private function snapshotSteps(Onboarding $o, array $steps)
    {
        $order = 0;
        foreach ($steps as $step) {
            $order++;
            if (!Integrations::available($step['type'])) {
                // The extension behind this step is not installed: leave it out.
                continue;
            }
            $s = new OnboardingStep();
            $s->onboarding = $o->id;
            $s->sort_order = $order;
            $s->phase = $step['phase'];
            $s->type = $step['type'];
            $s->title = $step['title'];
            $s->instructions = $step['instructions'];
            $s->owner_type = $step['owner_type'];
            $s->owner_employee = $this->resolveOwner($step['owner_type'], $step['owner_employee'], $o);
            if ($s->owner_type === 'buddy' && empty($s->owner_employee)) {
                // No buddy was chosen: HR does the buddy's tasks, so they have an owner.
                $s->owner_type = 'hr';
            }
            $s->due_offset_days = (int) $step['due_offset_days'];
            // Due relative to the start date, but never before the invitation: a hire
            // invited a week before starting should not begin with overdue steps.
            $due = date('Y-m-d', strtotime($o->start_date . ' ' . sprintf('%+d', (int) $step['due_offset_days']) . ' days'));
            $s->due_date = $due < date('Y-m-d') ? date('Y-m-d') : $due;
            $s->required = !empty($step['required']) ? 1 : 0;
            $s->needs_review = !empty($step['needs_review']) ? 1 : 0;
            $s->config = json_encode($step['config']);
            $s->status = 'Pending';
            $s->created = Db::now();
            $s->updated = Db::now();
            if (!$s->Save()) {
                throw new \RuntimeException('Could not save a step: ' . $s->ErrorMsg());
            }
        }
    }

    private function resolveOwner($ownerType, $ownerEmployee, $o)
    {
        if ($ownerType === 'manager') {
            return empty($o->supervisor) ? null : (int) $o->supervisor;
        }
        if ($ownerType === 'buddy') {
            return empty($o->buddy) ? null : (int) $o->buddy;
        }
        if ($ownerType === 'employee') {
            return empty($ownerEmployee) ? null : (int) $ownerEmployee;
        }
        return null;
    }

    // --- invitation hooks ---------------------------------------------------------------

    /** Core calls this after an onboarding invitation is accepted. */
    public static function onInvitationAccepted($invitation, $employee, $user)
    {
        if ($invitation->context !== 'onboarding' || empty($invitation->context_ref)) {
            return;
        }
        (new self())->accepted((int) $invitation->context_ref, $employee, $user);
    }

    public function accepted($onboardingId, $employee, $user)
    {
        $o = $this->find($onboardingId);
        if ($o === null) {
            return;
        }
        Db::db()->Execute(
            "UPDATE Onboardings SET employee = ?, status = 'InProgress', accepted_at = ?, updated = ? WHERE id = ?",
            array((int) $employee->id, Db::now(), Db::now(), (int) $onboardingId)
        );
        $this->log($onboardingId, null, $user->id, 'accepted', array());
        $this->startLinkedSteps($onboardingId);
        // A hire who starts today (or whose start date has passed) is active straight away.
        if ($o['start_date'] <= date('Y-m-d')) {
            $this->activate($onboardingId, null);
        }
        OnboardingNotifier::accepted($onboardingId);
    }

    /** Start the form / signing / course steps of an accepted hire that are not started. */
    public function startLinkedSteps($onboardingId)
    {
        $o = $this->find($onboardingId);
        if ($o === null || empty($o['employee'])) {
            return;
        }
        $hrUser = $this->actingHrUser($o);
        $steps = Db::rows(
            "SELECT * FROM OnboardingSteps WHERE onboarding = ? AND type IN ('form', 'esign', 'course') AND link_ref IS NULL AND status = 'Pending' ORDER BY sort_order",
            array((int) $onboardingId)
        );
        if (empty($steps)) {
            return;
        }
        // When the hire accepts the invitation nobody is signed in yet (the session is
        // created after this hook). Other extensions read the signed-in user (Learn
        // checks the course's access rules through PermissionManager), so the user who
        // invited the hire acts for HR for the length of this call.
        $base = BaseService::getInstance();
        $actAs = empty($base->getCurrentUser()) && !empty($hrUser->id);
        if ($actAs) {
            $base->setCurrentUser($hrUser);
        }
        try {
            foreach ($steps as $step) {
                list($ref, $error) = Integrations::start($step, (int) $o['employee'], $hrUser);
                $response = $error === null ? null : json_encode(array('error' => $error));
                Db::db()->Execute(
                    'UPDATE OnboardingSteps SET link_ref = ?, response = ?, updated = ? WHERE id = ?',
                    array($ref, $response, Db::now(), (int) $step['id'])
                );
                $this->log($onboardingId, (int) $step['id'], null, $error === null ? 'step_started' : 'step_start_failed', $error === null ? array() : array('error' => $error));
            }
        } finally {
            if ($actAs) {
                $base->setCurrentUser(null);
            }
        }
    }

    /** The user who invited the hire (falls back to any Admin) acts for HR in other extensions. */
    private function actingHrUser(array $o)
    {
        $user = new \Users\Common\Model\User();
        if (!empty($o['created_by'])) {
            $user->Load('id = ?', array((int) $o['created_by']));
        }
        if (empty($user->id)) {
            $user->Load("user_level = 'Admin' ORDER BY id LIMIT 1");
        }
        return $user;
    }

    /**
     * Extra content on the accept page and in the invitation email for onboarding
     * invitations: a personal welcome, the start date, the job title.
     */
    public static function acceptPageExtras($invitation)
    {
        if ($invitation->context !== 'onboarding' || empty($invitation->context_ref)) {
            return null;
        }
        $rows = Db::rows(
            'SELECT o.*, j.name AS job_title_name FROM Onboardings o LEFT JOIN JobTitles j ON j.id = o.job_title WHERE o.id = ?',
            array((int) $invitation->context_ref)
        );
        if (empty($rows)) {
            return null;
        }
        $o = $rows[0];
        $company = SettingsManager::getInstance()->getSetting('Company: Name');
        if (empty($company) || $company === 'Sample Company Pvt Ltd') {
            $company = 'IceHrm';
        }
        if (isset($o['track']) && $o['track'] === 'workforce') {
            // Existing staff: no start date, the company is moving onto IceHrm.
            $html = \Classes\Email\EmailParts::text(sprintf(
                '%s now uses IceHrm to keep employee records and run HR. After you set up your account, check and complete your details.',
                \Classes\Email\EmailParts::companyName()
            ));
            if (!empty($o['welcome_message'])) {
                $html .= \Classes\Email\EmailParts::text($o['welcome_message']);
            }
            return array(
                'title' => sprintf('%s now uses IceHrm', $company),
                'message' => !empty($o['welcome_message'])
                    ? $o['welcome_message']
                    : 'Choose a password to set up your account. You will then see a short list of details to check and complete.',
                'details' => array(),
                'email_html' => $html,
            );
        }
        $start = date('l, j F Y', strtotime($o['start_date']));
        $details = array('Start date' => $start);
        if (!empty($o['job_title_name'])) {
            $details['Role'] = $o['job_title_name'];
        }
        $message = !empty($o['welcome_message'])
            ? $o['welcome_message']
            : 'We are excited to have you on the team. Choose a password to set up your account. You will then see a short checklist to complete before your first day.';
        $html = '';
        if (!empty($o['welcome_message'])) {
            $html .= \Classes\Email\EmailParts::text($o['welcome_message']);
        }
        $html .= \Classes\Email\EmailParts::details(array(
            'Start date' => \Classes\Email\EmailParts::date($o['start_date']),
            'Role' => !empty($o['job_title_name']) ? $o['job_title_name'] : '',
        ));
        $html .= \Classes\Email\EmailParts::text('After you set up your account, you will see a short checklist of things to do before your first day.');
        return array(
            'title' => sprintf('Welcome to %s, %s!', $company, $o['first_name']),
            'message' => $message,
            'details' => $details,
            'email_html' => $html,
        );
    }

    // --- lifecycle ----------------------------------------------------------------------

    /** Employee becomes Active (full app, directory, payroll). Idempotent. */
    public function activate($onboardingId, $userId)
    {
        $o = $this->find($onboardingId);
        if ($o === null || empty($o['employee']) || !empty($o['activated_at'])) {
            return false;
        }
        Db::db()->Execute(
            "UPDATE Employees SET status = 'Active' WHERE id = ? AND status = 'Onboarding'",
            array((int) $o['employee'])
        );
        Db::db()->Execute(
            'UPDATE Onboardings SET activated_at = ?, updated = ? WHERE id = ?',
            array(Db::now(), Db::now(), (int) $onboardingId)
        );
        $this->log($onboardingId, null, $userId, 'activated', array());
        $this->completeIfDone($onboardingId);
        return true;
    }

    /**
     * HR finishes the onboarding now: the employee becomes active, and steps nobody has
     * done yet are skipped (their forms withdrawn, as Skip does), so nothing is left
     * open on a closed onboarding. Steps waiting for review stay: HR can still review
     * them after the onboarding is completed.
     */
    public function complete($onboardingId, $user)
    {
        $o = $this->find($onboardingId);
        if ($o === null || !in_array($o['status'], self::OPEN_STATUSES, true)) {
            return 'This onboarding is not open.';
        }
        if (empty($o['employee'])) {
            return 'The new hire has not accepted the invitation yet.';
        }
        (new StepService())->sync((int) $onboardingId);
        $this->activate($onboardingId, $user->id);
        $skipped = (new StepService())->skipOpen((int) $onboardingId, $user, StepService::OPEN);
        if ($skipped > 0) {
            $this->log($onboardingId, null, $user->id, 'open_steps_skipped', array('count' => $skipped));
        }
        $current = $this->find($onboardingId);
        if ($current !== null && $current['status'] !== 'Completed') {
            Db::db()->Execute(
                "UPDATE Onboardings SET status = 'Completed', completed_at = ?, updated = ? WHERE id = ? AND status <> 'Completed'",
                array(Db::now(), Db::now(), (int) $onboardingId)
            );
            $this->log($onboardingId, null, $user->id, 'completed', array('by_hr' => true));
        }
        $this->publishDocuments($onboardingId);
        return null;
    }

    /**
     * Documents uploaded during onboarding stay hidden from the employee's Documents
     * until the onboarding is completed; then the approved ones (and those that needed
     * no review) are added. Documents still waiting for review follow when approved.
     */
    public function publishDocuments($onboardingId)
    {
        Db::db()->Execute(
            "UPDATE EmployeeDocuments d JOIN OnboardingSteps s ON s.link_ref = d.id JOIN Onboardings o ON o.id = s.onboarding"
            . " SET d.hidden = 0, d.status = 'Active', d.visible_to = 'Owner Only'"
            . " WHERE s.onboarding = ? AND s.type = 'document' AND s.status = 'Done' AND d.employee = o.employee",
            array((int) $onboardingId)
        );
    }

    public function completeIfDone($onboardingId)
    {
        $o = $this->find($onboardingId);
        if ($o === null || $o['status'] !== 'InProgress' || empty($o['activated_at'])) {
            return;
        }
        $open = Db::rows(
            "SELECT COUNT(*) AS c FROM OnboardingSteps WHERE onboarding = ? AND required = 1 AND status NOT IN ('Done', 'Skipped')",
            array((int) $onboardingId)
        );
        if ((int) $open[0]['c'] === 0) {
            Db::db()->Execute(
                "UPDATE Onboardings SET status = 'Completed', completed_at = ?, updated = ? WHERE id = ?",
                array(Db::now(), Db::now(), (int) $onboardingId)
            );
            $this->publishDocuments($onboardingId);
            $this->log($onboardingId, null, null, 'completed', array());
            OnboardingNotifier::allDone($onboardingId);
        }
    }

    /**
     * Cancel. Before acceptance the link is withdrawn. After acceptance (and before the
     * start date) the login is removed and the employee is marked Terminated with the
     * termination type 'Onboarding cancelled' (shown on the Resigned tab; such an employee
     * cannot be activated). Their record and uploads are kept until the onboarding is
     * deleted.
     */
    public function cancel($onboardingId, $user)
    {
        $o = $this->find($onboardingId);
        if ($o === null || !in_array($o['status'], self::OPEN_STATUSES, true)) {
            return 'This onboarding is not open.';
        }
        if (!empty($o['activated_at'])) {
            return 'This employee has already started. Use the normal resignation steps instead.';
        }
        if (!empty($o['invitation'])) {
            (new UserInvitationService())->revoke((int) $o['invitation']);
        }
        // Withdraw what was sent to the hire (forms), as Skip does, and close the steps.
        (new StepService())->skipOpen((int) $onboardingId, $user, array('Pending', 'ChangesRequested', 'Submitted'));
        if (!empty($o['employee'])) {
            Db::db()->Execute('DELETE FROM Users WHERE employee = ?', array((int) $o['employee']));
            Db::db()->Execute(
                "UPDATE Employees SET status = 'Terminated', termination_date = ?, termination_type = 'Onboarding cancelled' WHERE id = ? AND status = 'Onboarding'",
                array(date('Y-m-d'), (int) $o['employee'])
            );
        }
        Db::db()->Execute(
            "UPDATE Onboardings SET status = 'Cancelled', updated = ? WHERE id = ?",
            array(Db::now(), (int) $onboardingId)
        );
        $this->log($onboardingId, null, $user->id, 'cancelled', array());
        return null;
    }

    /**
     * Remove a cancelled onboarding with its steps, activity and invitation. When the
     * hire had accepted (cancel left their record marked as left), their employee record
     * and all its data go too: see HireRecordRemover.
     */
    public function delete($onboardingId)
    {
        $o = $this->find($onboardingId);
        if ($o === null) {
            return 'This onboarding was not found.';
        }
        if ($o['status'] !== 'Cancelled') {
            return 'Only cancelled onboardings can be deleted.';
        }
        $id = (int) $onboardingId;
        $remover = new HireRecordRemover();
        if (!empty($o['employee'])) {
            $blocker = $remover->blocker($o);
            if ($blocker !== null) {
                return $blocker;
            }
        }
        try {
            if (!empty($o['employee'])) {
                $remover->remove((int) $o['employee']);
            }
            Db::transaction(function () use ($id, $o) {
                Db::db()->Execute('DELETE FROM OnboardingActivity WHERE onboarding = ?', array($id));
                Db::db()->Execute('DELETE FROM OnboardingSteps WHERE onboarding = ?', array($id));
                if (!empty($o['invitation'])) {
                    Db::db()->Execute(
                        "DELETE FROM UserInvitations WHERE id = ? AND context = 'onboarding'",
                        array((int) $o['invitation'])
                    );
                }
                Db::db()->Execute('DELETE FROM Onboardings WHERE id = ?', array($id));
            });
        } catch (\Exception $e) {
            LogManager::getInstance()->error($e->getMessage());
            return 'The onboarding could not be deleted. Please try again.';
        }
        return null;
    }

    public function resend($onboardingId, $user)
    {
        $o = $this->find($onboardingId);
        if ($o === null || $o['status'] !== 'Invited' || empty($o['invitation'])) {
            return array(null, 'Only invitations that have not been accepted can be resent.');
        }
        $resp = (new UserInvitationService())->resend((int) $o['invitation']);
        if ($resp->getStatus() !== IceResponse::SUCCESS) {
            return array(null, (string) $resp->getData());
        }
        $data = $resp->getData();
        $this->log($onboardingId, null, $user->id, 'invite_resent', array('sent' => is_array($data) && !empty($data['sent'])));
        return array($data, null);
    }

    /** Hourly: activate hires whose start date has come. */
    public function activateDue()
    {
        $rows = Db::rows(
            "SELECT id FROM Onboardings WHERE status = 'InProgress' AND employee IS NOT NULL AND activated_at IS NULL AND start_date <= CURDATE() LIMIT 200"
        );
        foreach ($rows as $row) {
            $this->activate((int) $row['id'], null);
        }
    }

    // --- reading ------------------------------------------------------------------------

    public function find($id)
    {
        $rows = Db::rows('SELECT * FROM Onboardings WHERE id = ?', array((int) $id));
        return empty($rows) ? null : $rows[0];
    }

    /**
     * Dashboard/Home banner (DashboardService::addBannerProvider) for someone who has
     * accepted their invitation but still has steps of their own to do.
     */
    public function checklistBanner($user)
    {
        if (empty($user) || empty($user->employee)) {
            return null;
        }
        $o = $this->findForEmployee((int) $user->employee);
        if ($o === null || $o['status'] !== 'InProgress') {
            return null;
        }
        (new StepService())->sync((int) $o['id']);
        $steps = Db::rows(
            "SELECT status FROM OnboardingSteps WHERE onboarding = ? AND owner_type = 'new_hire' AND status <> 'Skipped'",
            array((int) $o['id'])
        );
        $todo = 0;
        foreach ($steps as $step) {
            if (in_array($step['status'], StepService::OPEN, true)) {
                $todo++;
            }
        }
        if ($todo === 0) {
            return null;
        }
        $workforce = WorkflowService::track(isset($o['track']) ? $o['track'] : null) === 'workforce';
        return array(
            'key' => 'onboarding-checklist',
            'tone' => 'green',
            'icon' => 'rocket',
            'title' => $workforce ? 'Complete your details' : 'Complete your onboarding',
            'message' => $todo === 1
                ? 'You have 1 step left on your checklist.'
                : 'You have {count} steps left on your checklist.',
            'vars' => array('count' => $todo),
            'progress' => array('done' => count($steps) - $todo, 'total' => count($steps)),
            'actions' => array(
                array('label' => 'Open my checklist', 'g' => 'extension', 'n' => 'onboarding|user', 'primary' => true),
            ),
            'dismissible' => true,
        );
    }

    /**
     * The employee's onboarding. Cancelled ones only when asked (the profile section
     * shows them, so HR can reach and delete them); the employee's own pages never.
     */
    public function findForEmployee($employeeId, $includeCancelled = false)
    {
        $rows = Db::rows(
            'SELECT * FROM Onboardings WHERE employee = ?' . ($includeCancelled ? '' : " AND status <> 'Cancelled'") . ' ORDER BY id DESC LIMIT 1',
            array((int) $employeeId)
        );
        return empty($rows) ? null : $rows[0];
    }

    /**
     * Should "My Onboarding" be in this user's menu? Only for someone with an
     * onboarding of their own (not cancelled), or who owns an open onboarding task
     * (a buddy or named person) and cannot open the admin module, whose "My onboarding
     * tasks" tab lists the tasks of HR and managers.
     */
    public function showsMyOnboarding($user)
    {
        if (empty($user) || empty($user->employee)) {
            return false;
        }
        $employeeId = (int) $user->employee;
        if ($this->findForEmployee($employeeId) !== null) {
            return true;
        }
        if (OnboardingAccess::canOpenAdminModule($user)) {
            return false;
        }
        return !empty(Db::rows(
            "SELECT s.id FROM OnboardingSteps s JOIN Onboardings o ON o.id = s.onboarding
             WHERE s.owner_employee = ? AND s.owner_type <> 'new_hire' AND o.status IN ('Invited', 'InProgress') LIMIT 1",
            array($employeeId)
        ));
    }

    /**
     * New hires for the admin list. HR sees all; a manager sees the people they manage.
     */
    public function listForAdmin($user, array $filters)
    {
        $where = array("o.kind = 'onboarding'", 'o.track = ?');
        $params = array(WorkflowService::track(isset($filters['track']) ? $filters['track'] : null));
        $status = isset($filters['status']) ? (string) $filters['status'] : 'open';
        if ($status === 'open') {
            $where[] = "o.status IN ('Invited', 'InProgress')";
        } elseif (in_array($status, array('Invited', 'InProgress', 'Completed', 'Cancelled'), true)) {
            $where[] = 'o.status = ?';
            $params[] = $status;
        }
        if (!empty($filters['search'])) {
            $where[] = "(CONCAT(o.first_name, ' ', o.last_name) LIKE ? OR o.email LIKE ?)";
            $like = '%' . mb_substr((string) $filters['search'], 0, 100) . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $rows = Db::rows(
            'SELECT o.* FROM Onboardings o WHERE ' . implode(' AND ', $where) . ' ORDER BY o.start_date ASC, o.id DESC LIMIT 500',
            $params
        );
        $isHr = OnboardingAccess::isHr($user);
        $out = array();
        foreach ($rows as $row) {
            if (!$isHr && !OnboardingAccess::canView($user, $row)) {
                continue;
            }
            $out[] = $this->present($row);
        }
        return $out;
    }

    public function counts($user, $track = 'new_hire')
    {
        $out = array('open' => 0, 'Invited' => 0, 'InProgress' => 0, 'Completed' => 0, 'Cancelled' => 0, 'review' => 0);
        $isHr = OnboardingAccess::isHr($user);
        $track = WorkflowService::track($track);
        foreach (Db::rows("SELECT id, status, employee FROM Onboardings WHERE kind = 'onboarding' AND track = ?", array($track)) as $row) {
            if (!$isHr && !OnboardingAccess::canView($user, $row)) {
                continue;
            }
            $out[$row['status']] = isset($out[$row['status']]) ? $out[$row['status']] + 1 : 1;
            if (in_array($row['status'], self::OPEN_STATUSES, true)) {
                $out['open']++;
            }
        }
        if ($isHr) {
            $r = Db::rows(
                "SELECT COUNT(*) AS c FROM OnboardingSteps s JOIN Onboardings o ON o.id = s.onboarding WHERE s.status = 'Submitted' AND s.type IN ('document', 'form') AND o.status IN ('Invited', 'InProgress') AND o.track = ?",
                array($track)
            );
            $out['review'] = (int) $r[0]['c'];
        }
        return $out;
    }

    /** One hire with progress, for lists. */
    public function present(array $o)
    {
        $steps = Db::rows('SELECT status, required, due_date, type, owner_type FROM OnboardingSteps WHERE onboarding = ?', array((int) $o['id']));
        $total = 0;
        $done = 0;
        $overdue = 0;
        $review = 0;
        $today = date('Y-m-d');
        // Submitted documents and forms wait for HR, also after completion (not when cancelled).
        $reviewable = in_array($o['status'], self::REVIEW_STATUSES, true);
        foreach ($steps as $s) {
            if ($s['status'] === 'Skipped') {
                continue;
            }
            $total++;
            if ($s['status'] === 'Done') {
                $done++;
            } elseif (!empty($s['due_date']) && $s['due_date'] < $today) {
                $overdue++;
            }
            if ($reviewable && $s['status'] === 'Submitted' && ($s['type'] === 'document' || $s['type'] === 'form')) {
                $review++;
            }
        }
        $names = $this->names(array($o['supervisor'], $o['buddy']));
        $lookups = Db::rows(
            'SELECT (SELECT title FROM CompanyStructures WHERE id = ?) AS department_name, (SELECT name FROM JobTitles WHERE id = ?) AS job_title_name',
            array((int) $o['department'], (int) $o['job_title'])
        );
        $invitationState = null;
        if (!empty($o['invitation']) && $o['status'] === 'Invited') {
            $inv = new \Model\UserInvitation();
            $inv->Load('id = ?', array((int) $o['invitation']));
            if (!empty($inv->id)) {
                $invitationState = (new UserInvitationService())->getTokenState($inv);
                if ((int) $inv->invitation_status === UserInvitationService::INVITATION_EMAIL_ERROR && $invitationState === 'valid') {
                    $invitationState = 'email_failed';
                }
            }
        }
        return array(
            'id' => (int) $o['id'],
            'name' => trim($o['first_name'] . ' ' . $o['last_name']),
            'first_name' => $o['first_name'],
            'last_name' => $o['last_name'],
            'email' => $o['email'],
            'start_date' => $o['start_date'],
            'track' => WorkflowService::track(isset($o['track']) ? $o['track'] : null),
            'joined_date' => isset($o['joined_date']) ? $o['joined_date'] : null,
            'status' => $o['status'],
            'workflow' => $o['template_name'],
            'employee' => empty($o['employee']) ? null : (int) $o['employee'],
            'department_name' => empty($lookups) ? null : $lookups[0]['department_name'],
            'job_title_name' => empty($lookups) ? null : $lookups[0]['job_title_name'],
            'manager_name' => isset($names[(int) $o['supervisor']]) ? $names[(int) $o['supervisor']] : null,
            'buddy_name' => isset($names[(int) $o['buddy']]) ? $names[(int) $o['buddy']] : null,
            'invitation_state' => $invitationState,
            'accepted_at' => $o['accepted_at'],
            'activated_at' => $o['activated_at'],
            'completed_at' => $o['completed_at'],
            'progress' => array('done' => $done, 'total' => $total, 'overdue' => $overdue, 'review' => $review),
            'days_to_start' => (int) floor((strtotime($o['start_date']) - strtotime($today)) / 86400),
        );
    }

    public function detail(array $o, $viewer)
    {
        (new StepService())->sync((int) $o['id']);
        $o = $this->find($o['id']);
        $out = $this->present($o);
        $out['welcome_message'] = $o['welcome_message'];
        $out['welcome_video_url'] = $o['welcome_video_url'];
        $out['first_day_info'] = $o['first_day_info'];
        $out['steps'] = (new StepService())->listForOnboarding((int) $o['id'], $viewer);
        $out['activity'] = $this->activity((int) $o['id']);
        return $out;
    }

    public function activity($onboardingId)
    {
        $rows = Db::rows(
            'SELECT a.*, u.username, e.first_name AS actor_first, e.last_name AS actor_last, s.title AS step_title
             FROM OnboardingActivity a
             LEFT JOIN Users u ON u.id = a.actor LEFT JOIN Employees e ON e.id = u.employee
             LEFT JOIN OnboardingSteps s ON s.id = a.step
             WHERE a.onboarding = ? ORDER BY a.id DESC LIMIT 200',
            array((int) $onboardingId)
        );
        $out = array();
        foreach ($rows as $row) {
            $details = json_decode((string) $row['details'], true);
            $name = trim($row['actor_first'] . ' ' . $row['actor_last']);
            $out[] = array(
                'action' => $row['action'],
                'at' => $row['created'],
                'by' => $name !== '' ? $name : $row['username'],
                'step' => $row['step_title'],
                'details' => is_array($details) ? $details : array(),
            );
        }
        return $out;
    }

    public function log($onboardingId, $stepId, $userId, $action, array $details)
    {
        $a = new OnboardingActivity();
        $a->onboarding = (int) $onboardingId;
        $a->step = empty($stepId) ? null : (int) $stepId;
        $a->actor = empty($userId) ? null : (int) $userId;
        $a->action = $action;
        $a->details = empty($details) ? null : json_encode($details);
        $a->created = Db::now();
        $a->Save();
    }

    /** @return array id => "First Last" */
    public function names(array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return array();
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $out = array();
        foreach (Db::rows("SELECT id, first_name, last_name FROM Employees WHERE id IN ($in)", $ids) as $row) {
            $out[(int) $row['id']] = trim($row['first_name'] . ' ' . $row['last_name']);
        }
        return $out;
    }

    private function str(array $input, $key, $max)
    {
        return isset($input[$key]) && is_scalar($input[$key]) ? mb_substr(trim((string) $input[$key]), 0, $max) : '';
    }
}
