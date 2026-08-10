<?php
/**
 * Helpers shared by the integration tests: acting as a given user, describing a
 * model's owner column, generically seeding a throwaway row, and cleaning up.
 *
 * Kept framework-free (matches the repo's standalone-script test convention, e.g.
 * test/appshell/menu_filter_test.php) so it runs on any PHP the app runs on,
 * without depending on a PHPUnit version.
 */

use Classes\BaseService;

class TestContext
{
    /** @var array<int,array{table:string,id:mixed}> rows to delete on cleanup, LIFO */
    private $seeded = array();

    public function db()
    {
        return BaseService::getInstance()->getDB();
    }

    /**
     * Run a query and return a plain array of assoc rows. The app's ORM adapter
     * (MyORM\MySqlActiveRecord::Execute) already returns fetch_all(MYSQLI_ASSOC),
     * so this normalises the false/true/array cases.
     */
    public function query($sql, array $params = array())
    {
        try {
            $rows = $this->db()->Execute($sql, $params);
        } catch (\Throwable $e) {
            // e.g. querying a model whose table does not exist in this fixture.
            return array();
        }
        return is_array($rows) ? $rows : array();
    }

    /** True if $table exists in the current database. */
    public function tableExists($table)
    {
        $rows = $this->query(
            "SELECT 1 FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1",
            array($table)
        );
        return !empty($rows);
    }

    /** True if $table has column $col. */
    public function tableHasColumn($table, $col)
    {
        $rows = $this->query(
            "SELECT 1 FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1",
            array($table, $col)
        );
        return !empty($rows);
    }

    /** The DB table backing a model instance, or null. */
    public function tableFor($modelObj)
    {
        return isset($modelObj->table) ? $modelObj->table : null;
    }

    /**
     * The employee-owner column for a model, or null if it is not employee-owned.
     * Uses the model's own getUserOnlyMeAccessField() (what checkSecureAccess scopes
     * on) but only when that column actually exists on the table AND names an employee
     * — "id" (the Employee model's own field) is treated separately by the caller.
     */
    public function ownerColumnFor($modelObj, $table)
    {
        if (!method_exists($modelObj, 'getUserOnlyMeAccessField')) {
            return null;
        }
        $field = $modelObj->getUserOnlyMeAccessField();
        if (empty($field) || !$this->tableHasColumn($table, $field)) {
            return null;
        }
        return $field;
    }

    /**
     * Make the current session act as $empId at $level, with no admin-profile switch.
     * Mirrors what server.includes does from a real session.
     */
    public function actAs($empId, $level = 'Employee', $userId = null)
    {
        \Utils\SessionUtils::saveSessionObject('admin_current_profile', null);
        $user = new \Users\Common\Model\User();
        $user->id = $userId !== null ? $userId : (900000 + (int) $empId);
        $user->user_level = $level;
        $user->employee = $empId;
        $user->user_roles = null;
        BaseService::getInstance()->setCurrentUser($user);
        BaseService::getInstance()->setCurrentProfileId($empId);
        \Utils\SessionUtils::saveSessionObject('user', $user);
        return $user;
    }

    /**
     * Act as $empId at $level through a REAL Users row (seeded if absent), so code
     * that resolves the employee from the database — e.g. the ownership merge in
     * BaseModel::getRoleBasedAccess(), which calls getEmployeeByUserId() — behaves
     * exactly as it does for a real session. The plain actAs() fabricates a user id
     * with no Users row, which silently disables that merge; self-access tests MUST
     * use this variant or own-record grants look denied.
     */
    public function actAsRealUser($empId, $level = 'Employee')
    {
        // Always a THROWAWAY row (cleaned up like any seeded row) — never mutate an
        // existing Users row, so the fixture is left exactly as found. Duplicate
        // Users per employee are fine: getEmployeeByUserId() resolves user -> employee.
        $suffix = $empId . '_' . mt_rand(1000, 9999);
        $userId = $this->seedRow('Users', array(
            'employee' => $empId,
            'user_level' => $level,
            'username' => 'zztest_u' . $suffix,
            'email' => 'zztest_u' . $suffix . '@example.com',
        ));
        return $this->actAs($empId, $level, (int) $userId);
    }

