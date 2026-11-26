<?php

namespace OnboardingAdmin;

use OnboardingAdmin\Common\Model\OnboardingTemplate;

/**
 * Onboarding workflows.
 *
 *  - Standard workflows ship as JSON in admin/library/*.json. They are read-only: HR
 *    uses them as they are, or clones one to change it.
 *  - Custom workflows (clones, or built from scratch) live in OnboardingTemplates with
 *    status Active or Archived.
 *
 * A workflow is { name, description, welcome_message, welcome_video_url,
 * first_day_info, steps: [ step ] } where a step is
 * { key, phase, type, title, instructions, owner_type, owner_employee,
 *   due_offset_days, required, needs_review, config }.
 */
class WorkflowService
{
    const LIBRARY_DIR = __DIR__ . '/../library/';

    const PHASES = array('before', 'day1', 'week1', 'month1');
    // No 'form' step: IceHrm open source has no Forms extension.
    const TYPES = array('info', 'acknowledge', 'document', 'task', 'esign', 'course', 'asset', 'set_manager');
    /** new_hire: New Hire Onboarding; workforce: existing staff, from Company Onboarding. */
    const TRACKS = array('new_hire', 'workforce');
    const OWNERS = array('new_hire', 'hr', 'manager', 'buddy', 'employee');
    /** Types the new hire completes; the others are done by HR / manager / buddy / a named person. */
    const NEW_HIRE_TYPES = array('info', 'acknowledge', 'document', 'esign', 'course');
    const MAX_STEPS = 80;

    private static $library = null;

    // --- standard workflows -------------------------------------------------------------

    /** @return array key => workflow (with key) */
    public function libraryItems()
    {
        if (self::$library !== null) {
            return self::$library;
        }
        $items = array();
        foreach (glob(self::LIBRARY_DIR . '*.json') ?: array() as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            if (!is_array($data) || empty($data['key']) || empty($data['steps'])) {
                continue;
            }
            $data['track'] = self::track(isset($data['track']) ? $data['track'] : null);
            $items[$data['key']] = $data;
        }
        uasort($items, function ($a, $b) {
            $oa = isset($a['order']) ? (int) $a['order'] : 99;
            $ob = isset($b['order']) ? (int) $b['order'] : 99;
            return $oa === $ob ? strcmp($a['name'], $b['name']) : $oa - $ob;
        });
        self::$library = $items;
        return $items;
    }

    /** A valid track, defaulting to new_hire. */
    public static function track($value)
    {
        return in_array($value, self::TRACKS, true) ? $value : 'new_hire';
    }

    public function library($track = null)
    {
        $out = array();
        foreach ($this->libraryItems() as $key => $item) {
            if ($track !== null && $item['track'] !== $track) {
                continue;
            }
            $out[] = $this->summary($item, array('value' => 'system:' . $key, 'group' => 'standard', 'key' => $key));
        }
        return $out;
    }

    public function libraryItem($key)
    {
        $items = $this->libraryItems();
        return isset($items[$key]) ? $items[$key] : null;
    }

    // --- custom workflows ---------------------------------------------------------------

    public function listCustom($status = 'Active', $track = null)
    {
        $status = $status === 'Archived' ? 'Archived' : 'Active';
        $out = array();
        $sql = "SELECT t.*, (SELECT COUNT(*) FROM Onboardings o WHERE o.template = t.id) AS used_count
             FROM OnboardingTemplates t WHERE t.status = ? AND t.kind = 'onboarding'";
        $params = array($status);
        if ($track !== null) {
            $sql .= ' AND t.track = ?';
            $params[] = self::track($track);
        }
        $rows = Db::rows($sql . ' ORDER BY t.name', $params);
        foreach ($rows as $row) {
            $workflow = $this->fromRow($row);
            $out[] = $this->summary($workflow, array(
                'id' => (int) $row['id'],
                'value' => 'custom:' . (int) $row['id'],
                'group' => 'custom',
                'status' => $row['status'],
                'used_count' => (int) $row['used_count'],
                'library_key' => $row['library_key'],
                'updated' => $row['updated'],
            ));
        }
        return $out;
    }

