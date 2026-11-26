<?php

namespace OnboardingAdmin;

use Classes\BaseService;

/**
 * Doing the steps of an onboarding.
 *
 *   Statuses  Pending (to do), Submitted (a document waiting for HR review),
 *             ChangesRequested (HR or the linked extension sent it back), Done, Skipped.
 *
 *   new hire  info (viewed), acknowledge (ticked + typed name), document (uploaded
 *             file → an EmployeeDocuments row, Draft until HR approves when the step
 *             needs review). Form, signing and course steps are done in Forms, E-Sign
 *             and Learn; sync() copies their status back.
 *   others    task (marked done, with an optional note) and asset (HR picks the asset
 *             that is handed over) by HR, the manager, the buddy or a named person.
 *   HR        approve / request changes on documents, skip a step, mark any step done.
 */
class StepService
{
    const OPEN = array('Pending', 'ChangesRequested');

    // --- reading ------------------------------------------------------------------------

    public function find($id)
    {
        $rows = Db::rows('SELECT * FROM OnboardingSteps WHERE id = ?', array((int) $id));
        return empty($rows) ? null : $rows[0];
    }

    /**
     * Steps of one onboarding, shaped for the viewer. $viewer = ['user' => User,
     * 'is_hr' => bool, 'employee' => current profile id, 'is_new_hire' => bool].
     */
    public function listForOnboarding($onboardingId, array $viewer)
    {
        $rows = Db::rows('SELECT * FROM OnboardingSteps WHERE onboarding = ? ORDER BY sort_order, id', array((int) $onboardingId));
        $o = (new OnboardingService())->find($onboardingId);
        return $this->presentMany($rows, $o, $viewer);
    }

    public function presentMany(array $rows, $o, array $viewer)
    {
        $ids = array();
        foreach ($rows as $row) {
            $ids[] = $row['owner_employee'];
        }
        $names = (new OnboardingService())->names($ids);
        $out = array();
        foreach ($rows as $row) {
            $out[] = $this->present($row, $o, $viewer, $names);
        }
        return $out;
    }