    /**
     * Act as a NON-LOGGED-IN visitor: user level "Anonymous", no employee, no profile.
     * checkSecureAccess() reads the level straight off the in-memory current user and
     * dispatches to getAnonymousAccess(), so this is the shape an anonymous request
     * presents — no Users row is (or can be) written: the user_level enum has no
     * 'Anonymous' value.
     */
    public function actAsAnonymous()
    {
        \Utils\SessionUtils::saveSessionObject('admin_current_profile', null);
        $user = new \Users\Common\Model\User();
        $user->id = 0;
        $user->user_level = 'Anonymous';
        $user->employee = null;
        $user->user_roles = null;
        BaseService::getInstance()->setCurrentUser($user);
        BaseService::getInstance()->setCurrentProfileId(null);
        \Utils\SessionUtils::saveSessionObject('user', $user);
        return $user;
    }

    /**
     * Two distinct employee ids (attacker A, victim B), creating them if needed.
     * Overridable via ICEHRM_TEST_ATTACKER / ICEHRM_TEST_VICTIM so a run can pin a
     * known plain-Employee attacker rather than whatever sorts first (employee 1 is
     * usually the seeded admin's own record — a poor choice of "attacker").
     */
    public function ensureTwoEmployees()
    {
        $a = getenv('ICEHRM_TEST_ATTACKER');
        $b = getenv('ICEHRM_TEST_VICTIM');
        if ($a && $b) { return array((int) $a, (int) $b); }

        $rows = $this->query("SELECT id FROM Employees ORDER BY id LIMIT 3");
        $ids = array();
        foreach ($rows as $r) { $ids[] = (int) $r['id']; }
        while (count($ids) < 2) {
            $ids[] = $this->seedRow('Employees', array(
                'first_name' => 'ZZTest', 'last_name' => 'Emp' . count($ids), 'status' => 'Active',
            ));
        }
        // Prefer an attacker that is not employee 1 (the seeded admin's record).
        $attacker = $ids[0] === 1 && count($ids) > 2 ? $ids[1] : $ids[0];
        $victim = null;
        foreach ($ids as $id) { if ($id !== $attacker) { $victim = $id; break; } }
        return array($attacker, $victim);
    }

    /** Seed a throwaway employee, returning its id. */
    public function seedEmployee(array $overrides = array())
    {
        $overrides += array('first_name' => 'ZZTest', 'last_name' => 'Emp', 'status' => 'Active');
        return (int) $this->seedRow('Employees', $overrides);
    }

    /**
     * A manager fixture for the record-scope checks (finding 2.11): a manager, one
     * DIRECT subordinate (supervisor = manager), and one unrelated employee the
     * manager does NOT supervise. Returns array($managerId, $subordinateId, $otherId).
     */
    public function ensureManagerFixture()
    {
        $mgr = $this->seedEmployee(array('first_name' => 'ZZMgr'));
        $sub = $this->seedEmployee(array('first_name' => 'ZZSub', 'supervisor' => $mgr));
        // Non-subordinate: supervised by nobody the manager manages.
        $other = $this->seedEmployee(array('first_name' => 'ZZOther', 'supervisor' => 0));
        return array($mgr, $sub, $other);
    }

    /**
     * The full set of models reachable via t=<Model>: the registered class map PLUS
     * the \Model\<Class> fallback that getFullQualifiedModelClassName() also resolves
     * (findings 2.8/2.12). Shared by every getElement sweep. Returns [model => fqcn].
     */
    public function allModels()
    {
        $map = BaseService::getInstance()->getModelClassMap();
        $modelDir = APP_BASE_PATH . 'src/Model';
        if (is_dir($modelDir)) {
            foreach (glob($modelDir . '/*.php') as $file) {
                $cls = basename($file, '.php');
                if (isset($map[$cls])) { continue; }
                $fqcn = '\\Model\\' . $cls;
                if (class_exists($fqcn) && is_subclass_of($fqcn, '\\Model\\BaseModel')) {
                    $map[$cls] = $fqcn;
                }
            }
        }
        ksort($map);
        return $map;
    }

    /**
     * The employee-owner column for a model+table, or null if it is not employee-owned.
     * 'id' for the Employees table itself; otherwise the model's
     * getUserOnlyMeAccessField() when that column exists and the table has an employee
     * column. (Same rule EmployeeElementAccessTest uses.)
     */
    public function employeeOwnerColumn($modelObj, $table)
    {
        if ($table === 'Employees') { return 'id'; }
        $col = $this->ownerColumnFor($modelObj, $table);
        if (!$col) { return null; }
        if ($col !== 'employee' && !$this->tableHasColumn($table, 'employee')) { return null; }
        if (!$this->tableHasColumn($table, $col)) { return null; }
        return $col;
    }