    public function load($id)
    {
        $rows = Db::rows("SELECT * FROM OnboardingTemplates WHERE id = ? AND status IN ('Active', 'Archived')", array((int) $id));
        if (empty($rows)) {
            return null;
        }
        $workflow = $this->fromRow($rows[0]);
        $workflow['id'] = (int) $rows[0]['id'];
        $workflow['status'] = $rows[0]['status'];
        $workflow['library_key'] = $rows[0]['library_key'];
        return $workflow;
    }

    /**
     * Create (no id) or update a custom workflow. Returns [id|null, errors].
     */
    public function save($id, array $input, $user)
    {
        list($workflow, $errors) = $this->validate($input);
        if (!empty($errors)) {
            return array(null, $errors);
        }
        $template = new OnboardingTemplate();
        if (!empty($id)) {
            $template->Load("id = ? AND status IN ('Active', 'Archived')", array((int) $id));
            if (empty($template->id)) {
                return array(null, array('Workflow not found.'));
            }
        } else {
            $template->kind = 'onboarding';
            $template->track = $workflow['track'];
            $template->status = 'Active';
            $template->created_by = (int) $user->id;
            $template->created = Db::now();
            $template->library_key = isset($input['library_key']) && $this->libraryItem($input['library_key'])
                ? $input['library_key'] : null;
        }
        $template->name = $workflow['name'];
        $template->description = $workflow['description'];
        $template->welcome_message = $workflow['welcome_message'];
        $template->welcome_video_url = $workflow['welcome_video_url'];
        $template->first_day_info = $workflow['first_day_info'];
        $template->steps = json_encode($workflow['steps']);
        $template->updated = Db::now();
        if (!$template->Save()) {
            return array(null, array('The workflow could not be saved: ' . $template->ErrorMsg()));
        }
        return array((int) $template->id, array());
    }

    public function cloneLibrary($key, $user)
    {
        $item = $this->libraryItem($key);
        if ($item === null) {
            return array(null, array('Workflow not found.'));
        }
        $item['name'] = mb_substr($item['name'] . ' (custom)', 0, 150);
        $item['library_key'] = $key;
        return $this->save(null, $item, $user);
    }

    public function duplicate($id, $user)
    {
        $workflow = $this->load($id);
        if ($workflow === null) {
            return array(null, array('Workflow not found.'));
        }
        $workflow['name'] = mb_substr($workflow['name'] . ' (copy)', 0, 150);
        return $this->save(null, $workflow, $user);
    }

    public function setArchived($id, $archived)
    {
        $template = new OnboardingTemplate();
        $template->Load("id = ? AND status IN ('Active', 'Archived')", array((int) $id));
        if (empty($template->id)) {
            return false;
        }
        $template->status = $archived ? 'Archived' : 'Active';
        $template->updated = Db::now();
        return (bool) $template->Save();
    }

    /** Delete a custom workflow that was never used; used ones can only be archived. */
    public function delete($id)
    {
        $used = Db::rows('SELECT id FROM Onboardings WHERE template = ? LIMIT 1', array((int) $id));
        if (!empty($used)) {
            return 'This workflow has been used. Archive it instead.';
        }
        $template = new OnboardingTemplate();
        $template->Load("id = ? AND status IN ('Active', 'Archived')", array((int) $id));
        if (empty($template->id)) {
            return 'Workflow not found.';
        }
        $template->Delete();
        return null;
    }

    /**
     * Resolve the workflow chosen when inviting: "system:<key>" or "custom:<id>".
     * A standard workflow gets a hidden System row the first time it is used, so
     * onboardings can point at a template id. Returns [templateId, workflow] or null.
     */
    public function resolve($value)
    {
        $value = (string) $value;
        if (strpos($value, 'system:') === 0) {
            $key = substr($value, 7);
            $item = $this->libraryItem($key);
            if ($item === null) {
                return null;
            }
            list($workflow) = $this->validate($item);
            $rows = Db::rows("SELECT id FROM OnboardingTemplates WHERE status = 'System' AND library_key = ? LIMIT 1", array($key));
            if (!empty($rows)) {
                $templateId = (int) $rows[0]['id'];
            } else {
                $template = new OnboardingTemplate();
                $template->kind = 'onboarding';
                $template->track = $workflow['track'];
                $template->status = 'System';
                $template->library_key = $key;
                $template->name = $workflow['name'];
                $template->created = Db::now();
                $template->updated = Db::now();
                $template->Save();
                $templateId = (int) $template->id;
            }
            return array($templateId, $workflow);
        }
        if (strpos($value, 'custom:') === 0) {
            $workflow = $this->load((int) substr($value, 7));
            if ($workflow === null || $workflow['status'] !== 'Active') {
                return null;
            }
            return array($workflow['id'], $workflow);
        }
        return null;
    }