    public function present(array $row, $o, array $viewer, array $names = array())
    {
        $config = Integrations::config($row);
        $response = json_decode((string) $row['response'], true);
        $response = is_array($response) ? $response : array();
        $today = date('Y-m-d');
        $open = in_array($row['status'], self::OPEN, true);

        $publicConfig = array();
        foreach (array('text', 'link', 'link_label') as $key) {
            if (isset($config[$key])) {
                $publicConfig[$key] = $config[$key];
            }
        }
        $ownerType = $row['owner_type'];
        if ($ownerType === 'buddy' && empty($row['owner_employee'])) {
            // Onboardings started before buddy tasks fell back to HR: HR does them.
            $ownerType = 'hr';
        }
        $item = array(
            'id' => (int) $row['id'],
            'phase' => $row['phase'],
            'type' => $row['type'],
            'title' => $row['title'],
            'instructions' => $row['instructions'],
            'owner_type' => $ownerType,
            'owner_employee' => empty($row['owner_employee']) ? null : (int) $row['owner_employee'],
            'owner_name' => !empty($row['owner_employee']) && isset($names[(int) $row['owner_employee']]) ? $names[(int) $row['owner_employee']] : null,
            'due_date' => $row['due_date'],
            'overdue' => $open && !empty($row['due_date']) && $row['due_date'] < $today,
            'required' => (bool) $row['required'],
            'needs_review' => (bool) $row['needs_review'],
            'status' => $row['status'],
            'config' => $publicConfig,
            'subject' => Integrations::describe($row['type'], $config),
            'link_url' => empty($row['link_ref']) ? null : Integrations::linkUrl($row),
            'started' => !empty($row['link_ref']),
            // HR and managers open the answers of a form step in the Forms review drawer
            // (Forms decides what each of them may see there).
            'form_assignment' => $row['type'] === 'form' && !empty($row['link_ref']) && empty($viewer['is_new_hire'])
                ? (int) $row['link_ref'] : null,
            // The new hire's own form, so the checklist can open it directly.
            'my_form' => $row['type'] === 'form' && !empty($row['link_ref']) && !empty($viewer['is_new_hire'])
                ? (int) $row['link_ref'] : null,
            // The new hire's own signing request, so the checklist can open it directly.
            'my_esign' => $row['type'] === 'esign' && !empty($row['link_ref']) && !empty($viewer['is_new_hire'])
                ? (int) $row['link_ref'] : null,
            // Why a linked step could not be started is for HR (who can try again); the
            // new hire sees "This step is being prepared".
            'error' => isset($response['error']) && empty($viewer['is_new_hire']) ? $response['error'] : null,
            'review_note' => $row['review_note'],
            'completed_at' => $row['completed_at'],
            'note' => isset($response['note']) ? $response['note'] : null,
        );
        if ($row['type'] === 'acknowledge' && !empty($response['acknowledged_at'])) {
            $item['acknowledgement'] = array(
                'name' => isset($response['name']) ? $response['name'] : null,
                'at' => $response['acknowledged_at'],
            );
        }
        // Onboarding documents are for the employee and admins only, not managers.
        $seesDocuments = !empty($viewer['is_hr']) || !empty($viewer['is_new_hire']);
        if ($row['type'] === 'document' && !empty($response['file']) && $seesDocuments) {
            $item['file'] = array(
                'name' => isset($response['original']) ? $response['original'] : 'Document',
                'url' => UploadGuard::fileUrl($response['file']),
            );
        }
        if ($row['type'] === 'asset' && !empty($response['asset_label'])) {
            $item['asset'] = $response['asset_label'];
        }
        if ($row['type'] === 'asset') {
            $item['asset_type'] = Integrations::assetTypeId($config);
        }
        if ($row['type'] === 'set_manager') {
            if (!empty($response['manager_name'])) {
                $item['manager_name'] = $response['manager_name'];
            }
            // Blocked until the person accepts: there is no employee record to update yet.
            $item['waiting_for_accept'] = $o !== null && empty($o['employee']);
        }
        $onboardingOpen = $o !== null && in_array($o['status'], OnboardingService::OPEN_STATUSES, true);
        $hr = !empty($viewer['is_hr']) && $onboardingOpen;
        $item['can_complete'] = $this->canComplete($row, $o, $viewer);
        // Documents are reviewed here; forms are reviewed in Forms ("Review answers").
        // Reviewing stays possible after the onboarding is completed (not when cancelled).
        // HR can fill in a form step for the employee and approve it (Forms decides).
        $item['can_fill'] = !empty($viewer['is_hr']) && $row['type'] === 'form' && !empty($row['link_ref'])
            && $o !== null && in_array($o['status'], OnboardingService::REVIEW_STATUSES, true)
            && !in_array($row['status'], array('Done', 'Skipped'), true)
            && Integrations::formHrCanFill((int) $row['link_ref']);
        $item['can_review'] = !empty($viewer['is_hr']) && $o !== null && in_array($o['status'], OnboardingService::REVIEW_STATUSES, true)
            && $row['status'] === 'Submitted' && $row['type'] === 'document';
        $item['can_skip'] = $hr && $row['status'] !== 'Done' && $row['status'] !== 'Skipped';
        $item['can_retry'] = $hr && in_array($row['type'], array('form', 'esign', 'course'), true)
            && empty($row['link_ref']) && !empty($o['employee']) && $open;
        return $item;
    }

    /** May this viewer do (complete) this step? */
    public function canComplete(array $row, $o, array $viewer)
    {
        if ($this->isResubmission($row, $o) && !empty($viewer['is_new_hire']) && !empty($o['employee'])) {
            return true;
        }
        if (!in_array($row['status'], self::OPEN, true) || $o === null || !in_array($o['status'], OnboardingService::OPEN_STATUSES, true)) {
            return false;
        }
        if ($row['owner_type'] === 'new_hire') {
            // Linked steps are completed in their own extension.
            if (in_array($row['type'], array('form', 'esign', 'course'), true)) {
                return false;
            }
            return !empty($viewer['is_new_hire']) && !empty($o['employee']);
        }
        if (!empty($viewer['is_hr'])) {
            // Handing over an asset and setting the manager need the employee record,
            // which exists once the invitation is accepted.
            return !in_array($row['type'], array('asset', 'set_manager'), true) || !empty($o['employee']);
        }
        if ($row['owner_type'] === 'hr') {
            return false;
        }
        return !empty($row['owner_employee']) && (int) $row['owner_employee'] === (int) $viewer['employee'];
    }