    /**
     * A text column on $table that the write-verb sweeps can scribble on to observe
     * whether an edit landed. It must be BOTH a real text column AND one the model
     * maps (addElement() only copies keys present in getObjectKeys(), so a column
     * outside that set would never change and every save would look denied).
     *
     * Returns null when the model has no such column — the caller then reports the
     * model UNTESTED rather than silently passing.
     */
    public function writableTextColumn($modelObj, $table, $excludeCol = null)
    {
        $probe = $this->writableProbeColumn($modelObj, $table, $excludeCol);
        return $probe === null ? null : $probe['column'];
    }

    /**
     * As above, but also falls back to a plain integer column when the model maps no
     * suitable text column (EmployeeLanguage, LeaveRule, LeaveGroupEmployee …), so
     * those models are actually swept instead of being reported UNTESTED forever.
     *
     * @return array|null array('column' => string, 'type' => 'text'|'int')
     */
    public function writableProbeColumn($modelObj, $table, $excludeCol = null)
    {
        $keys = array();
        try {
            $keys = (array) $modelObj->getObjectKeys();
        } catch (\Throwable $e) {
            return null;
        }

        $skip = array('id', 't', 'a', 'created', 'updated', 'csrf');
        if ($excludeCol !== null) { $skip[] = $excludeCol; }

        $mapped = function ($col) use ($keys, $skip) {
            if (in_array($col, $skip, true)) { return false; }
            // getObjectKeys() is keyed by column name for mapped columns.
            return isset($keys[$col]) || in_array($col, $keys, true);
        };

        $text = $this->query(
            "SELECT COLUMN_NAME AS column_name FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ?
               AND DATA_TYPE IN ('varchar','text','tinytext','mediumtext','longtext')
               AND (CHARACTER_MAXIMUM_LENGTH IS NULL OR CHARACTER_MAXIMUM_LENGTH >= 16)
             ORDER BY ordinal_position",
            array($table)
        );
        foreach ($text as $r) {
            if ($mapped($r['column_name'])) {
                return array('column' => $r['column_name'], 'type' => 'text');
            }
        }

        $ints = $this->query(
            "SELECT COLUMN_NAME AS column_name FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ?
               AND DATA_TYPE IN ('int','bigint','smallint','mediumint')
               AND EXTRA NOT LIKE '%auto_increment%'
             ORDER BY ordinal_position",
            array($table)
        );
        foreach ($ints as $r) {
            if ($mapped($r['column_name'])) {
                return array('column' => $r['column_name'], 'type' => 'int');
            }
        }

        return null;
    }

    /** True if a row with this id still exists. */
    public function rowExists($table, $id)
    {
        $rows = $this->query("SELECT 1 AS x FROM `$table` WHERE id = ? LIMIT 1", array($id));
        return !empty($rows);
    }

    /**
     * The column => value map needed to insert a row into $table: every NOT NULL
     * column that has no default, filled with a type-appropriate dummy, plus
     * $overrides. Shared by seedRow() (which inserts it directly) and the "add"
     * sweep (which submits it through BaseService::addElement(), so the row is
     * created by the application rather than by SQL).
     */
    public function requiredInsertValues($table, array $overrides = array())
    {
        // Alias to lowercase — MySQL 8 returns information_schema keys UPPERCASE.
        $schema = $this->query(
            "SELECT COLUMN_NAME AS column_name, DATA_TYPE AS data_type,
                    IS_NULLABLE AS is_nullable, COLUMN_DEFAULT AS column_default,
                    EXTRA AS extra, COLUMN_TYPE AS column_type
             FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? ORDER BY ordinal_position",
            array($table)
        );
        if (empty($schema)) {
            throw new \RuntimeException("no such table: $table");
        }

        $vals = array();
        foreach ($schema as $f) {
            $name = $f['column_name'];
            $auto = strpos((string) $f['extra'], 'auto_increment') !== false;
            $nullable = strtoupper($f['is_nullable']) === 'YES';
            $hasDefault = $f['column_default'] !== null;
            if ($auto) { continue; }
            if (array_key_exists($name, $overrides)) {
                $vals[$name] = $overrides[$name];
            } elseif (!$nullable && !$hasDefault) {
                $vals[$name] = $this->dummyFor($f['data_type'], $f['column_type']);
            }
        }
        foreach ($overrides as $k => $v) { $vals[$k] = $v; }

        return $vals;
    }

