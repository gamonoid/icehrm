<?php
/**
 * Shared role sweep for a single access-matrix VERB.
 * ==================================================
 *
 * The per-verb tests (`AddAccessTest`, `SaveAccessTest`, `DeleteAccessTest`,
 * `GetAccessTest`) all ask the same question of every model — "who may do this
 * verb, to whose rows?" — and differ only in HOW the verb is invoked. This class
 * owns the shared half: the fixture, the six role scenarios, the expectation
 * rules, the reporting and the exit code. Each test supplies a single callback
 * that performs its verb and reports the OUTCOME.
 *
 * The six scenarios, and what each one is for:
 *
 *   | # | Actor (level)          | Row owner        | Expectation                    |
 *   |---|------------------------|------------------|--------------------------------|
 *   | 1 | Admin                  | someone else     | ALLOWED — over-restriction guard|
 *   | 2 | Manager                | their subordinate| allowed iff Manager declares it |
 *   | 3 | Manager                | NON-subordinate  | DENIED — row scope (2.11)      |
 *   | 4 | Employee               | THEMSELVES       | allowed iff declared (self)    |
 *   | 5 | Employee               | another employee | DENIED — the IDOR property     |
 *   | 6 | Anonymous              | someone else     | DENIED — nothing is public     |
 *   | 7 | Employee (supervisor)  | their DIRECT report| "element" only, iff Manager may |
 *
 * Scenarios 3, 5 and 6 are the security properties: a verb granted by role must
 * never reach rows outside that role's scope. Scenarios 1, 2 and 4 are the
 * over-restriction guards, so tightening one of the first three cannot silently
 * break legitimate use. Scenario 7 is both: an Employee who directly supervises
 * the owner may READ that row (employeeDirectReportScopeAllows), and must not be
 * able to do anything else to it — so it guards the widening in one direction and
 * pins its read-only limit in the other.
 *
 * TWO SIGNALS, because "it didn't happen" has two very different causes. The
 * callback reports both:
 *
 *   effect     — did the verb actually TAKE EFFECT (row created / modified /
 *                deleted / returned)? This is the oracle for the DENY direction:
 *                `addElement()` can accept a request and silently force the owner
 *                back to the caller (safe), and a handler could throw yet still
 *                have mutated the row (a leak). Only the row itself settles it.
 *   authDenied — did AUTHORIZATION specifically refuse (the 403 that
 *                checkSecureAccess() throws)? This is the oracle for the ALLOW
 *                direction. A model's own business rules routinely decline an edit
 *                — a licence check, an approval-status guard, a validation failure
 *                — and that is NOT an access-control regression. Judging
 *                over-restriction by "the row didn't change" would report every one
 *                of those as a finding.
 *
 * So: a scenario that should be denied fails only if the verb TOOK EFFECT; one that
 * should be allowed fails only if AUTHORIZATION was what stopped it.
 */

use Classes\BaseService;

class VerbAccessSweep
{
    /** @var TestContext */
    private $ctx;

    /** @var string the verb under test: add|save|delete|get */
    private $verb;

    private $mgr;
    private $sub;
    private $other;

    private $pass = 0;
    private $leak = 0;
    private $overDeny = 0;
    private $blocked = 0;
    private $untested = 0;
    private $skip = 0;
    private $undeclared = 0;
    private $inertDecl = 0;

    private $leaks = array();
    private $overDenies = array();
    private $blockeds = array();
    private $untestedModels = array();
    private $undeclareds = array();
    private $inertDecls = array();

    /** @var array employee id => REAL Users row id */
    private $userIds = array();

    /**
     * Scenario label => why it is not asserted for this verb. A relaxed scenario is
     * still RUN and still reported (as NOTE/relaxed counts), it just cannot fail the
     * build — for cases where the product's intended behaviour is genuinely broader
     * than the strict rule, and narrowing it would be a product decision rather than
     * a bug fix.
     */
    private $relaxed = array();

    /**
     * Findings grouped by the permission METHOD they exercise, so the report reads
     * as "here is what getUserAccess() allows and whether that held" rather than as
     * a stream of per-scenario lines. method => [ [result, class, perms], ... ].
     */
    private $rows = array();