    /**
     * People who can be chosen as the manager in a Set manager step: Active employees
     * and people whose onboarding is in progress (they have an employee record), plus
     * people only invited so far, shown but not selectable.
     */
    public function managerOptions(array $o)
    {
        $self = empty($o['employee']) ? 0 : (int) $o['employee'];
        $onboarding = array();
        foreach (Db::rows("SELECT employee FROM Onboardings WHERE status = 'InProgress' AND employee IS NOT NULL") as $row) {
            $onboarding[(int) $row['employee']] = true;
        }
        $out = array();
        $rows = Db::rows(
            "SELECT id, first_name, last_name, status FROM Employees WHERE status IN ('Active', 'Onboarding') AND id <> ? ORDER BY first_name, last_name",
            array($self)
        );
        foreach ($rows as $row) {
            $out[] = array(
                'value' => (int) $row['id'],
                'label' => trim($row['first_name'] . ' ' . $row['last_name']),
                'onboarding' => $row['status'] === 'Onboarding' || isset($onboarding[(int) $row['id']]),
                'disabled' => false,
            );
        }
        $invited = Db::rows(
            "SELECT id, first_name, last_name FROM Onboardings WHERE status = 'Invited' AND employee IS NULL AND id <> ? ORDER BY first_name, last_name",
            array((int) $o['id'])
        );
        foreach ($invited as $row) {
            $out[] = array(
                'value' => 'invited:' . (int) $row['id'],
                'label' => trim($row['first_name'] . ' ' . $row['last_name']),
                'onboarding' => true,
                'disabled' => true,
            );
        }
        return $out;
    }

    /** Complete a Set manager step: the employee's supervisor becomes $managerId. */
    private function setManager(array $step, array $o, $user, $managerId)
    {
        if (empty($o['employee'])) {
            return sprintf('Waiting for %s to accept the invitation.', trim($o['first_name'] . ' ' . $o['last_name']));
        }
        $employeeId = (int) $o['employee'];
        if ($managerId <= 0 || $managerId === $employeeId) {
            return 'Choose the manager.';
        }
        $manager = Db::rows(
            "SELECT id, first_name, last_name FROM Employees WHERE id = ? AND status IN ('Active', 'Onboarding')",
            array($managerId)
        );
        if (empty($manager)) {
            return 'This person cannot be chosen as the manager.';
        }
        // No loops: the employee must not already be above the manager.
        $seen = array();
        $cursor = $managerId;
        while ($cursor && !isset($seen[$cursor]) && count($seen) < 50) {
            $seen[$cursor] = true;
            $up = Db::rows('SELECT supervisor FROM Employees WHERE id = ?', array($cursor));
            $cursor = empty($up) || empty($up[0]['supervisor']) ? 0 : (int) $up[0]['supervisor'];
            if ($cursor === $employeeId) {
                return 'This person reports to the employee, so they cannot be their manager.';
            }
        }
        $name = trim($manager[0]['first_name'] . ' ' . $manager[0]['last_name']);
        Db::db()->Execute('UPDATE Employees SET supervisor = ? WHERE id = ?', array($managerId, $employeeId));
        Db::db()->Execute('UPDATE Onboardings SET supervisor = ?, updated = ? WHERE id = ?', array($managerId, Db::now(), (int) $o['id']));
        // Open steps owned by "the manager" move to the new manager.
        Db::db()->Execute(
            "UPDATE OnboardingSteps SET owner_employee = ?, updated = ? WHERE onboarding = ? AND owner_type = 'manager' AND status NOT IN ('Done', 'Skipped')",
            array($managerId, Db::now(), (int) $o['id'])
        );
        $this->finish($step, $user, array('manager' => $managerId, 'manager_name' => $name));
        $service = new OnboardingService();
        $service->log($o['id'], $step['id'], $user->id, 'manager_set', array('manager' => $name));
        OnboardingNotifier::ownersAssigned((int) $o['id'], array('manager'));
        $service->completeIfDone($o['id']);
        return null;
    }

