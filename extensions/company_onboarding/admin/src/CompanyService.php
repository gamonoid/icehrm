<?php

namespace CompanyOnboardingAdmin;

use Classes\SettingsManager;

/**
 * Company details and structure for the setup wizard.
 *
 * The company's name is the "Company: Name" setting; its country and time zone live on
 * the root CompanyStructures row (type Company, no parent), which every other unit hangs
 * from.
 */
class CompanyService
{
    const UNIT_TYPES = array('Head Office', 'Regional Office', 'Department', 'Unit', 'Sub Unit', 'Other');

    /** Placeholder names a fresh install ships with. */
    const PLACEHOLDER_NAMES = array('Sample Company Pvt Ltd', 'IceHrm Pty Ltd', 'Your Company');

    /** Department sets offered as one-click starting points. */
    const PRESETS = array(
        'small' => array(
            'offices' => array(),
            'departments' => array('Management', 'Human Resources', 'Finance', 'Sales', 'Operations'),
        ),
        'office' => array(
            'offices' => array(array('Head Office', 'Head Office')),
            'departments' => array('Management', 'Human Resources', 'Finance', 'Sales', 'Marketing', 'Operations', 'Engineering'),
        ),
        'regional' => array(
            'offices' => array(array('Head Office', 'Head Office'), array('Regional Office', 'Regional Office')),
            'departments' => array('Management', 'Human Resources', 'Finance', 'Sales', 'Operations'),
        ),
    );

    // --- company --------------------------------------------------------------------

    public function root()
    {
        $rows = Db::rows("SELECT * FROM CompanyStructures WHERE parent IS NULL ORDER BY (type = 'Company') DESC, id LIMIT 1");
        return empty($rows) ? null : $rows[0];
    }

    public function getCompany()
    {
        $settings = SettingsManager::getInstance();
        $root = $this->root();
        $name = (string) $settings->getSetting('Company: Name');
        if (in_array($name, self::PLACEHOLDER_NAMES, true)) {
            $name = '';
        }
        return array(
            'name' => $name,
            'country' => $root !== null && preg_match('/^[A-Z]{2}$/', (string) $root['country']) ? $root['country'] : null,
            'timezone' => $root !== null ? $root['timezone'] : null,
            'weekly_hours' => $settings->getSetting('Company: Standard Weekly Hours'),
            'generate_numbers' => $settings->getSetting('Company: Generate Employee Numbers') === '1',
            'number_prefix' => (string) $settings->getSetting('Company: Employee Number Prefix'),
        );
    }

    /** @return array field => error */
    public function saveCompany(array $input)
    {
        $errors = array();
        $name = $this->str($input, 'name', 100);
        $country = $this->str($input, 'country', 2);
        $timezone = $this->str($input, 'timezone', 100);
        if ($name === '') {
            $errors['name'] = 'Enter the company name.';
        }
        if (!$this->validCountry($country)) {
            $errors['country'] = 'Choose the country.';
        }
        if (!in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            $errors['timezone'] = 'Choose the time zone.';
        }
        $hours = isset($input['weekly_hours']) && $input['weekly_hours'] !== '' ? (float) $input['weekly_hours'] : null;
        if ($hours !== null && ($hours < 1 || $hours > 80)) {
            $errors['weekly_hours'] = 'Enter between 1 and 80 hours.';
        }
        $prefix = $this->str($input, 'number_prefix', 10);
        if ($prefix !== '' && !preg_match('/^[A-Za-z0-9_\-\/]+$/', $prefix)) {
            $errors['number_prefix'] = 'Use letters, numbers, - or / only.';
        }
        if (!empty($errors)) {
            return $errors;
        }

        $settings = SettingsManager::getInstance();
        $settings->addSetting('Company: Name', $name);
        if ($hours !== null) {
            $settings->addSetting('Company: Standard Weekly Hours', (string) $hours);
        }
        $settings->addSetting('Company: Generate Employee Numbers', !empty($input['generate_numbers']) ? '1' : '0');
        $settings->addSetting('Company: Employee Number Prefix', $prefix);

        $root = $this->root();
        if ($root === null) {
            Db::db()->Execute(
                "INSERT INTO CompanyStructures (title, description, type, country, parent, timezone) VALUES (?, '', 'Company', ?, NULL, ?)",
                array($name, $country, $timezone)
            );
        } else {
            Db::db()->Execute(
                'UPDATE CompanyStructures SET title = ?, country = ?, timezone = ? WHERE id = ?',
                array($name, $country, $timezone, (int) $root['id'])
            );
        }
        return array();
    }

    // --- structure ------------------------------------------------------------------

    public function units()
    {
        $counts = array();
        foreach (Db::rows("SELECT department, COUNT(*) AS c FROM Employees WHERE status <> 'Terminated' AND department IS NOT NULL GROUP BY department") as $row) {
            $counts[(int) $row['department']] = (int) $row['c'];
        }
        $units = array();
        foreach (Db::rows('SELECT id, title, type, parent, country, timezone FROM CompanyStructures ORDER BY id') as $row) {
            $units[] = array(
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'type' => $row['type'],
                'parent' => $row['parent'] === null ? null : (int) $row['parent'],
                'country' => $row['country'],
                'timezone' => $row['timezone'],
                'employees' => isset($counts[(int) $row['id']]) ? $counts[(int) $row['id']] : 0,
            );
        }
        return $units;
    }