    /** The permission method each scenario is really testing. */
    private static $SCENARIO_METHOD = array(
        'Admin'                => 'getAdminAccess',
        'Manager'              => 'getManagerAccess',
        'Manager(non-sub)'     => 'getManagerAccess',
        'Employee(self)'       => 'getUserOnlyMeAccess',
        'Employee(other)'      => 'getUserAccess',
        'Employee(supervisor)' => 'employeeDirectReportScopeAllows',
        'Anonymous'            => 'getAnonymousAccess',
        'Vertical'             => 'getUserAccess',
    );

    /** Human blurb per method, printed above its table. */
    private static $METHOD_BLURB = array(
        'getAdminAccess' => 'Admin user level — all rows. Guards against over-restriction.',
        'getManagerAccess' => 'Manager user level — record verbs must reach SUBORDINATE rows only (managerRecordScopeAllows).',
        'getUserOnlyMeAccess' => 'Employee user level, OWN rows only — ownership re-read from the database.',
        'getUserAccess' => 'Employee user level — applies to ALL rows, with no ownership scoping. Also covers the vertical phase (models with no employee owner).',
        'getAnonymousAccess' => 'No user level — any non-logged-in visitor. Nothing here should be reachable.',
        'employeeDirectReportScopeAllows' => 'Runtime gate, not a declared method: an Employee who directly supervises the owner. Read-only, capped by getManagerAccess.',
    );

    private function record($scenarioLabel, $model, array $perms, $result, $detail = '')
    {
        $method = isset(self::$SCENARIO_METHOD[$scenarioLabel])
            ? self::$SCENARIO_METHOD[$scenarioLabel]
            : $scenarioLabel;
        if (!isset($this->rows[$method])) {
            $this->rows[$method] = array();
        }
        // The column answers one question: does THIS method grant the verb under
        // test for this class? The full matrix is noise here — what a reader wants
        // beside a LEAK is "and it was not even supposed to be allowed".
        $granted = in_array($this->verb, $perms, true);
        $this->rows[$method][] = array(
            'result'  => $result,
            'class'   => $model,
            'allowed' => $granted ? 'allowed' : 'not allowed',
            'detail'  => $detail,
            'scen'    => $scenarioLabel,
        );
    }

    /**
     * Print one table: RESULT | CLASS | PERMISSIONS ALLOWED. Public so a test can
     * report a phase of its own (the anonymous declaration audit) in the same shape.
     */
    public static function printTable($heading, $blurb, array $rows, $permHeading = 'PERMISSION')
    {
        echo "\n" . str_repeat('=', 96) . "\n";
        echo $heading . "\n";
        if ($blurb !== '') {
            foreach (explode("\n", wordwrap($blurb, 92)) as $line) {
                echo '  ' . $line . "\n";
            }
        }
        echo str_repeat('=', 96) . "\n";
        printf("  %-11s %-34s %s\n", 'RESULT', 'CLASS', $permHeading);
        printf("  %-11s %-34s %s\n", str_repeat('-', 11), str_repeat('-', 34), str_repeat('-', 24));
        if (empty($rows)) {
            echo "  (nothing exercised)\n";
            return;
        }
        // Findings first — the reason anyone reads the table.
        usort($rows, function ($a, $b) {
            $aBad = $a['result'] === 'PASS' ? 1 : 0;
            $bBad = $b['result'] === 'PASS' ? 1 : 0;
            if ($aBad !== $bBad) { return $aBad - $bBad; }
            return strcmp($a['class'], $b['class']);
        });
        foreach ($rows as $r) {
            printf("  %-11s %-34s %s\n", $r['result'], $r['class'], $r['allowed']);
            if (!empty($r['detail'])) {
                printf("  %-11s   %s\n", '', $r['detail']);
            }
        }
        // RELAXED and UNTESTED are reported, not counted: one is a documented
        // non-assertion, the other a coverage gap. Neither is a finding.
        $bad = 0; $relaxed = 0; $untested = 0;
        foreach ($rows as $r) {
            if ($r['result'] === 'RELAXED') { $relaxed++; }
            elseif ($r['result'] === 'UNTESTED') { $untested++; }
            elseif ($r['result'] !== 'PASS') { $bad++; }
        }
        printf(
            "  -- %d classes, %d finding(s)%s%s\n",
            count($rows),
            $bad,
            $relaxed ? ", $relaxed relaxed" : '',
            $untested ? ", $untested untested" : ''
        );
    }