    /** Steps assigned to the signed-in person (HR, manager, buddy, named person). */
    public function myTasks($user, $employeeId, $isHr, $includeDone)
    {
        $where = "o.status IN ('Invited', 'InProgress') AND s.owner_type <> 'new_hire'";
        $params = array();
        $mine = array();
        if (!empty($employeeId)) {
            $mine[] = 's.owner_employee = ?';
            $params[] = (int) $employeeId;
        }
        if ($isHr) {
            // Buddy tasks without a buddy fall back to HR.
            $mine[] = "(s.owner_type = 'hr' OR (s.owner_type = 'buddy' AND s.owner_employee IS NULL))";
        }
        if (empty($mine)) {
            return array();
        }
        $where .= ' AND (' . implode(' OR ', $mine) . ')';
        if (!$includeDone) {
            $where .= " AND s.status IN ('Pending', 'ChangesRequested')";
        }
        $rows = Db::rows(
            "SELECT s.*, o.first_name, o.last_name, o.start_date, o.track, o.employee AS hire_employee
             FROM OnboardingSteps s JOIN Onboardings o ON o.id = s.onboarding
             WHERE $where ORDER BY s.due_date IS NULL, s.due_date, s.id LIMIT 300",
            $params
        );
        $viewer = array('user' => $user, 'is_hr' => $isHr, 'employee' => $employeeId, 'is_new_hire' => false);
        $service = new OnboardingService();
        $out = array();
        foreach ($rows as $row) {
            $o = $service->find($row['onboarding']);
            $item = $this->present($row, $o, $viewer);
            $item['onboarding'] = (int) $row['onboarding'];
            $item['hire_name'] = trim($row['first_name'] . ' ' . $row['last_name']);
            $item['start_date'] = $row['start_date'];
            $item['track'] = WorkflowService::track($row['track']);
            $out[] = $item;
        }
        return $out;
    }

    // --- doing --------------------------------------------------------------------------

    /**
     * The new hire completes one of their steps. Returns an error or null.
     */
    public function submitByNewHire(array $step, array $o, $user, array $input)
    {
        $now = Db::now();
        $service = new OnboardingService();
        switch ($step['type']) {
            case 'info':
                $this->finish($step, $user, array('viewed_at' => $now));
                $service->log($o['id'], $step['id'], $user->id, 'step_done', array());
                break;
            case 'acknowledge':
                $name = isset($input['name']) ? mb_substr(trim((string) $input['name']), 0, 150) : '';
                if (empty($input['agree']) || $name === '') {
                    return 'Tick the box and type your full name to confirm.';
                }
                $this->finish($step, $user, array(
                    'name' => $name,
                    'acknowledged_at' => $now,
                    'ip' => isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : null,
                ));
                $service->log($o['id'], $step['id'], $user->id, 'acknowledged', array('name' => $name));
                break;
            case 'document':
                return $this->submitDocument($step, $o, $user, $input);
            default:
                return 'This step is completed somewhere else.';
        }
        $service->completeIfDone($o['id']);
        return null;
    }

    private function submitDocument(array $step, array $o, $user, array $input)
    {
        $file = isset($input['file']) ? (string) $input['file'] : '';
        if (!UploadGuard::ownsFile($file, (int) $o['employee'])) {
            return 'Upload the document first.';
        }
        $config = Integrations::config($step);
        $documentType = Integrations::documentTypeId($config);
        if (empty($documentType)) {
            return 'This document type no longer exists. Please contact HR.';
        }
        $response = json_decode((string) $step['response'], true);
        $response = is_array($response) ? $response : array();
        $original = isset($input['original']) ? mb_substr((string) $input['original'], 0, 150) : 'Document';

        // The document goes into the employee's Documents, hidden until the onboarding is
        // completed and the document approved (OnboardingService::publishDocuments); a
        // Draft while HR reviews it. Onboarding documents are visible to the employee
        // and admins only, not to managers.
        $doc = new \Documents\Common\Model\EmployeeDocument();
        if (!empty($step['link_ref'])) {
            $doc->Load('id = ? AND employee = ?', array((int) $step['link_ref'], (int) $o['employee']));
        }
        $doc->employee = (int) $o['employee'];
        $doc->document = (int) $documentType;
        $doc->attachment = $file;
        $doc->details = mb_substr($step['title'], 0, 500);
        $doc->status = !empty($step['needs_review']) ? 'Draft' : 'Active';
        $doc->visible_to = 'Owner Only';
        $doc->hidden = (empty($step['needs_review']) && $o['status'] === 'Completed') ? 0 : 1;
        if (empty($doc->id)) {
            $doc->date_added = date('Y-m-d');
        }
        if (!$doc->Save()) {
            return 'The document could not be saved.';
        }
        $response['file'] = $file;
        $response['original'] = $original;
        $response['uploaded_at'] = Db::now();
        $status = !empty($step['needs_review']) ? 'Submitted' : 'Done';
        Db::db()->Execute(
            'UPDATE OnboardingSteps SET status = ?, response = ?, link_ref = ?, review_note = NULL, completed_at = ?, completed_by = ?, updated = ? WHERE id = ?',
            array($status, json_encode($response), (int) $doc->id, $status === 'Done' ? Db::now() : null, $status === 'Done' ? (int) $user->id : null, Db::now(), (int) $step['id'])
        );
        $service = new OnboardingService();
        $service->log($o['id'], $step['id'], $user->id, $status === 'Done' ? 'step_done' : 'submitted', array('file' => $original));
        if ($status === 'Submitted') {
            OnboardingNotifier::documentSubmitted($o, $step);
        } else {
            $service->completeIfDone($o['id']);
        }
        return null;
    }