    /** Options for the invite dialog: standard and active custom workflows. */
    public function choices($track = 'new_hire')
    {
        $out = array();
        foreach ($this->library($track) as $item) {
            $out[] = array('value' => $item['value'], 'label' => $item['name'], 'group' => 'standard', 'step_count' => $item['step_count']);
        }
        foreach ($this->listCustom('Active', $track) as $item) {
            $out[] = array('value' => $item['value'], 'label' => $item['name'], 'group' => 'custom', 'step_count' => $item['step_count']);
        }
        return $out;
    }

    // --- validation ---------------------------------------------------------------------

    /**
     * Clean and check a workflow. Returns [workflow, errors].
     */
    public function validate(array $input)
    {
        $errors = array();
        $name = isset($input['name']) ? trim((string) $input['name']) : '';
        if ($name === '') {
            $errors[] = 'Give the workflow a name.';
        }
        $workflow = array(
            'name' => mb_substr($name, 0, 150),
            'description' => $this->text($input, 'description', 2000),
            'welcome_message' => $this->text($input, 'welcome_message', 5000),
            'welcome_video_url' => null,
            'first_day_info' => $this->text($input, 'first_day_info', 5000),
            'track' => self::track(isset($input['track']) ? $input['track'] : null),
            'steps' => array(),
        );
        $video = $this->text($input, 'welcome_video_url', 500);
        if ($video !== null) {
            if (!preg_match('#^https://#i', $video)) {
                $errors[] = 'The welcome video link must start with https://';
            } else {
                $workflow['welcome_video_url'] = $video;
            }
        }

        $steps = isset($input['steps']) && is_array($input['steps']) ? array_values($input['steps']) : array();
        if (count($steps) > self::MAX_STEPS) {
            $errors[] = sprintf('A workflow can have at most %d steps.', self::MAX_STEPS);
            $steps = array_slice($steps, 0, self::MAX_STEPS);
        }
        $keys = array();
        foreach ($steps as $i => $step) {
            if (!is_array($step)) {
                continue;
            }
            $label = sprintf('Step %d', $i + 1);
            $type = isset($step['type']) ? (string) $step['type'] : '';
            if (!in_array($type, self::TYPES, true)) {
                $errors[] = $label . ': unknown step type.';
                continue;
            }
            $title = isset($step['title']) ? trim((string) $step['title']) : '';
            if ($title === '') {
                $errors[] = $label . ': give the step a title.';
            }
            $key = isset($step['key']) && preg_match('/^[a-z0-9_\-]{1,60}$/', (string) $step['key'])
                ? (string) $step['key'] : 'step_' . ($i + 1);
            while (isset($keys[$key])) {
                $key .= '_' . ($i + 1);
            }
            $keys[$key] = true;

            $owner = isset($step['owner_type']) && in_array($step['owner_type'], self::OWNERS, true)
                ? $step['owner_type'] : 'new_hire';
            // The new hire does their own steps; tasks and asset hand-overs are for others.
            if (in_array($type, self::NEW_HIRE_TYPES, true)) {
                $owner = 'new_hire';
            } elseif ($type === 'set_manager' || $owner === 'new_hire') {
                // Setting the manager is HR's job.
                $owner = 'hr';
            }
            $ownerEmployee = null;
            if ($owner === 'employee') {
                $ownerEmployee = isset($step['owner_employee']) ? (int) $step['owner_employee'] : 0;
                if ($ownerEmployee <= 0 || empty(Db::rows('SELECT id FROM Employees WHERE id = ?', array($ownerEmployee)))) {
                    $errors[] = $label . ': choose who does this step.';
                    $ownerEmployee = null;
                }
            }
            $offset = isset($step['due_offset_days']) && is_numeric($step['due_offset_days'])
                ? max(-365, min(365, (int) $step['due_offset_days'])) : 0;
            $phase = isset($step['phase']) && in_array($step['phase'], self::PHASES, true)
                ? $step['phase'] : $this->phaseFor($offset);

            $config = isset($step['config']) && is_array($step['config']) ? $step['config'] : array();
            list($config, $configError) = $this->cleanConfig($type, $config);
            if ($configError !== null) {
                $errors[] = $label . ': ' . $configError;
            }

            $workflow['steps'][] = array(
                'key' => $key,
                'phase' => $phase,
                'type' => $type,
                'title' => mb_substr($title, 0, 200),
                'instructions' => $this->text($step, 'instructions', 5000),
                'owner_type' => $owner,
                'owner_employee' => $ownerEmployee,
                'due_offset_days' => $offset,
                'required' => !isset($step['required']) || !empty($step['required']),
                // Documents can be reviewed by HR; forms are reviewed in Forms.
                'needs_review' => $type === 'document' && (!isset($step['needs_review']) || !empty($step['needs_review'])),
                'config' => $config,
            );
        }
        return array($workflow, $errors);
    }