    /** @return array [id|null, error|null] */
    public function addUnit(array $input)
    {
        $title = $this->str($input, 'title', 100);
        $type = $this->str($input, 'type', 30);
        if ($title === '') {
            return array(null, 'Enter a name.');
        }
        if (!in_array($type, self::UNIT_TYPES, true)) {
            return array(null, 'Choose a type.');
        }
        $parent = $this->unit(isset($input['parent']) ? $input['parent'] : null);
        if ($parent === null) {
            $parent = $this->root();
        }
        if ($parent === null) {
            return array(null, 'Save the company details first.');
        }
        if ($this->siblingExists($title, (int) $parent['id'])) {
            return array(null, 'There is already a unit with this name here.');
        }
        list($country, $timezone) = $this->placeFor($input, $parent);
        Db::db()->Execute(
            "INSERT INTO CompanyStructures (title, description, type, country, parent, timezone) VALUES (?, '', ?, ?, ?, ?)",
            array($title, $type, $country, (int) $parent['id'], $timezone)
        );
        return array((int) Db::value('SELECT LAST_INSERT_ID()'), null);
    }

    public function updateUnit($id, array $input)
    {
        $unit = $this->unit($id);
        if ($unit === null) {
            return 'This unit was not found.';
        }
        $title = $this->str($input, 'title', 100);
        if ($title === '') {
            return 'Enter a name.';
        }
        $type = $unit['type'] === 'Company' ? 'Company' : $this->str($input, 'type', 30);
        if (!in_array($type, array_merge(self::UNIT_TYPES, array('Company')), true)) {
            return 'Choose a type.';
        }
        if ($unit['parent'] !== null && $this->siblingExists($title, (int) $unit['parent'], (int) $unit['id'])) {
            return 'There is already a unit with this name here.';
        }
        list($country, $timezone) = $this->placeFor($input, $unit);
        Db::db()->Execute(
            'UPDATE CompanyStructures SET title = ?, type = ?, country = ?, timezone = ? WHERE id = ?',
            array($title, $type, $country, $timezone, (int) $unit['id'])
        );
        return null;
    }

    public function deleteUnit($id)
    {
        $unit = $this->unit($id);
        if ($unit === null) {
            return 'This unit was not found.';
        }
        if ($unit['parent'] === null) {
            return 'The company itself cannot be removed.';
        }
        if ((int) Db::value('SELECT COUNT(*) FROM CompanyStructures WHERE parent = ?', array((int) $unit['id'])) > 0) {
            return 'Remove the units inside it first.';
        }
        if ((int) Db::value('SELECT COUNT(*) FROM Employees WHERE department = ?', array((int) $unit['id'])) > 0) {
            return 'Employees belong to this unit. Move them first.';
        }
        if (Db::db()->Execute('DELETE FROM CompanyStructures WHERE id = ?', array((int) $unit['id'])) === false) {
            return 'This unit is still used elsewhere, so it cannot be removed.';
        }
        return null;
    }

    /** Add a preset's offices and departments, skipping names that already exist. */
    public function applyPreset($key)
    {
        if (!isset(self::PRESETS[$key])) {
            return 'Unknown preset.';
        }
        $root = $this->root();
        if ($root === null) {
            return 'Save the company details first.';
        }
        $preset = self::PRESETS[$key];
        $home = $root;
        foreach ($preset['offices'] as $i => $office) {
            $id = $this->findOrAdd($office[0], $office[1], $root);
            if ($i === 0) {
                $home = $this->unit($id);
            }
        }
        foreach ($preset['departments'] as $department) {
            $this->findOrAdd($department, 'Department', $home);
        }
        return null;
    }

    // --- helpers --------------------------------------------------------------------

    private function findOrAdd($title, $type, array $parent)
    {
        $rows = Db::rows(
            'SELECT id FROM CompanyStructures WHERE parent = ? AND LOWER(title) = LOWER(?) LIMIT 1',
            array((int) $parent['id'], $title)
        );
        if (!empty($rows)) {
            return (int) $rows[0]['id'];
        }
        Db::db()->Execute(
            "INSERT INTO CompanyStructures (title, description, type, country, parent, timezone) VALUES (?, '', ?, ?, ?, ?)",
            array($title, $type, $parent['country'], (int) $parent['id'], $parent['timezone'])
        );
        return (int) Db::value('SELECT LAST_INSERT_ID()');
    }

    private function unit($id)
    {
        if (empty($id)) {
            return null;
        }
        $rows = Db::rows('SELECT * FROM CompanyStructures WHERE id = ?', array((int) $id));
        return empty($rows) ? null : $rows[0];
    }

    private function siblingExists($title, $parentId, $exceptId = 0)
    {
        return !empty(Db::rows(
            'SELECT id FROM CompanyStructures WHERE parent = ? AND LOWER(title) = LOWER(?) AND id <> ? LIMIT 1',
            array($parentId, $title, $exceptId)
        ));
    }

    /** Country and time zone from the input, else inherited from $from. */
    private function placeFor(array $input, array $from)
    {
        $country = $this->str($input, 'country', 2);
        $timezone = $this->str($input, 'timezone', 100);
        if (!$this->validCountry($country)) {
            $country = $from['country'];
        }
        if (!in_array($timezone, \DateTimeZone::listIdentifiers(), true)) {
            $timezone = $from['timezone'];
        }
        return array($country, $timezone);
    }

    private function validCountry($code)
    {
        return is_string($code) && preg_match('/^[A-Z]{2}$/', $code)
            && !empty(Db::rows('SELECT code FROM Country WHERE code = ? LIMIT 1', array($code)));
    }

    private function str(array $input, $key, $max)
    {
        if (!isset($input[$key]) || !is_scalar($input[$key])) {
            return '';
        }
        return mb_substr(trim((string) $input[$key]), 0, $max);
    }
}