    /**
     * A task or asset step done by its owner (or by HR). Returns an error or null.
     */
    public function markDone(array $step, array $o, $user, array $input)
    {
        if ($step['type'] === 'set_manager') {
            return $this->setManager($step, $o, $user, isset($input['manager']) ? (int) $input['manager'] : 0);
        }
        $response = array();
        $note = isset($input['note']) ? mb_substr(trim((string) $input['note']), 0, 2000) : '';
        if ($note !== '') {
            $response['note'] = $note;
        }
        if ($step['type'] === 'asset') {
            if (empty($o['employee'])) {
                return 'Assets can be handed over once the new hire has accepted the invitation.';
            }
            $assetId = isset($input['asset']) ? (int) $input['asset'] : 0;
            if ($assetId <= 0) {
                return 'Choose the asset to hand over.';
            }
            $error = Integrations::assignAsset($assetId, (int) $o['employee']);
            if ($error !== null) {
                return $error;
            }
            $label = Db::rows('SELECT code, name FROM CompanyAssets WHERE id = ?', array($assetId));
            $response['asset'] = $assetId;
            $response['asset_label'] = empty($label) ? (string) $assetId : trim($label[0]['code'] . ' ' . $label[0]['name']);
        }
        $this->finish($step, $user, $response);
        $service = new OnboardingService();
        $service->log($o['id'], $step['id'], $user->id, 'step_done', $note === '' ? array() : array('note' => $note));
        $service->completeIfDone($o['id']);
        return null;
    }

    /**
     * A document HR sent back after the onboarding was completed: the employee can still
     * upload a new copy.
     */
    public function isResubmission(array $row, $o)
    {
        return $o !== null && $o['status'] === 'Completed' && $row['type'] === 'document'
            && $row['owner_type'] === 'new_hire' && $row['status'] === 'ChangesRequested';
    }

    /**
     * HR approves a submitted document: it becomes an active employee document. It shows
     * in the employee's My Documents once the onboarding is completed (straight away when
     * it already is). Also possible after the onboarding is completed.
     */
    public function approve(array $step, array $o, $user)
    {
        if (!in_array($o['status'], OnboardingService::REVIEW_STATUSES, true)) {
            return 'This onboarding is cancelled.';
        }
        if ($step['type'] === 'form') {
            return Integrations::reviewForm($step, $user, 'approve', null) ?: $this->afterFormReview($o);
        }
        if ($step['status'] !== 'Submitted') {
            return 'Only submitted steps can be approved.';
        }
        if ($step['type'] === 'document' && !empty($step['link_ref'])) {
            Db::db()->Execute(
                "UPDATE EmployeeDocuments SET status = 'Active', hidden = ?, visible_to = 'Owner Only' WHERE id = ? AND employee = ?",
                array($o['status'] === 'Completed' ? 0 : 1, (int) $step['link_ref'], (int) $o['employee'])
            );
        }
        $response = json_decode((string) $step['response'], true);
        $this->finish($step, $user, is_array($response) ? $response : array());
        $service = new OnboardingService();
        $service->log($o['id'], $step['id'], $user->id, 'approved', array());
        $service->completeIfDone($o['id']);
        return null;
    }

    public function requestChanges(array $step, array $o, $user, $note)
    {
        $note = mb_substr(trim((string) $note), 0, 2000);
        if ($note === '') {
            return 'Tell the new hire what to change.';
        }
        if (!in_array($o['status'], OnboardingService::REVIEW_STATUSES, true)) {
            return 'This onboarding is cancelled.';
        }
        if ($step['type'] === 'form') {
            return Integrations::reviewForm($step, $user, 'request-changes', $note) ?: $this->afterFormReview($o);
        }
        if ($step['status'] !== 'Submitted') {
            return 'Only submitted steps can be sent back.';
        }
        Db::db()->Execute(
            "UPDATE OnboardingSteps SET status = 'ChangesRequested', review_note = ?, updated = ? WHERE id = ?",
            array($note, Db::now(), (int) $step['id'])
        );
        (new OnboardingService())->log($o['id'], $step['id'], $user->id, 'changes_requested', array('note' => $note));
        OnboardingNotifier::changesRequested($o, $step, $note);
        return null;
    }

