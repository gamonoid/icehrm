<?php

namespace OnboardingAdmin;

use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Utils\LogManager;

/**
 * The Onboarding REST surface. Every handler identifies the caller first and decides
 * access from OnboardingAccess — nothing the client sends grants access.
 *
 * HR (OnboardingAccess::isHr)
 *   GET    onboarding/library                     standard workflows (shipped in code)
 *   GET    onboarding/library/(:any)              one, with its steps (preview)
 *   POST   onboarding/library/(:any)/clone        copy into an editable custom workflow
 *   GET    onboarding/workflows[&status=]         custom workflows
 *   POST   onboarding/workflows                   create
 *   GET    onboarding/workflows/(:num)            one
 *   POST   onboarding/workflows/(:num)            save
 *   DELETE onboarding/workflows/(:num)            only if never used
 *   DELETE onboarding/hires/(:num)                only cancelled ones
 *   POST   onboarding/workflows/(:num)/archive    | /restore | /duplicate
 *   GET    onboarding/workflow-choices            workflows that can be used to invite
 *   GET    onboarding/lookups                     departments, job titles, people, ...
 *   GET    onboarding/assets[&type=]              assets free to hand out
 *   POST   onboarding/hires                       invite a new hire (or one employee: track=workforce)
 *   POST   onboarding/workforce/invite            existing staff in bulk, 25 per call
 *   POST   onboarding/hires/(:num)/resend         | /cancel | /complete
 *   POST   onboarding/steps/(:num)/approve        | /request-changes | /skip | /retry
 *   GET    onboarding/steps/(:num)/manager-options  people who can be set as manager
 *
 * HR and managers (for the people they manage)
 *   GET    onboarding/context
 *   GET    onboarding/hires[&status=&search=]
 *   GET    onboarding/hires/(:num)
 *   GET    onboarding/employees/(:num)/onboarding profile section
 *
 * Step owners (HR, managers, buddies, named people) and HR
 *   GET    onboarding/me/tasks[&done=1]
 *   POST   onboarding/me/tasks/(:num)/done
 *
 * The new hire (own onboarding only)
 *   GET    onboarding/me
 *   POST   onboarding/me/steps/(:num)           complete an info / acknowledge / document step
 *   POST   onboarding/me/upload                 document upload
 */
/*
 * Only GET, POST and DELETE are used: the shared IceApiClient has no PUT. Query
 * parameters arrive in $_GET because the client appends them to the legacy
 * api/index.php URL, e.g. 'onboarding/hires&status=open'.
 */
class ApiController extends IceApiController
{
    public function registerEndPoints()
    {
        // --- context -----------------------------------------------------------------
        $this->route(self::GET, 'onboarding/context', function ($rest, $user) {
            $isHr = OnboardingAccess::isHr($user);
            $this->ok($rest, array(
                'is_hr' => $isHr,
                'can_open' => OnboardingAccess::canOpenAdminModule($user),
                'employee_id' => OnboardingAccess::currentEmployeeId(),
                'phases' => WorkflowService::PHASES,
                'types' => WorkflowService::TYPES,
                'options' => $isHr ? Integrations::options() : null,
                'counts' => (new OnboardingService())->counts($user, $this->track()),
                'track' => $this->track(),
            ));
        });

        // --- workflows (HR) ----------------------------------------------------------
        $this->route(self::GET, 'onboarding/library', function ($rest, $user) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $this->ok($rest, (new WorkflowService())->library($this->track()));
        });

