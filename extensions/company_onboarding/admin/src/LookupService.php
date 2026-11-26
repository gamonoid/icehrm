<?php

namespace CompanyOnboardingAdmin;

/**
 * The simple name lists the wizard edits: job titles, employment types, work locations
 * and leave types. Names are unique (ignoring case); an entry in use by an employee (or
 * a leave request) cannot be removed.
 */
class LookupService
{
    /**
     * list key => table, the column that marks it used (table.column), and whether the
     * list carries "days per year" (leave types).
     */
    const LISTS = array(
        'job_titles' => array('table' => 'JobTitles', 'used' => array('Employees.job_title')),
        'employment' => array('table' => 'EmploymentStatus', 'used' => array('Employees.employment_status')),
        'locations' => array('table' => 'WorkLocations', 'used' => array('Employees.work_location')),
        'leave_types' => array('table' => 'LeaveTypes', 'used' => array('EmployeeLeaves.leave_type', 'LeaveRules.leave_type'), 'days' => true),
    );

    public static function exists($key)
    {
        return isset(self::LISTS[$key]) && Db::tableExists(self::LISTS[$key]['table']);
    }

    public function items($key)
    {
        $list = self::LISTS[$key];
        $used = $this->usedIds($list);
        $cols = !empty($list['days']) ? 'id, name, default_per_year' : 'id, name';
        $items = array();
        foreach (Db::rows("SELECT $cols FROM {$list['table']} ORDER BY name") as $row) {
            $item = array(
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'used' => isset($used[(int) $row['id']]),
            );
            if (!empty($list['days'])) {
                $item['days'] = (float) $row['default_per_year'];
            }
            $items[] = $item;
        }
        return $items;
    }

    /**
     * Add entries, skipping names that already exist. $entries: list of
     * array('name' => ..., 'days' => ...). Returns the number added.
     */
    public function add($key, array $entries)
    {
        $list = self::LISTS[$key];
        $existing = array();
        foreach (Db::rows("SELECT name FROM {$list['table']}") as $row) {
            $existing[mb_strtolower(trim($row['name']))] = true;
        }
        $added = 0;
        foreach ($entries as $entry) {
            $name = isset($entry['name']) && is_scalar($entry['name']) ? mb_substr(trim((string) $entry['name']), 0, 100) : '';
            if ($name === '' || isset($existing[mb_strtolower($name)])) {
                continue;
            }
            $this->insert($key, $name, isset($entry['days']) ? $entry['days'] : null);
            $existing[mb_strtolower($name)] = true;
            $added++;
        }
        return $added;
    }

    public function update($key, $id, array $input)
    {
        $list = self::LISTS[$key];
        $name = isset($input['name']) && is_scalar($input['name']) ? mb_substr(trim((string) $input['name']), 0, 100) : '';
        if ($name === '') {
            return 'Enter a name.';
        }
        if (empty(Db::rows("SELECT id FROM {$list['table']} WHERE id = ?", array((int) $id)))) {
            return 'Not found.';
        }
        if (!empty(Db::rows("SELECT id FROM {$list['table']} WHERE LOWER(name) = LOWER(?) AND id <> ?", array($name, (int) $id)))) {
            return 'This name is already in the list.';
        }
        if (!empty($list['days'])) {
            $days = $this->days(isset($input['days']) ? $input['days'] : null);
            Db::db()->Execute("UPDATE {$list['table']} SET name = ?, default_per_year = ? WHERE id = ?", array($name, $days, (int) $id));
        } else {
            Db::db()->Execute("UPDATE {$list['table']} SET name = ? WHERE id = ?", array($name, (int) $id));
        }
        return null;
    }

    public function remove($key, $id)
    {
        $list = self::LISTS[$key];
        $used = $this->usedIds($list);
        if (isset($used[(int) $id])) {
            return 'This is in use, so it cannot be removed.';
        }
        if (Db::db()->Execute("DELETE FROM {$list['table']} WHERE id = ?", array((int) $id)) === false) {
            return 'This is still used elsewhere, so it cannot be removed.';
        }
        return null;
    }

    private function insert($key, $name, $days)
    {
        $now = Db::now();
        switch ($key) {
            case 'job_titles':
                Db::db()->Execute(
                    "INSERT INTO JobTitles (code, name, description, specification) VALUES (?, ?, '', '')",
                    array($this->jobCode($name), $name)
                );
                break;
            case 'employment':
                Db::db()->Execute("INSERT INTO EmploymentStatus (name, description) VALUES (?, '')", array($name));
                break;
            case 'locations':
                Db::db()->Execute('INSERT INTO WorkLocations (name, created, updated) VALUES (?, ?, ?)', array($name, $now, $now));
                break;
            case 'leave_types':
                Db::db()->Execute(
                    'INSERT INTO LeaveTypes (name, default_per_year) VALUES (?, ?)',
                    array($name, $this->days($days))
                );
                break;
        }
    }

    /** A short unique code from the initials of the title, e.g. "Finance Manager" -> "FM". */
    private function jobCode($name)
    {
        $words = preg_split('/[^A-Za-z0-9]+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $code = '';
        foreach ($words as $word) {
            $code .= strtoupper(substr($word, 0, 1));
        }
        if (strlen($code) < 2) {
            $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        }
        $code = substr($code === '' ? 'JT' : $code, 0, 7);
        $candidate = $code;
        $n = 1;
        while (!empty(Db::rows('SELECT id FROM JobTitles WHERE code = ? LIMIT 1', array($candidate)))) {
            $n++;
            $candidate = $code . $n;
        }
        return $candidate;
    }

    private function days($value)
    {
        $days = is_numeric($value) ? (float) $value : 0;
        return max(0, min(366, $days));
    }

    private function usedIds(array $list)
    {
        $used = array();
        foreach ($list['used'] as $ref) {
            list($table, $column) = explode('.', $ref);
            if (!Db::tableExists($table)) {
                continue;
            }
            foreach (Db::rows("SELECT DISTINCT `$column` AS id FROM `$table` WHERE `$column` IS NOT NULL") as $row) {
                $used[(int) $row['id']] = true;
            }
        }
        return $used;
    }
}