    public function skip(array $step, array $o, $user)
    {
        if (!in_array($o['status'], OnboardingService::OPEN_STATUSES, true)) {
            return 'This onboarding is closed.';
        }
        if (in_array($step['status'], array('Done', 'Skipped'), true)) {
            return 'This step is already closed.';
        }
        Integrations::cancelLinked($step, $user);
        Db::db()->Execute(
            "UPDATE OnboardingSteps SET status = 'Skipped', completed_at = ?, completed_by = ?, updated = ? WHERE id = ?",
            array(Db::now(), (int) $user->id, Db::now(), (int) $step['id'])
        );
        $service = new OnboardingService();
        $service->log($o['id'], $step['id'], $user->id, 'skipped', array());
        $service->completeIfDone($o['id']);
        return null;
    }

    /**
     * Skip every step of an onboarding that is in one of $statuses, withdrawing its
     * linked form as skip() does (used when HR completes or cancels the onboarding).
     * Does not complete the onboarding. Returns how many steps were skipped.
     */
    public function skipOpen($onboardingId, $user, array $statuses)
    {
        if (empty($statuses)) {
            return 0;
        }
        $in = implode(',', array_fill(0, count($statuses), '?'));
        $rows = Db::rows(
            "SELECT * FROM OnboardingSteps WHERE onboarding = ? AND status IN ($in) ORDER BY sort_order, id",
            array_merge(array((int) $onboardingId), array_values($statuses))
        );
        foreach ($rows as $step) {
            Integrations::cancelLinked($step, $user);
            Db::db()->Execute(
                "UPDATE OnboardingSteps SET status = 'Skipped', completed_at = ?, completed_by = ?, updated = ? WHERE id = ?",
                array(Db::now(), empty($user) ? null : (int) $user->id, Db::now(), (int) $step['id'])
            );
        }
        return count($rows);
    }

    /** Try again to start a form / signing / course step that failed to start. */
    public function retry(array $step, array $o)
    {
        if (!empty($step['link_ref']) || empty($o['employee'])) {
            return 'This step cannot be started again.';
        }
        (new OnboardingService())->startLinkedSteps((int) $o['id']);
        $fresh = $this->find($step['id']);
        return empty($fresh['link_ref']) ? ($this->errorOf($fresh) ?: 'The step could not be started.') : null;
    }

    /** Copy the status of linked records (Forms, E-Sign, Learn) onto their steps. */
    public function sync($onboardingId)
    {
        $rows = Db::rows(
            "SELECT * FROM OnboardingSteps WHERE onboarding = ? AND link_ref IS NOT NULL AND type IN ('form', 'esign', 'course') AND (status NOT IN ('Done', 'Skipped') OR (type = 'form' AND status = 'Done'))",
            array((int) $onboardingId)
        );
        $changed = false;
        foreach ($rows as $row) {
            $status = Integrations::linkedStatus($row);
            if ($status === null || $status === $row['status']) {
                continue;
            }
            Db::db()->Execute(
                'UPDATE OnboardingSteps SET status = ?, completed_at = ?, updated = ? WHERE id = ?',
                array($status, $status === 'Done' ? Db::now() : null, Db::now(), (int) $row['id'])
            );
            if ($status === 'Done') {
                (new OnboardingService())->log($onboardingId, $row['id'], null, 'step_done', array('source' => $row['type']));
            }
            $changed = true;
        }
        if ($changed) {
            (new OnboardingService())->completeIfDone($onboardingId);
        }
    }

    /** The form changed in Forms: copy its status onto the step. Returns null (no error). */
    private function afterFormReview(array $o)
    {
        $this->sync((int) $o['id']);
        return null;
    }

    private function finish(array $step, $user, array $response)
    {
        Db::db()->Execute(
            "UPDATE OnboardingSteps SET status = 'Done', response = ?, review_note = NULL, completed_at = ?, completed_by = ?, updated = ? WHERE id = ?",
            array(empty($response) ? null : json_encode($response), Db::now(), empty($user) ? null : (int) $user->id, Db::now(), (int) $step['id'])
        );
    }

    private function errorOf($step)
    {
        $response = json_decode((string) $step['response'], true);
        return is_array($response) && isset($response['error']) ? $response['error'] : null;
    }
}