    /** @var int scenarios that would have been findings but are relaxed */
    private $relaxedHits = 0;
    private $relaxedDetail = array();

    public function __construct(TestContext $ctx, $verb)
    {
        $this->ctx = $ctx;
        $this->verb = $verb;
        list($this->mgr, $this->sub, $this->other) = $ctx->ensureManagerFixture();

        // Each actor needs a REAL Users row. BaseModel::getRoleBasedAccess() merges
        // the only-me matrix for a record the caller owns only after resolving the
        // employee from the database (getEmployeeByUserId), so an actor invented
        // purely in memory never gets its self-access grant and every own-row
        // scenario would look denied. Created once and reused: seeding a user per
        // scenario would be ~900 inserts.
        foreach (array($this->mgr, $this->sub, $this->other) as $emp) {
            $user = $ctx->actAsRealUser($emp, 'Employee');
            $this->userIds[$emp] = $user->id;
        }
    }

    /**
     * Was this throwable the ACCESS-CONTROL refusal (the 403 checkSecureAccess
     * raises), as opposed to a model's own validation/business rule? The verb tests
     * must not treat a licence check or an approval-status guard as an
     * authorization regression.
     */
    public static function isAuthDenial(\Throwable $e)
    {
        if ($e instanceof \Classes\Exception\IceHttpException) {
            return true;
        }
        return strpos($e->getMessage(), 'You are not allowed to') !== false;
    }

    public function describeFixture()
    {
        return sprintf(
            "Manager=%s, subordinate=%s (supervisor=%s), unrelated employee=%s",
            $this->mgr,
            $this->sub,
            $this->mgr,
            $this->other
        );
    }

