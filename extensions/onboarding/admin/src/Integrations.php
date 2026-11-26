<?php

namespace OnboardingAdmin;

use Classes\IceResponse;
use Utils\LogManager;

/**
 * The step types that hand work to another extension: form (Forms), esign (E-Sign),
 * course (Learn) and asset (Company Assets). Each is offered only while that
 * extension is installed and enabled. Linked steps store the other extension's record
 * id in OnboardingSteps.link_ref and are kept up to date by sync() — no hooks are
 * needed in the other extensions.
 */
class Integrations
{
    const EXTENSIONS = array(
        'form' => array('hrforms', 'HrFormsAdmin\\AssignmentService'),
        'esign' => array('esign', 'EsignAdmin\\EsignRequestService'),
        'course' => array('learn', 'LearnAdmin\\CourseService'),
        'asset' => array('company_assets', 'Company_assetsAdmin\\Common\\Model\\CompanyAsset'),
    );

    private static $enabled = null;

    /** Is the extension behind this step type installed and enabled? (Core types always are.) */
    public static function available($type)
    {
        if (!isset(self::EXTENSIONS[$type])) {
            return true;
        }
        list($module, $class) = self::EXTENSIONS[$type];
        if (!class_exists($class)) {
            return false;
        }
        if ($type === 'course' && !class_exists('EditorUser\\EditorService')) {
            return false;
        }
        if (self::$enabled === null) {
            self::$enabled = array();
            foreach (Db::rows("SELECT name FROM Modules WHERE mod_group = 'admin' AND status = 'Enabled'") as $row) {
                self::$enabled[$row['name']] = true;
            }
        }
        return isset(self::$enabled[$module]);
    }

    /** What the workflow builder can pick for each step type. */
    public static function options()
    {
        $out = array(
            'available' => array(),
            'forms' => array(),
            'esign_documents' => array(),
            'courses' => array(),
            'asset_types' => array(),
            'document_types' => array(),
        );
        foreach (array_keys(self::EXTENSIONS) as $type) {
            $out['available'][$type] = self::available($type);
        }
        foreach (Db::rows('SELECT id, name FROM Documents ORDER BY name') as $row) {
            $out['document_types'][] = array('value' => (int) $row['id'], 'label' => $row['name']);
        }
        if ($out['available']['form']) {
            foreach ((new \HrFormsAdmin\TemplateService())->sendableOptions() as $option) {
                $out['forms'][] = array(
                    'value' => $option['value'],
                    'label' => $option['name'],
                    'group' => $option['group'],
                    'country' => isset($option['country']) ? $option['country'] : null,
                );
            }
        }
        if ($out['available']['esign']) {
            foreach (\EsignAdmin\EsignRequestService::signableTemplates() as $doc) {
                $out['esign_documents'][] = array('value' => $doc['id'], 'label' => $doc['title']);
            }
        }
        if ($out['available']['course']) {
            foreach (Db::rows("SELECT id, name FROM LmsCourse WHERE status = 'Published' ORDER BY name") as $row) {
                $out['courses'][] = array('value' => (int) $row['id'], 'label' => $row['name']);
            }
        }
        if ($out['available']['asset']) {
            foreach (Db::rows('SELECT id, name FROM AssetTypes ORDER BY name') as $row) {
                $out['asset_types'][] = array('value' => (int) $row['id'], 'label' => $row['name']);
            }
        }
        return $out;
    }