    private function cleanConfig($type, array $config)
    {
        $out = array();
        switch ($type) {
            case 'info':
            case 'acknowledge':
                $out['text'] = $this->text($config, 'text', 20000);
                $link = $this->text($config, 'link', 500);
                if ($link !== null) {
                    if (!preg_match('#^https?://#i', $link)) {
                        return array($out, 'links must start with https:// or http://');
                    }
                    $out['link'] = $link;
                    $out['link_label'] = $this->text($config, 'link_label', 120);
                }
                if ($type === 'acknowledge' && $out['text'] === null && !isset($out['link'])) {
                    return array($out, 'add the text to acknowledge or a link to it.');
                }
                return array($out, null);
            case 'document':
                if (!empty($config['document_type'])) {
                    $out['document_type'] = (int) $config['document_type'];
                } elseif (!empty($config['document_name'])) {
                    $out['document_name'] = mb_substr((string) $config['document_name'], 0, 100);
                } else {
                    return array($out, 'choose the document type.');
                }
                return array($out, null);
            case 'esign':
                if (empty($config['document'])) {
                    return array($out, 'choose the document to sign.');
                }
                $out['document'] = (int) $config['document'];
                return array($out, null);
            case 'course':
                if (empty($config['course'])) {
                    return array($out, 'choose the course.');
                }
                $out['course'] = (int) $config['course'];
                return array($out, null);
            case 'asset':
                if (!empty($config['asset_type'])) {
                    $out['asset_type'] = (int) $config['asset_type'];
                } elseif (!empty($config['asset_type_name'])) {
                    $out['asset_type_name'] = mb_substr((string) $config['asset_type_name'], 0, 100);
                }
                return array($out, null);
            default:
                return array($out, null);
        }
    }

    public function phaseFor($offset)
    {
        if ($offset < 0) {
            return 'before';
        }
        if ($offset === 0) {
            return 'day1';
        }
        return $offset <= 7 ? 'week1' : 'month1';
    }

    // --- helpers ------------------------------------------------------------------------

    private function fromRow(array $row)
    {
        $steps = json_decode((string) $row['steps'], true);
        return array(
            'name' => $row['name'],
            'description' => $row['description'],
            'welcome_message' => $row['welcome_message'],
            'welcome_video_url' => $row['welcome_video_url'],
            'first_day_info' => $row['first_day_info'],
            'track' => self::track(isset($row['track']) ? $row['track'] : null),
            'steps' => is_array($steps) ? $steps : array(),
        );
    }

    private function summary(array $workflow, array $extra)
    {
        $steps = isset($workflow['steps']) && is_array($workflow['steps']) ? $workflow['steps'] : array();
        $types = array();
        $unavailable = 0;
        foreach ($steps as $step) {
            $type = isset($step['type']) ? $step['type'] : '';
            $types[$type] = isset($types[$type]) ? $types[$type] + 1 : 1;
            if (!Integrations::available($type)) {
                $unavailable++;
            }
        }
        return array_merge(array(
            'name' => $workflow['name'],
            'description' => isset($workflow['description']) ? $workflow['description'] : null,
            'step_count' => count($steps),
            'types' => $types,
            'unavailable_steps' => $unavailable,
            'audience' => isset($workflow['audience']) ? $workflow['audience'] : null,
            'track' => self::track(isset($workflow['track']) ? $workflow['track'] : null),
        ), $extra);
    }

    private function text(array $input, $key, $max)
    {
        if (!isset($input[$key]) || !is_scalar($input[$key])) {
            return null;
        }
        $value = trim((string) $input[$key]);
        return $value === '' ? null : mb_substr($value, 0, $max);
    }
}