    /**
     * Run every scenario for every model.
     *
     * @param callable $attempt function ($model, $ownerEmpId) : array
     *        Performs the verb against a row owned by $ownerEmpId (creating and
     *        cleaning up the fixture it needs) and returns
     *        array('effect' => bool, 'authDenied' => bool) — see the class docblock.
     * @param bool     $employeeOwnedOnly only sweep models with an employee owner
     *        column (all the row-scoping properties are meaningless without one).
     */
    public function run(callable $attempt, $employeeOwnedOnly = true, array $relaxed = array())
    {
        $this->relaxed = $relaxed;
        $map = $this->ctx->allModels();
        fwrite(STDOUT, "Verb under test: \"{$this->verb}\"\n");
        fwrite(STDOUT, $this->describeFixture() . "\n");
        fwrite(STDOUT, "Models under test: " . count($map) . "\n\n");
        foreach ($relaxed as $label => $reason) {
            fwrite(STDOUT, "  NOTE: scenario [$label] is NOT asserted here — $reason\n");
        }
        if ($relaxed) { fwrite(STDOUT, "\n"); }

        foreach ($map as $model => $fqcn) {
            try {
                $obj = new $fqcn();
            } catch (\Throwable $e) {
                $this->skip++; continue;
            }
            $table = $this->ctx->tableFor($obj);
            if (!$table || !$this->ctx->tableExists($table)) {
                $this->skip++; continue;
            }

            $ownerCol = $this->ctx->employeeOwnerColumn($obj, $table);
            if ($employeeOwnedOnly && !$ownerCol) {
                $this->skip++; continue;
            }

            // The Employees table is its own owner; mutating it through this sweep
            // would rewrite the fixture's identities mid-run.
            if ($table === 'Employees') {
                $this->skip++; continue;
            }

            // Expectations must come from a REAL loaded row, not a blank instance.
            // Several matrices are record-conditional — EmployeeExpense only grants
            // its owner "delete" while the claim is Pending or Rejected — and a blank
            // object has no status, so a blank-derived expectation would report every
            // correctly-permitted delete as an undeclared grant.
            $sample = $this->loadSampleRow($fqcn, $table, $ownerCol);
            $declared = $this->declaredMatrices($sample !== null ? $sample : $obj);
            $verdicts = array();

            // ---- 1. Admin on somebody else's row ---------------------------------
            // Conditional on the declaration: a model may deliberately withhold a
            // verb from Admin too (an append-only audit log withholding "save"), and
            // honouring that is correct, not a regression.
            $adminExpects = in_array($this->verb, $declared['admin'], true);
            $verdicts[] = $this->scenario(
                $model, 'Admin', $this->mgr, 'Admin', $this->other, $attempt,
                $adminExpects, 'BLOCKED', 'admin blocked from another employee\'s row',
                false, $declared['admin']
            );

            // ---- 2. Manager on their subordinate's row ---------------------------
            $mgrExpects = in_array($this->verb, $declared['manager'], true);
            $verdicts[] = $this->scenario(
                $model, 'Manager', $this->mgr, 'Manager', $this->sub, $attempt,
                $mgrExpects, 'OVER-DENY', 'manager denied their subordinate\'s row',
                false, $declared['manager']
            );

            // ---- 3. Manager on a NON-subordinate's row: must be denied -----------
            $verdicts[] = $this->scenario(
                $model, 'Manager(non-sub)', $this->mgr, 'Manager', $this->other, $attempt,
                false, 'LEAK', 'manager reached a non-subordinate\'s row', true, $declared['manager']
            );

            // ---- 4. Employee on their OWN row ------------------------------------
            $selfExpects = in_array($this->verb, $declared['self'], true);

            // A self LIST additionally requires the model to be a REGISTERED USER
            // TABLE. checkSecureAccess() refuses to authorise "get" from the only-me
            // matrix otherwise, because nothing would constrain which rows come back
            // (that is the fix behind "t=EmployeeSalary&a=get&employee=<own id>"
            // returning every salary). So a model declaring only-me "get" without
            // being registered has an INERT declaration — fail-closed, not a hole,
            // but misleading. Counted separately rather than reported as a denial.
            if ($this->verb === 'get' && $selfExpects
                && !in_array($model, BaseService::getInstance()->userTables)
            ) {
                $selfExpects = false;
                $this->inertDecl++;
                $this->inertDecls[] = $model;
            }
            $verdicts[] = $this->scenario(
                $model, 'Employee(self)', $this->sub, 'Employee', $this->sub, $attempt,
                $selfExpects, 'OVER-DENY', 'employee denied their own row',
                false, $declared['self']
            );

            // ---- 5. Employee on ANOTHER employee's row: must be denied -----------
            $verdicts[] = $this->scenario(
                $model, 'Employee(other)', $this->other, 'Employee', $this->sub, $attempt,
                false, 'LEAK', 'employee reached another employee\'s row', true, $declared['employee']
            );

            // ---- 6. Anonymous: must be denied ------------------------------------
            $verdicts[] = $this->scenario(
                $model, 'Anonymous', null, 'Anonymous', $this->sub, $attempt,
                false, 'LEAK', 'anonymous visitor reached a row', true, $declared['anonymous']
            );

            // ---- 7. Employee-level SUPERVISOR on a direct report's row -----------
            // employeeDirectReportScopeAllows() lets an Employee who directly
            // supervises the owner READ that row — "element" only, and only where the
            // model grants Manager "element". For every other verb the supervisor must
            // be denied, which is what makes this scenario worth running on all five:
            // it is the guard that the read-only widening never became a write path.
            $supExpects = ($this->verb === 'element')
                && in_array('element', $declared['manager'], true);
            $verdicts[] = $this->scenario(
                $model, 'Employee(supervisor)', $this->mgr, 'Employee', $this->sub, $attempt,
                $supExpects, 'OVER-DENY', 'supervisor denied their direct report\'s row',
                !$supExpects, $declared['manager']
            );

            $bad = array_filter($verdicts, function ($v) { return $v !== null; });
            if (empty($bad)) {
                $this->pass++;
            }
        }
    }