    /** Label of a configured choice, for showing a step (e.g. the form or course name). */
    public static function describe($type, array $config)
    {
        try {
            if ($type === 'form' && !empty($config['form']) && self::available('form')) {
                foreach ((new \HrFormsAdmin\TemplateService())->sendableOptions() as $option) {
                    if ($option['value'] === $config['form']) {
                        return $option['name'];
                    }
                }
            }
            if ($type === 'document') {
                $id = self::documentTypeId($config);
                if ($id) {
                    $rows = Db::rows('SELECT name FROM Documents WHERE id = ?', array($id));
                    return empty($rows) ? null : $rows[0]['name'];
                }
            }
            if ($type === 'esign' && !empty($config['document'])) {
                $rows = Db::rows('SELECT title FROM EsignDocuments WHERE id = ?', array((int) $config['document']));
                return empty($rows) ? null : $rows[0]['title'];
            }
            if ($type === 'course' && !empty($config['course'])) {
                $rows = Db::rows('SELECT name FROM LmsCourse WHERE id = ?', array((int) $config['course']));
                return empty($rows) ? null : $rows[0]['name'];
            }
            if ($type === 'asset') {
                $id = self::assetTypeId($config);
                if ($id) {
                    $rows = Db::rows('SELECT name FROM AssetTypes WHERE id = ?', array($id));
                    return empty($rows) ? null : $rows[0]['name'];
                }
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }

    /**
     * Library workflows name document and asset types (ids differ per company);
     * workflows built here store the id.
     */
    public static function documentTypeId(array $config)
    {
        if (!empty($config['document_type'])) {
            return (int) $config['document_type'];
        }
        if (!empty($config['document_name'])) {
            $rows = Db::rows('SELECT id FROM Documents WHERE name = ? LIMIT 1', array((string) $config['document_name']));
            return empty($rows) ? null : (int) $rows[0]['id'];
        }
        return null;
    }

    public static function assetTypeId(array $config)
    {
        if (!empty($config['asset_type'])) {
            return (int) $config['asset_type'];
        }
        if (!empty($config['asset_type_name'])) {
            $rows = Db::rows('SELECT id FROM AssetTypes WHERE name = ? LIMIT 1', array((string) $config['asset_type_name']));
            return empty($rows) ? null : (int) $rows[0]['id'];
        }
        return null;
    }

    /**
     * Start a linked step for the new hire (send the form, the signing request, enrol
     * in the course). Returns [link_ref|null, error|null].
     *
     * @param array  $step       OnboardingSteps row
     * @param int    $employeeId the new hire
     * @param object $hrUser     the user acting for HR (who invited the hire)
     */
    public static function start(array $step, $employeeId, $hrUser)
    {
        $config = self::config($step);
        try {
            if ($step['type'] === 'form') {
                if (!self::available('form') || empty($config['form'])) {
                    return array(null, 'The Forms extension is not available or no form was chosen.');
                }
                $result = (new \HrFormsAdmin\AssignmentService())->assign($hrUser, array(
                    'template' => $config['form'],
                    'employees' => array((int) $employeeId),
                    'due_date' => empty($step['due_date']) ? '' : $step['due_date'],
                    'message' => empty($step['instructions']) ? '' : $step['instructions'],
                    'needs_review' => !isset($config['needs_review']) || !empty($config['needs_review']),
                    'source' => 'onboarding',
                    'source_ref' => 'onboarding-step:' . $step['id'],
                    // The new hire's checklist lists the forms: no email per form.
                    'notify' => false,
                ));
                $ids = isset($result['ids']) ? $result['ids'] : array();
                if (!empty($ids[(int) $employeeId])) {
                    return array((int) $ids[(int) $employeeId], null);
                }
                return array(null, empty($result['errors']) ? 'The form could not be sent.' : implode(' ', $result['errors']));
            }
            if ($step['type'] === 'esign') {
                if (!self::available('esign') || empty($config['document'])) {
                    return array(null, 'E-Sign is not available or no document was chosen.');
                }
                $resp = \EsignAdmin\EsignRequestService::requestForEmployee(
                    (int) $config['document'],
                    (int) $employeeId,
                    empty($hrUser) ? null : (int) $hrUser->id
                );
                if ($resp->getStatus() !== IceResponse::SUCCESS) {
                    return array(null, (string) $resp->getData());
                }
                return array((int) $resp->getData()->id, null);
            }
            if ($step['type'] === 'course') {
                if (!self::available('course') || empty($config['course'])) {
                    return array(null, 'Learn is not available or no course was chosen.');
                }
                $courseId = (int) $config['course'];
                $existing = \LearnAdmin\CourseService::getEmployeeCourseByCourseId($courseId, (int) $employeeId);
                if ($existing === null) {
                    $resp = \LearnAdmin\CourseService::enrollToCourse($courseId, (int) $employeeId);
                    if ($resp->getStatus() !== IceResponse::SUCCESS) {
                        return array(null, (string) $resp->getData());
                    }
                    $existing = \LearnAdmin\CourseService::getEmployeeCourseByCourseId($courseId, (int) $employeeId);
                }
                return array($existing === null ? null : (int) $existing->id, $existing === null ? 'Enrolment failed.' : null);
            }
        } catch (\Throwable $e) {
            LogManager::getInstance()->error('Onboarding: could not start step ' . $step['id'] . ': ' . $e->getMessage());
            return array(null, 'Something went wrong while starting this step.');
        }
        return array(null, null);
    }

    /**
     * The step status implied by the linked record, or null to leave it as it is.
     */
    public static function linkedStatus(array $step)
    {
        if (empty($step['link_ref'])) {
            return null;
        }
        $ref = (int) $step['link_ref'];
        try {
            if ($step['type'] === 'form' && self::available('form')) {
                $rows = Db::rows('SELECT status FROM HrFormAssignments WHERE id = ?', array($ref));
                if (empty($rows)) {
                    return null;
                }
                $map = array(
                    'Assigned' => 'Pending',
                    'InProgress' => 'Pending',
                    'Submitted' => 'Submitted',
                    'ChangesRequested' => 'ChangesRequested',
                    'Approved' => 'Done',
                    'Cancelled' => 'Skipped',
                );
                return isset($map[$rows[0]['status']]) ? $map[$rows[0]['status']] : null;
            }
            if ($step['type'] === 'esign' && self::available('esign')) {
                $status = \EsignAdmin\EsignRequestService::status($ref);
                if ($status === 'Signed') {
                    return 'Done';
                }
                if ($status === 'Rejected' || $status === 'Deleted') {
                    return 'ChangesRequested';
                }
                return $status === null ? null : 'Pending';
            }
            if ($step['type'] === 'course' && self::available('course')) {
                $rows = Db::rows(
                    "SELECT COUNT(*) AS total, SUM(status = 'Completed') AS done FROM LmsEmployeeLesson WHERE employee_course = ?",
                    array($ref)
                );
                if (empty($rows) || (int) $rows[0]['total'] === 0) {
                    return null;
                }
                return (int) $rows[0]['done'] >= (int) $rows[0]['total'] ? 'Done' : 'Pending';
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }

    /**
     * Approve or send back the linked form in Forms (so the answers reach the profile,
     * or the new hire can edit the form again). Returns an error or null.
     */
    public static function reviewForm(array $step, $user, $action, $note)
    {
        if (empty($step['link_ref']) || !self::available('form')) {
            return 'This form is not available.';
        }
        $service = new \HrFormsAdmin\AssignmentService();
        $row = $service->find((int) $step['link_ref']);
        if ($row === null) {
            return 'This form is not available.';
        }
        if ($row['status'] !== 'Submitted') {
            return 'Only submitted forms can be reviewed.';
        }
        $result = $action === 'approve'
            ? $service->approve($row, $user, false)
            : $service->requestChanges($row, $user, (string) $note);
        return empty($result[0]) ? (string) $result[1] : null;
    }

    /** Withdraw the linked form when its step is skipped, so the new hire no longer sees it. */
    public static function cancelLinked(array $step, $user)
    {
        if ($step['type'] !== 'form' || empty($step['link_ref']) || !self::available('form')) {
            return;
        }
        try {
            $service = new \HrFormsAdmin\AssignmentService();
            $row = $service->find((int) $step['link_ref']);
            if ($row !== null) {
                $service->cancel($row, $user);
            }
        } catch (\Throwable $e) {
            LogManager::getInstance()->error('Onboarding: could not cancel the form of step ' . $step['id'] . ': ' . $e->getMessage());
        }
    }

    /** Where the new hire completes a linked step. */
    /**
     * May HR fill in this form for the employee? Forms decides: not when it has a
     * signature or secure (encrypted) answers, and only while it is still open.
     */
    public static function formHrCanFill($assignmentId)
    {
        if (empty($assignmentId) || !self::available('form')) {
            return false;
        }
        $row = (new \HrFormsAdmin\AssignmentService())->find((int) $assignmentId);
        if ($row === null || !in_array($row['status'], array('Assigned', 'InProgress', 'ChangesRequested', 'Submitted'), true)) {
            return false;
        }
        $schema = (new \HrFormsAdmin\TemplateService())->versionSchema($row['template_version']);
        return $schema !== null && \HrFormsAdmin\FormSchema::hrCanFill($schema);
    }

    public static function linkUrl(array $step)
    {
        $urls = array(
            'form' => 'g=extension&n=hrforms|user',
            'esign' => 'g=extension&n=esign|user',
            'course' => 'g=extension&n=learn|user',
        );
        return isset($urls[$step['type']]) ? $urls[$step['type']] : null;
    }

    /**
     * Hand an asset to the new hire (asset steps, done by HR). Returns an error or null.
     */
    public static function assignAsset($assetId, $employeeId)
    {
        if (!self::available('asset')) {
            return 'Company Assets is not available.';
        }
        $asset = new \Company_assetsAdmin\Common\Model\CompanyAsset();
        $asset->Load('id = ?', array((int) $assetId));
        if (empty($asset->id)) {
            return 'Asset not found.';
        }
        if (!empty($asset->employee) && (int) $asset->employee !== (int) $employeeId) {
            return 'This asset is already assigned to someone else.';
        }
        $asset->employee = (int) $employeeId;
        $asset->status = 'Assigned';
        $asset->updated = Db::now();
        if (!$asset->Save()) {
            return 'The asset could not be assigned.';
        }
        return null;
    }

    /** Assets of a type that are free to hand out. */
    public static function availableAssets($assetTypeId)
    {
        if (!self::available('asset')) {
            return array();
        }
        $params = array();
        $sql = "SELECT id, code, name, serial_number FROM CompanyAssets WHERE status = 'Available' AND (employee IS NULL OR employee = 0)";
        if (!empty($assetTypeId)) {
            $sql .= ' AND type = ?';
            $params[] = (int) $assetTypeId;
        }
        $out = array();
        foreach (Db::rows($sql . ' ORDER BY code LIMIT 200', $params) as $row) {
            $label = trim($row['code'] . ' ' . ($row['name'] ? '· ' . $row['name'] : '') . ($row['serial_number'] ? ' (' . $row['serial_number'] . ')' : ''));
            $out[] = array('value' => (int) $row['id'], 'label' => $label);
        }
        return $out;
    }

    public static function config(array $step)
    {
        if (is_array($step['config'])) {
            return $step['config'];
        }
        $config = json_decode((string) $step['config'], true);
        return is_array($config) ? $config : array();
    }
}