    /**
     * Insert a row into $table, filling every NOT NULL column that has no default
     * with a type-appropriate dummy, then applying $overrides. Returns the new id, or
     * throws so the caller can mark the model UNTESTED.
     */
    public function seedRow($table, array $overrides = array())
    {
        // Alias to lowercase — MySQL 8 returns information_schema keys UPPERCASE.
        $schema = $this->query(
            "SELECT COLUMN_NAME AS column_name, DATA_TYPE AS data_type,
                    IS_NULLABLE AS is_nullable, COLUMN_DEFAULT AS column_default,
                    EXTRA AS extra, COLUMN_TYPE AS column_type
             FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? ORDER BY ordinal_position",
            array($table)
        );
        if (empty($schema)) {
            throw new \RuntimeException("no such table: $table");
        }
        $pk = 'id';
        $vals = array();
        foreach ($schema as $f) {
            $name = $f['column_name'];
            $auto = strpos((string) $f['extra'], 'auto_increment') !== false;
            $nullable = strtoupper($f['is_nullable']) === 'YES';
            $hasDefault = $f['column_default'] !== null;
            if ($auto) { continue; }
            if (array_key_exists($name, $overrides)) {
                $vals[$name] = $overrides[$name];
            } elseif (!$nullable && !$hasDefault) {
                $vals[$name] = $this->dummyFor($f['data_type'], $f['column_type']);
            }
        }
        foreach ($overrides as $k => $v) { $vals[$k] = $v; }
        if (empty($vals)) {
            // Every column is nullable/defaulted (Backups, Candidates, PayrollColumns,
            // ...): insert an all-defaults row so these tables are testable at all
            // instead of reporting UNTESTED forever.
            $sql = "INSERT INTO `$table` () VALUES ()";
        } else {
            $names = array_keys($vals);
            $place = implode(',', array_fill(0, count($names), '?'));
            $sql = "INSERT INTO `$table` (`" . implode('`,`', $names) . "`) VALUES ($place)";
        }
        // Throwaway test DB: a victim row only needs a valid id + owner column for the
        // getElement check, so disable FK enforcement to seed models whose real parents
        // (leave types, categories, ...) are not present in the minimal fixture. The row
        // is deleted again in cleanup().
        $this->db()->Execute("SET FOREIGN_KEY_CHECKS = 0");
        $ok = $this->db()->Execute($sql, array_values($vals));
        $err = $ok === false ? $this->db()->ErrorMsg() : null;
        $this->db()->Execute("SET FOREIGN_KEY_CHECKS = 1");
        if ($ok === false) {
            throw new \RuntimeException("insert failed for $table: " . $err);
        }
        $idRows = $this->query("SELECT LAST_INSERT_ID() AS id");
        $id = isset($idRows[0]['id']) ? $idRows[0]['id'] : null;
        if (empty($id)) {
            throw new \RuntimeException("could not read insert id for $table");
        }
        $this->seeded[] = array('table' => $table, 'id' => $id, 'pk' => $pk);
        return $id;
    }

    private function dummyFor($dataType, $columnType)
    {
        switch ($dataType) {
            case 'int': case 'bigint': case 'smallint': case 'tinyint':
            case 'decimal': case 'float': case 'double':
                return 0;
            case 'date': return '2020-01-01';
            case 'datetime': case 'timestamp': return '2020-01-01 00:00:00';
            case 'time': return '00:00:00';
            case 'enum':
                if (preg_match("/^enum\\('([^']*)'/", $columnType, $m)) { return $m[1]; }
                return '';
            default:
                // Unique-per-row so a UNIQUE column (filename, code, ...) does not
                // collide when a test seeds more than one row of the same model.
                $value = 'test' . (++$this->dummyCounter);
                // Narrow columns exist (PayGrade.currency is varchar(3)); overflowing
                // one fails the insert for reasons unrelated to what is being tested.
                if (preg_match('/\((\d+)\)/', (string) $columnType, $m)) {
                    $max = (int) $m[1];
                    if ($max > 0 && strlen($value) > $max) {
                        $value = substr($value, -$max);
                    }
                }
                return $value;
        }
    }

    private $dummyCounter = 0;

    /** Delete everything seeded, most-recent first. */
    public function cleanup()
    {
        foreach (array_reverse($this->seeded) as $row) {
            $this->db()->Execute("DELETE FROM `{$row['table']}` WHERE `{$row['pk']}` = ?", array($row['id']));
        }
        $this->seeded = array();
    }
}