    /**
     * Reference / config / lookup data a plain Employee may READ: the data that
     * populates dropdowns and labels and is shared by everyone. THIS IS THE POLICY
     * for the vertical phase below — anything not on it is default-deny, so a new
     * model is exposed loudly until somebody classifies it here. Keep it
     * conservative: when in doubt leave a model OFF and let the sweep flag it.
     *
     * Deliberately NOT derived from each model's own ACL. Deriving it that way would
     * let a mis-declared model pass itself — RestAccessToken's inherited default
     * grants Employee "element" — which is exactly the class of bug this catches.
     */
    public static $PUBLIC_LOOKUP = array(
        'Country', 'Nationality', 'Province', 'Language', 'CurrencyType', 'Timezone',
        'JobTitle', 'PayGrade', 'EmploymentStatus', 'EmployementType', 'EducationLevel',
        'Skill', 'Certification', 'Education',
        'LeaveType', 'LeavePeriod', 'WorkDay', 'HoliDay',
        'OvertimeCategory', 'ExpensesCategory', 'ExpensesPaymentMethod',
        'ImmigrationStatus', 'Deduction', 'DeductionGroup', 'PayFrequency',
        'Industry', 'Benifit', 'CalculationHook', 'CustomField', 'Document',
        'CompanyStructure', 'ReviewTemplate', 'Course', 'LmsCourse', 'LmsLesson',
        'Client', 'Project', 'TravelProject',
    );

    /**
     * Additional models a plain Employee may LIST (but not element-read) through the
     * generic path. Separate from $PUBLIC_LOOKUP because "reference data everybody
     * may read by id" and "an employee-facing screen that lists rows" are different
     * questions: these back real employee screens whose lists are fetched generically,
     * and each was reviewed when its `array("get")` grant was written.
     *
     *   CompanyDocument / CompanyLoan  the employee Documents tab, and the loan-type
     *                                  name lookup on the Loans screen.
     *   TrainingSession / …WithCourse  the open sign-up session list.
     *   HiringPipeline                 already reviewed public for ANONYMOUS
     *                                  visitors, so an employee listing it is
     *                                  strictly less exposure.
     *
     * Applies to the "get" verb only — element/add/save/delete keep the stricter rule.
     */
    public static $LIST_PUBLIC = array(
        'CompanyDocument', 'CompanyLoan', 'TrainingSession', 'TrainingSessionWithCourse',
        'HiringPipeline',
    );

    private $vPass = 0;
    private $vLeak = 0;
    private $vUntested = 0;
    private $vSkip = 0;
    private $vLeaks = array();
    private $vUntestedModels = array();
    private $verticalRan = false;
    private $verticalRule = '';

    /**
     * VERTICAL phase — the same verb against models with NO employee owner: the
     * lookup, config and admin/system tables (Audit, RestAccessToken, SystemData,
     * Migration, EmailLog, backups, candidate data...). The row-scoping scenarios
     * above are meaningless there, but the question "may a plain Employee do this
     * at all?" is not, and for the write verbs it is the sharper question of the two.
     *
     * The rule depends on the verb, because "shared reference data" and "data an
     * employee may CHANGE" are different sets:
     *
     *   get / element      allowed only for $PUBLIC_LOOKUP models — everyone needs
     *                      to read country and job-title lists.
     *   add / save / delete NEVER allowed, allowlist or not. A plain Employee has no
     *                      business creating, editing or deleting a lookup, a config
     *                      row or a system table — including the ones they may read.
     */
    public function runVertical(callable $attempt)
    {
        $this->verticalRan = true;
        $isRead = in_array($this->verb, array('get', 'element'), true);
        $readable = self::$PUBLIC_LOOKUP;
        if ($this->verb === 'get') {
            $readable = array_merge($readable, self::$LIST_PUBLIC);
        }
        $lookup = array_flip($readable);

        $this->verticalRule = $isRead
            ? 'readable only if on the reviewed public-lookup allowlist'
                . ($this->verb === 'get' ? ' (+ the reviewed employee-list allowlist)' : '')
            : 'never writable by a plain Employee, allowlist or not';

        foreach ($this->ctx->allModels() as $model => $fqcn) {
            try {
                $obj = new $fqcn();
            } catch (\Throwable $e) {
                $this->vSkip++; continue;
            }
            $table = $this->ctx->tableFor($obj);
            if (!$table || !$this->ctx->tableExists($table)) {
                $this->vSkip++; continue;
            }
            if ($this->ctx->employeeOwnerColumn($obj, $table)) {
                $this->vSkip++; continue;
            }

            $mayRead = $isRead && isset($lookup[$model]);

            // The declared Employee matrix for this non-owned model, so the table
            // shows whether the grant exists as well as whether it held.
            $declaredEmployee = array();
            try {
                $declaredEmployee = (array) $obj->getRoleBasedAccess('Employee', null);
            } catch (\Throwable $e) {
            }

            $this->ctx->actAs($this->other, 'Employee', $this->userIds[$this->other]);
            try {
                $result = $attempt($model, null);
            } catch (\Throwable $e) {
                $this->vUntested++; $this->vUntestedModels[] = $model;
                $this->record('Vertical', $model, $declaredEmployee, 'UNTESTED', substr($e->getMessage(), 0, 60));
                continue;
            }

            $tookEffect = !empty($result['effect']);
            if ($tookEffect && !$mayRead) {
                $this->vLeak++; $this->vLeaks[] = $model;
                $this->record(
                    'Vertical', $model, $declaredEmployee, 'LEAK-VERT',
                    sprintf('plain Employee did "%s" on %s (no employee owner, not reviewed public)', $this->verb, $table)
                );
            } elseif ($mayRead) {
                $this->vPass++;
                $this->record('Vertical', $model, $declaredEmployee, 'PASS', 'reviewed public — readable by design');
            } else {
                $this->vPass++;
                $this->record('Vertical', $model, $declaredEmployee, 'PASS');
            }
        }
    }