        $this->route(self::GET, 'onboarding/library/(:any)', function ($rest, $user, $key) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $service = new WorkflowService();
            $item = $service->libraryItem($key);
            if ($item === null) {
                $this->notFound($rest);
                return;
            }
            list($workflow) = $service->validate($item);
            $workflow['key'] = $key;
            $workflow['audience'] = isset($item['audience']) ? $item['audience'] : null;
            $workflow['steps'] = $this->describeSteps($workflow['steps']);
            $this->ok($rest, $workflow);
        });

        $this->route(self::POST, 'onboarding/library/(:any)/clone', function ($rest, $user, $key) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            list($id, $errors) = (new WorkflowService())->cloneLibrary($key, $user);
            $this->result($rest, array('id' => $id), $errors);
        });

        $this->route(self::GET, 'onboarding/workflows', function ($rest, $user) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $status = isset($_GET['status']) ? (string) $_GET['status'] : 'Active';
            $this->ok($rest, (new WorkflowService())->listCustom($status, $this->track()));
        });

        $this->route(self::POST, 'onboarding/workflows', function ($rest, $user) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            list($id, $errors) = (new WorkflowService())->save(null, $this->body($rest), $user);
            $this->result($rest, array('id' => $id), $errors);
        });

        $this->route(self::GET, 'onboarding/workflows/(:num)', function ($rest, $user, $id) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $workflow = (new WorkflowService())->load($id);
            if ($workflow === null) {
                $this->notFound($rest);
                return;
            }
            $this->ok($rest, $workflow);
        });

        $this->route(self::POST, 'onboarding/workflows/(:num)', function ($rest, $user, $id) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            list($saved, $errors) = (new WorkflowService())->save($id, $this->body($rest), $user);
            $this->result($rest, array('id' => $saved), $errors);
        });

        $this->route(self::DELETE, 'onboarding/workflows/(:num)', function ($rest, $user, $id) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $error = (new WorkflowService())->delete($id);
            $this->result($rest, array('id' => (int) $id), $error === null ? array() : array($error));
        });

        $this->route(self::POST, 'onboarding/workflows/(:num)/(archive|restore|duplicate)', function ($rest, $user, $id, $action) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $service = new WorkflowService();
            if ($action === 'duplicate') {
                list($newId, $errors) = $service->duplicate($id, $user);
                $this->result($rest, array('id' => $newId), $errors);
                return;
            }
            if (!$service->setArchived($id, $action === 'archive')) {
                $this->notFound($rest);
                return;
            }
            $this->ok($rest, array('id' => (int) $id));
        });

        $this->route(self::GET, 'onboarding/workflow-choices', function ($rest, $user) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $this->ok($rest, (new WorkflowService())->choices($this->track()));
        });

        $this->route(self::GET, 'onboarding/lookups', function ($rest, $user) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $map = function ($rows, $label = 'name') {
                $out = array();
                foreach ($rows as $row) {
                    $out[] = array('value' => (int) $row['id'], 'label' => $row[$label]);
                }
                return $out;
            };
            $people = array();
            foreach (Db::rows("SELECT id, first_name, last_name, employee_id FROM Employees WHERE status = 'Active' ORDER BY first_name, last_name LIMIT 2000") as $row) {
                $people[] = array('value' => (int) $row['id'], 'label' => trim($row['first_name'] . ' ' . $row['last_name']) . ($row['employee_id'] ? ' (' . $row['employee_id'] . ')' : ''));
            }
            $countries = array();
            foreach (Db::rows('SELECT code, name FROM Country ORDER BY name') as $row) {
                $countries[] = array('value' => $row['code'], 'label' => $row['name']);
            }
            $this->ok($rest, array(
                'departments' => $map(Db::rows('SELECT id, title FROM CompanyStructures ORDER BY title'), 'title'),
                'job_titles' => $map(Db::rows('SELECT id, name FROM JobTitles ORDER BY name')),
                'employment_statuses' => $map(Db::rows('SELECT id, name FROM EmploymentStatus ORDER BY name')),
                'people' => $people,
                'countries' => $countries,
            ));
        });

        $this->route(self::GET, 'onboarding/assets', function ($rest, $user) {
            // HR, or whoever owns an open "hand over an asset" step.
            $allowed = OnboardingAccess::isHr($user);
            $me = OnboardingAccess::currentEmployeeId();
            if (!$allowed && $me !== null) {
                $allowed = !empty(Db::rows(
                    "SELECT id FROM OnboardingSteps WHERE type = 'asset' AND owner_employee = ? AND status IN ('Pending', 'ChangesRequested') LIMIT 1",
                    array($me)
                ));
            }
            if (OnboardingAccess::deny($rest, $allowed)) {
                return;
            }
            $type = isset($_GET['type']) ? (int) $_GET['type'] : 0;
            $this->ok($rest, Integrations::availableAssets($type));
        });

        // --- new hires ----------------------------------------------------------------
        $this->route(self::GET, 'onboarding/hires', function ($rest, $user) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::canOpenAdminModule($user))) {
                return;
            }
            $this->ok($rest, (new OnboardingService())->listForAdmin($user, array(
                'status' => isset($_GET['status']) ? (string) $_GET['status'] : 'open',
                'search' => isset($_GET['search']) ? (string) $_GET['search'] : '',
                'track' => $this->track(),
            )));
        });

        $this->route(self::POST, 'onboarding/hires', function ($rest, $user) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            list($id, $errors, $url, $sent) = (new OnboardingService())->invite($this->body($rest), $user);
            if (!empty($errors)) {
                $this->ok($rest, array('ok' => false, 'errors' => $errors));
                return;
            }
            $this->ok($rest, array('ok' => true, 'id' => $id, 'url' => $url, 'sent' => $sent));
        });

        // Existing staff in bulk (Company Onboarding): up to 25 people per call, each
        // invited like a single invite; the result says per row what happened.
        $this->route(self::POST, 'onboarding/workforce/invite', function ($rest, $user) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $body = $this->body($rest);
            $rows = isset($body['rows']) && is_array($body['rows']) ? $body['rows'] : array();
            if (count($rows) > 25) {
                $this->fail($rest, 'Send at most 25 people at a time.');
                return;
            }
            $service = new OnboardingService();
            $results = array();
            foreach (array_values($rows) as $i => $row) {
                if (!is_array($row)) {
                    continue;
                }
                $input = array_merge($row, array(
                    'track' => 'workforce',
                    'workflow' => isset($body['workflow']) ? $body['workflow'] : null,
                    'welcome_message' => isset($body['welcome_message']) ? $body['welcome_message'] : null,
                    'user_level' => isset($row['user_level']) ? $row['user_level'] : 'Employee',
                ));
                list($id, $errors, $url, $sent) = $service->invite($input, $user);
                $results[] = array(
                    'index' => isset($row['index']) ? (int) $row['index'] : $i,
                    'ok' => empty($errors),
                    'id' => $id,
                    'errors' => $errors,
                    'sent' => (bool) $sent,
                    // The link is only needed when the email could not be sent.
                    'url' => empty($errors) && !$sent ? $url : null,
                );
            }
            $this->ok($rest, array('results' => $results));
        });

        $this->route(self::GET, 'onboarding/hires/(:num)', function ($rest, $user, $id) {
            $service = new OnboardingService();
            $o = $service->find($id);
            if ($o === null) {
                $this->notFound($rest);
                return;
            }
            if (OnboardingAccess::deny($rest, OnboardingAccess::canView($user, $o))) {
                return;
            }
            $this->ok($rest, $service->detail($o, $this->viewer($user, false)));
        });

        $this->route(self::POST, 'onboarding/hires/(:num)/(resend|cancel|complete)', function ($rest, $user, $id, $action) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $service = new OnboardingService();
            if ($service->find($id) === null) {
                $this->notFound($rest);
                return;
            }
            if ($action === 'resend') {
                list($data, $error) = $service->resend($id, $user);
                $this->result($rest, $data, $error === null ? array() : array($error));
                return;
            }
            $error = $action === 'cancel' ? $service->cancel($id, $user) : $service->complete($id, $user);
            $this->result($rest, array('id' => (int) $id), $error === null ? array() : array($error));
        });

        $this->route(self::DELETE, 'onboarding/hires/(:num)', function ($rest, $user, $id) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $error = (new OnboardingService())->delete($id);
            $this->result($rest, array('id' => (int) $id), $error === null ? array() : array($error));
        });

        $this->route(self::GET, 'onboarding/steps/(:num)/manager-options', function ($rest, $user, $id) {
            if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user))) {
                return;
            }
            $steps = new StepService();
            $step = $steps->find($id);
            $o = $step === null ? null : (new OnboardingService())->find($step['onboarding']);
            if ($o === null || $step['type'] !== 'set_manager') {
                $this->notFound($rest);
                return;
            }
            $this->ok($rest, $steps->managerOptions($o));
        });

        $this->route(self::POST, 'onboarding/steps/(:num)/(approve|request-changes|skip|retry|done)', function ($rest, $user, $id, $action) {
            $steps = new StepService();
            $step = $steps->find($id);
            if ($step === null) {
                $this->notFound($rest);
                return;
            }
            $o = (new OnboardingService())->find($step['onboarding']);
            $isHr = OnboardingAccess::isHr($user);
            if ($action === 'done') {
                // Tasks and asset hand-overs; the new hire's own steps are done by them.
                $allowed = $step['owner_type'] !== 'new_hire' && $steps->canComplete($step, $o, $this->viewer($user, false));
                if (OnboardingAccess::deny($rest, $allowed)) {
                    return;
                }
                $error = $steps->markDone($step, $o, $user, $this->body($rest));
            } else {
                if (OnboardingAccess::deny($rest, $isHr)) {
                    return;
                }
                $body = $this->body($rest);
                if ($action === 'approve') {
                    $error = $steps->approve($step, $o, $user);
                } elseif ($action === 'request-changes') {
                    $error = $steps->requestChanges($step, $o, $user, isset($body['note']) ? $body['note'] : '');
                } elseif ($action === 'skip') {
                    $error = $steps->skip($step, $o, $user);
                } else {
                    $error = $steps->retry($step, $o);
                }
            }
            $this->result($rest, array('id' => (int) $id), $error === null ? array() : array($error));
        });

        $this->route(self::GET, 'onboarding/employees/(:num)/onboarding', function ($rest, $user, $employeeId) {
            $service = new OnboardingService();
            // Cancelled onboardings too, so HR can reach (and delete) them from the profile.
            $o = $service->findForEmployee($employeeId, true);
            if ($o === null) {
                if (OnboardingAccess::deny($rest, OnboardingAccess::isHr($user)
                    || \Classes\BaseService::getInstance()->currentUserCanAccessEmployeeData($employeeId))) {
                    return;
                }
                $this->ok($rest, null);
                return;
            }
            if (OnboardingAccess::deny($rest, OnboardingAccess::canView($user, $o))) {
                return;
            }
            $this->ok($rest, $service->detail($o, $this->viewer($user, false)));
        });

        // --- step owners --------------------------------------------------------------
        $this->route(self::GET, 'onboarding/me/tasks', function ($rest, $user) {
            $isHr = OnboardingAccess::isHr($user);
            $done = !empty($_GET['done']);
            $this->ok($rest, (new StepService())->myTasks($user, OnboardingAccess::currentEmployeeId(), $isHr, $done));
        });

        $this->route(self::POST, 'onboarding/me/tasks/(:num)/done', function ($rest, $user, $id) {
            $steps = new StepService();
            $step = $steps->find($id);
            if ($step === null || $step['owner_type'] === 'new_hire') {
                $this->notFound($rest);
                return;
            }
            $o = (new OnboardingService())->find($step['onboarding']);
            if (OnboardingAccess::deny($rest, $steps->canComplete($step, $o, $this->viewer($user, false)))) {
                return;
            }
            $error = $steps->markDone($step, $o, $user, $this->body($rest));
            $this->result($rest, array('id' => (int) $id), $error === null ? array() : array($error));
        });

        // --- the new hire -------------------------------------------------------------
        $this->route(self::GET, 'onboarding/me', function ($rest, $user) {
            $employeeId = OnboardingAccess::currentEmployeeId();
            $service = new OnboardingService();
            $o = $employeeId === null ? null : $service->findForEmployee($employeeId);
            if ($o === null) {
                $this->ok($rest, null);
                return;
            }
            (new StepService())->sync((int) $o['id']);
            $o = $service->find($o['id']);
            $data = $service->present($o);
            $data['welcome_message'] = $o['welcome_message'];
            $data['welcome_video_url'] = $o['welcome_video_url'];
            $data['first_day_info'] = $o['first_day_info'];
            $all = (new StepService())->listForOnboarding((int) $o['id'], $this->viewer($user, true));
            // The new hire sees their own steps, and the others only as "what happens next".
            $data['steps'] = array_values(array_filter($all, function ($s) {
                return $s['owner_type'] === 'new_hire';
            }));
            $data['team_steps'] = array_values(array_map(function ($s) {
                return array('id' => $s['id'], 'title' => $s['title'], 'phase' => $s['phase'], 'status' => $s['status'], 'owner_type' => $s['owner_type'], 'due_date' => $s['due_date']);
            }, array_filter($all, function ($s) {
                return $s['owner_type'] !== 'new_hire';
            })));
            $this->ok($rest, $data);
        });

        $this->route(self::POST, 'onboarding/me/steps/(:num)', function ($rest, $user, $id) {
            $employeeId = OnboardingAccess::currentEmployeeId();
            $steps = new StepService();
            $step = $steps->find($id);
            $o = $step === null ? null : (new OnboardingService())->find($step['onboarding']);
            if ($step === null || $o === null || empty($o['employee']) || (int) $o['employee'] !== (int) $employeeId || $step['owner_type'] !== 'new_hire') {
                $this->notFound($rest);
                return;
            }
            if (OnboardingAccess::deny($rest, $steps->canComplete($step, $o, $this->viewer($user, true)))) {
                return;
            }
            $error = $steps->submitByNewHire($step, $o, $user, $this->body($rest));
            $this->result($rest, array('id' => (int) $id), $error === null ? array() : array($error));
        });

        $this->route(self::POST, 'onboarding/me/upload', function ($rest, $user) {
            $employeeId = OnboardingAccess::currentEmployeeId();
            $o = $employeeId === null ? null : (new OnboardingService())->findForEmployee($employeeId);
            // Also after completion, to replace a document HR sent back.
            if (OnboardingAccess::deny($rest, $o !== null && in_array($o['status'], array('Invited', 'InProgress', 'Completed'), true))) {
                return;
            }
            UploadGuard::handleUpload($rest, $employeeId);
        });
    }

    // --- helpers ---------------------------------------------------------------------

    private function viewer($user, $asNewHire)
    {
        return array(
            'user' => $user,
            'is_hr' => !$asNewHire && OnboardingAccess::isHr($user),
            'employee' => OnboardingAccess::currentEmployeeId(),
            'is_new_hire' => $asNewHire,
        );
    }

    private function describeSteps(array $steps)
    {
        foreach ($steps as &$step) {
            $step['subject'] = Integrations::describe($step['type'], $step['config']);
            $step['available'] = Integrations::available($step['type']);
        }
        return $steps;
    }

    /**
     * Register a route whose handler gets ($rest, $user, ...pathParams) after the caller
     * is authenticated. Unexpected errors become a 500 with a generic message.
     */
    private function route($method, $path, callable $handler)
    {
        self::register(REST_API_PATH . $path, $method, function () use ($handler, $path) {
            $rest = new RestEndPoint();
            $user = OnboardingAccess::requireUser($rest);
            if (!$user) {
                return;
            }
            $args = array_merge(array($rest, $user), func_get_args());
            try {
                call_user_func_array($handler, $args);
            } catch (\Throwable $e) {
                LogManager::getInstance()->error('Onboarding API ' . $path . ': ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
                $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Something went wrong. Please try again.', 500));
            }
        });
    }

    /** The track a list is for (&track=new_hire|workforce), new hires by default. */
    private function track()
    {
        return WorkflowService::track(isset($_GET['track']) ? (string) $_GET['track'] : null);
    }

    private function body(RestEndPoint $rest)
    {
        $body = $rest->getRequestBody();
        return is_array($body) ? $body : array();
    }

    private function ok(RestEndPoint $rest, $data)
    {
        $rest->sendResponse(new IceResponse(IceResponse::SUCCESS, $data));
    }

    private function fail(RestEndPoint $rest, $message)
    {
        $rest->sendResponse(new IceResponse(IceResponse::ERROR, $message ?: 'The request could not be completed.', 400));
    }

    private function notFound(RestEndPoint $rest)
    {
        $rest->sendResponse(new IceResponse(IceResponse::ERROR, 'Not found', 404));
    }

    private function result(RestEndPoint $rest, $data, array $errors)
    {
        if (!empty($errors)) {
            $this->fail($rest, implode("\n", $errors));
            return;
        }
        $this->ok($rest, $data);
    }
}