    /**
     * A throwaway row of this model, loaded into the model object, so
     * record-conditional access methods see a realistic record. Removed immediately;
     * only the in-memory object is kept. Returns null when the model cannot be seeded.
     */
    private function loadSampleRow($fqcn, $table, $ownerCol)
    {
        if (!$ownerCol) { return null; }
        try {
            $id = $this->ctx->seedRow($table, array($ownerCol => $this->sub));
        } catch (\Throwable $e) {
            return null;
        }
        $sample = new $fqcn();
        try {
            $sample->Load('id = ?', array($id));
        } catch (\Throwable $e) {
            $sample = null;
        }
        $this->ctx->query("DELETE FROM `$table` WHERE id = ?", array($id));

        return ($sample !== null && !empty($sample->id)) ? $sample : null;
    }

    /**
     * The matrices that decide what SHOULD be allowed:
     *  - manager: the Manager role grant.
     *  - self:    what an Employee may do to their OWN row — the union
     *             checkSecureAccess() effectively applies (role grant + only-me).
     */
    private function declaredMatrices($obj)
    {
        $admin = array();
        $manager = array();
        $employee = array();
        $onlyMe = array();
        try { $admin = (array) $obj->getRoleBasedAccess('Admin', null); } catch (\Throwable $e) {
        }
        try { $manager = (array) $obj->getRoleBasedAccess('Manager', null); } catch (\Throwable $e) {
        }
        try { $employee = (array) $obj->getRoleBasedAccess('Employee', null); } catch (\Throwable $e) {
        }
        try { $onlyMe = (array) $obj->getUserOnlyMeAccess(); } catch (\Throwable $e) {
        }

        $anonymous = array();
        try { $anonymous = (array) $obj->getRoleBasedAccess('Anonymous', null); } catch (\Throwable $e) {
        }

        return array(
            'admin'     => $admin,
            'manager'   => $manager,
            'employee'  => $employee,
            'anonymous' => $anonymous,
            'self'      => array_unique(array_merge($employee, $onlyMe)),
        );
    }

    /**
     * Run one scenario. Returns null when it behaved as expected, else the finding
     * label (already reported and counted).
     */
    private function scenario($model, $label, $actorEmp, $level, $ownerEmp, callable $attempt, $expectAllowed, $badLabel, $why, $isScopeScenario = false, array $perms = array())
    {
        if ($level === 'Anonymous') {
            $this->ctx->actAsAnonymous();
        } else {
            $userId = isset($this->userIds[$actorEmp]) ? $this->userIds[$actorEmp] : null;
            $this->ctx->actAs($actorEmp, $level, $userId);
        }

        try {
            $result = $attempt($model, $ownerEmp);
        } catch (\Throwable $e) {
            // A throw from the fixture side (not the permission check) means the
            // scenario could not be evaluated — never silently a pass.
            $this->untested++; $this->untestedModels[] = $model . " [$label]";
            $this->record($label, $model, $perms, 'UNTESTED', substr($e->getMessage(), 0, 60));
            return 'UNTESTED';
        }

        $tookEffect = !empty($result['effect']);
        $authDenied = !empty($result['authDenied']);

        // Deny direction: only the row settles it. Allow direction: only an
        // authorization refusal counts — a model's own business rules declining the
        // operation is not an access-control finding.
        $isFinding = $expectAllowed ? $authDenied : $tookEffect;
        if (!$isFinding) {
            $this->record($label, $model, $perms, 'PASS');
            return null;
        }

        if (isset($this->relaxed[$label]) || isset($this->relaxed["$model [$label]"])) {
            $this->relaxedHits++;
            $this->relaxedDetail[] = "$model [$label]";
            $this->record($label, $model, $perms, 'RELAXED', 'not asserted for this verb — see NOTE');
            return null;
        }

        if ($expectAllowed) {
            // Authorization refused something the matrix declares.
            $this->record($label, $model, $perms, $badLabel, $why);
            if ($badLabel === 'BLOCKED') {
                $this->blocked++; $this->blockeds[] = "$model [$label]";
            } else {
                $this->overDeny++; $this->overDenies[] = "$model [$label]";
            }
            return $badLabel;
        }

        // The verb took effect when it should not have. Which finding that is depends
        // on WHY it should not have: reaching outside the actor's scope is a leak,
        // whereas acting on one's own row without a declaration is enforcement being
        // broader than the matrix. Reporting both as "LEAK" (with the allow-direction
        // message) would misdescribe half of them.
        if ($isScopeScenario) {
            $this->record($label, $model, $perms, 'LEAK', $why);
            $this->leak++; $this->leaks[] = "$model [$label]";
            return 'LEAK';
        }

        $this->record(
            $label, $model, $perms, 'UNDECLARED',
            sprintf('runtime allowed "%s", which this matrix does not declare', $this->verb)
        );
        $this->undeclared++; $this->undeclareds[] = "$model [$label]";

        return 'UNDECLARED';
    }

    /** Print one table per permission method, then the totals. Returns the exit code. */
    public function report()
    {
        $order = array(
            'getAdminAccess',
            'getManagerAccess',
            'getUserAccess',
            'getUserOnlyMeAccess',
            'getAnonymousAccess',
            'employeeDirectReportScopeAllows',
        );
        foreach ($this->rows as $method => $_) {
            if (!in_array($method, $order, true)) { $order[] = $method; }
        }

        foreach ($order as $method) {
            if (empty($this->rows[$method])) { continue; }
            $blurb = isset(self::$METHOD_BLURB[$method]) ? self::$METHOD_BLURB[$method] : '';
            if ($method === 'getUserAccess' && $this->verticalRan) {
                $blurb .= ' Vertical rule: ' . $this->verticalRule . '.';
            }
            self::printTable(
                sprintf('%s()  —  verb "%s"', $method, $this->verb),
                $blurb,
                $this->rows[$method],
                sprintf('"%s" ALLOWED?', $this->verb)
            );
        }

        echo "\n" . str_repeat('=', 96) . "\n";
        printf(
            "TOTALS  verb=%s  classes-clean=%d  LEAK=%d  UNDECLARED=%d  OVER-DENY=%d  BLOCKED=%d  UNTESTED=%d  SKIP=%d\n",
            $this->verb, $this->pass, $this->leak, $this->undeclared, $this->overDeny,
            $this->blocked, $this->untested, $this->skip
        );
        if ($this->verticalRan) {
            printf(
                "VERTICAL (models with no employee owner)  PASS=%d  LEAK=%d  UNTESTED=%d  SKIP=%d\n",
                $this->vPass, $this->vLeak, $this->vUntested, $this->vSkip
            );
        }
        if ($this->inertDecls) {
            echo "INERT DECLARATIONS (only-me \"get\" on a model that is not a registered user\n"
                . "table — the list can never be authorised, so the grant does nothing): "
                . implode(', ', $this->inertDecls) . "\n";
        }
        if ($this->relaxedHits) {
            printf(
                "RELAXED: %d scenario(s) broader than the strict rule, not asserted — see the NOTE above.\n",
                $this->relaxedHits
            );
        }
        echo str_repeat('=', 96) . "\n";

        return ($this->leak + $this->undeclared + $this->overDeny + $this->blocked + $this->vLeak) > 0 ? 1 : 0;
    }
}
