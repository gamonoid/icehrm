<?php

namespace DemoModeAdmin;

use Classes\BaseService;
use Classes\IceResponse;
use DemoModeAdmin\Common\Model\DemoDataEntry;
use Employees\Common\Model\Employee;
use Employees\Common\Model\EmploymentStatus;
use Jobs\Common\Model\JobTitle;
use Company\Common\Model\CompanyStructure;
use Attendance\Common\Model\Attendance;
use TimeSheets\Common\Model\EmployeeTimeSheet;
use TimeSheets\Common\Model\EmployeeTimeEntry;
use Payroll\Common\Model\Payroll;
use Payroll\Common\Model\PayrollData;
use Payroll\Common\Model\PayFrequency;
use Payroll\Common\Model\PayrollColumn;
use Projects\Common\Model\Project;
use Projects\Common\Model\Client;
use Projects\Common\Model\EmployeeProject;
use Employees\Common\Model\EmployeeCareer;
use Qualifications\Common\Model\Education;
use Qualifications\Common\Model\EmployeeEducation;
use Qualifications\Common\Model\Certification;
use Qualifications\Common\Model\EmployeeCertification;
use Qualifications\Common\Model\Language;
use Qualifications\Common\Model\EmployeeLanguage;
use Dependents\Common\Model\EmployeeDependent;
use EmergencyContacts\Common\Model\EmergencyContact;
use Users\Common\Model\User;
use Classes\PasswordManager;

class DemoDataService
{
    private static $clientNames = [
        'Acme Corporation', 'TechStart Inc', 'Global Solutions Ltd', 'Blue Ocean Ventures',
        'Summit Industries', 'Pinnacle Group', 'Nova Systems', 'Horizon Partners',
        'Atlas Holdings', 'Phoenix Enterprises'
    ];

    private static $projectNames = [
        'Website Redesign', 'Mobile App Development', 'CRM Implementation', 'Data Migration',
        'Cloud Infrastructure', 'Security Audit', 'ERP Integration', 'API Development',
        'Marketing Campaign', 'Product Launch', 'Brand Refresh', 'Customer Portal',
        'Inventory System', 'Analytics Dashboard', 'Automation Project'
    ];

    private static $firstNames = [
        'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda',
        'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica',
        'Thomas', 'Sarah', 'Charles', 'Karen', 'Christopher', 'Lisa', 'Daniel', 'Nancy',
        'Matthew', 'Betty', 'Anthony', 'Margaret', 'Mark', 'Sandra', 'Donald', 'Ashley',
        'Steven', 'Kimberly', 'Paul', 'Emily', 'Andrew', 'Donna', 'Joshua', 'Michelle'
    ];

    private static $lastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
        'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
        'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
        'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker',
        'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores'
    ];

    private static $cities = [
        'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia',
        'San Antonio', 'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville',
        'Fort Worth', 'Columbus', 'Charlotte', 'San Francisco', 'Indianapolis', 'Seattle'
    ];

    private static $streets = [
        'Main St', 'Oak Ave', 'Maple Dr', 'Cedar Ln', 'Pine Rd', 'Elm St',
        'Washington Ave', 'Park Blvd', 'Lake Dr', 'Hill St', 'River Rd', 'Forest Ave'
    ];

    private static $universities = [
        'Massachusetts Institute of Technology', 'Stanford University', 'Harvard University',
        'California Institute of Technology', 'University of Chicago', 'Princeton University',
        'Columbia University', 'Yale University', 'Cornell University', 'Duke University',
        'University of Michigan', 'Northwestern University', 'Carnegie Mellon University',
        'University of California Berkeley', 'University of Pennsylvania', 'UCLA',
        'Georgia Institute of Technology', 'University of Texas at Austin'
    ];

    private static $careerDetails = [
        'Led team projects and initiatives', 'Managed client relationships',
        'Developed strategic solutions', 'Improved operational efficiency',
        'Collaborated with cross-functional teams', 'Delivered high-impact results',
        'Mentored junior team members', 'Streamlined business processes'
    ];

    private static $relationships = ['Spouse', 'Child', 'Parent', 'Other'];

    private static $emergencyRelationships = [
        'Spouse', 'Parent', 'Sibling', 'Friend', 'Partner', 'Relative'
    ];

    private static $languageProficiencyLevels = ['Elementary', 'Limited', 'Professional', 'Full Professional', 'Native'];

    /**
     * Check if we should show the demo mode prompt to the user
     * Conditions: Only 1 employee with first_name "IceHrm" and last_name "Employee"
     */
    public static function shouldShowDemoPrompt(): IceResponse
    {
        try {
            $employee = new Employee();
            $employees = $employee->Find('status = ?', ['Active']);

            // Check if there's only one employee
            if (count($employees) !== 1) {
                return new IceResponse(IceResponse::SUCCESS, ['show' => false, 'reason' => 'Multiple employees exist']);
            }

            $singleEmployee = $employees[0];

            // Check if it's the default IceHrm Employee
            if ($singleEmployee->first_name === 'IceHrm' && $singleEmployee->last_name === 'Employee') {
                // Also check if demo mode is not already enabled
                $isDemoMode = DemoModeTracker::isDemoModeEnabled();
                if ($isDemoMode) {
                    return new IceResponse(IceResponse::SUCCESS, ['show' => false, 'reason' => 'Demo mode already enabled']);
                }

                // Check if we already have demo data
                $stats = self::getStats();
                if ($stats->getObject()['employee'] > 0) {
                    return new IceResponse(IceResponse::SUCCESS, ['show' => false, 'reason' => 'Demo data already exists']);
                }

                return new IceResponse(IceResponse::SUCCESS, ['show' => true]);
            }

            return new IceResponse(IceResponse::SUCCESS, ['show' => false, 'reason' => 'Not default employee']);
        } catch (\Exception $e) {
            return new IceResponse(IceResponse::ERROR, 'Error checking demo prompt: ' . $e->getMessage());
        }
    }

    /**
     * Generate all demo data at once
     * - Enable demo mode
     * - Create 20 employees
     * - Create 2 years of attendance
     * - Create leave requests
     * - Create 2 years of timesheets
     */
    public static function generateAllDemoData(bool $futureLeaveOnly = false): IceResponse
    {
        $results = [
            'demoModeEnabled' => false,
            'employees' => 0,
            'users' => 0,
            'employeeDetails' => [
                'career' => 0,
                'education' => 0,
                'certifications' => 0,
                'languages' => 0,
                'dependents' => 0,
                'emergencyContacts' => 0
            ],
            'clients' => 0,
            'projects' => 0,
            'employeeProjects' => 0,
            'attendance' => 0,
            'leave' => 0,
            'leaveDays' => 0,
            'timesheets' => 0,
            'timesheetEntries' => 0,
            'limitReached' => false,
            'maxEntries' => DemoModeTracker::MAX_DEMO_ENTRIES,
            'errors' => []
        ];

        try {
            // Check if limit is already reached before starting
            if (DemoModeTracker::isLimitReached()) {
                $results['limitReached'] = true;
                $results['errors'][] = 'Demo data limit of ' . DemoModeTracker::MAX_DEMO_ENTRIES . ' entries has been reached.';
                return new IceResponse(IceResponse::ERROR, $results);
            }

            // Step 1: Enable demo mode
            $enabled = DemoModeTracker::enableDemoMode();
            $results['demoModeEnabled'] = $enabled;
            if (!$enabled) {
                $results['errors'][] = 'Failed to enable demo mode';
            }

            // Step 2: Generate 15 employees with user accounts (with details: career, education, etc.)
            $employeeIds = [];
            $employeeResult = self::generateEmployees(15);
            if ($employeeResult->getStatus() === IceResponse::SUCCESS) {
                $results['employees'] = $employeeResult->getObject()['created'] ?? 0;
                $results['users'] = $employeeResult->getObject()['usersCreated'] ?? 0;
                $employeeIds = $employeeResult->getObject()['employeeIds'] ?? [];
                $results['employeeDetails'] = $employeeResult->getObject()['employeeDetails'] ?? $results['employeeDetails'];
            } else {
                $results['errors'][] = 'Employee generation: ' . $employeeResult->getObject();
            }

            // Step 3: Generate 5 clients
            $clientIds = [];
            $clientResult = self::generateClients(5);
            if ($clientResult->getStatus() === IceResponse::SUCCESS) {
                $results['clients'] = $clientResult->getObject()['created'] ?? 0;
                $clientIds = $clientResult->getObject()['clientIds'] ?? [];
            } else {
                $results['errors'][] = 'Client generation: ' . $clientResult->getObject();
            }

            // Step 4: Generate 5 projects (pass employee and client IDs directly)
            $projectIds = [];
            $projectResult = self::generateProjectsWithIds(5, $employeeIds, $clientIds);
            if ($projectResult->getStatus() === IceResponse::SUCCESS) {
                $results['projects'] = $projectResult->getObject()['created'] ?? 0;
                $results['employeeProjects'] = $projectResult->getObject()['employeeProjects'] ?? 0;
                $projectIds = $projectResult->getObject()['projectIds'] ?? [];
            } else {
                $results['errors'][] = 'Project generation: ' . $projectResult->getObject();
            }

            // Step 5: Generate 6 months of attendance (max 2000 records)
            $attendanceResult = self::generateAttendanceWithIds(180, 2000, $employeeIds);
            if ($attendanceResult->getStatus() === IceResponse::SUCCESS) {
                $results['attendance'] = $attendanceResult->getObject()['created'] ?? 0;
            } else {
                $results['errors'][] = 'Attendance generation: ' . $attendanceResult->getObject();
            }

            // Step 6: Generate leave requests with leave days (max 150 records)
            $leaveResult = self::generateLeaveRequestsWithIds(10, 150, $employeeIds, $futureLeaveOnly);
            if ($leaveResult->getStatus() === IceResponse::SUCCESS) {
                $results['leave'] = $leaveResult->getObject()['created'] ?? 0;
                $results['leaveDays'] = $leaveResult->getObject()['leaveDays'] ?? 0;
            } else {
                $results['errors'][] = 'Leave generation: ' . $leaveResult->getObject();
            }

            // Step 7: Generate 6 months of timesheets (max 200 timesheets)
            $timesheetResult = self::generateTimesheetsWithIds(26, 200, $employeeIds, $projectIds);
            if ($timesheetResult->getStatus() === IceResponse::SUCCESS) {
                $results['timesheets'] = $timesheetResult->getObject()['timesheets'] ?? 0;
                $results['timesheetEntries'] = $timesheetResult->getObject()['entries'] ?? 0;
            } else {
                $results['errors'][] = 'Timesheet generation: ' . $timesheetResult->getObject();
            }

            // Check if limit was reached during generation
            if (DemoModeTracker::isLimitReached()) {
                $results['limitReached'] = true;
            }

            return new IceResponse(IceResponse::SUCCESS, $results);
        } catch (\Exception $e) {
            $results['errors'][] = 'Exception: ' . $e->getMessage();
            return new IceResponse(IceResponse::ERROR, $results);
        }
    }

    /**
     * Generate mock employees with supervisor assignments and user accounts
     * Hierarchy: IceHrm Employee (ID 1) -> Managers -> Regular Employees
     */
    public static function generateEmployees(int $count): IceResponse
    {
        $createdIds = [];
        $createdUserIds = [];
        $errors = [];

        // Get lookup data
        $departments = self::getAvailableDepartments();
        $jobTitles = self::getAvailableJobTitles();
        $employmentStatuses = self::getAvailableEmploymentStatuses();

        if (empty($departments)) {
            return new IceResponse(IceResponse::ERROR, 'No departments found. Please create at least one department first.');
        }
        if (empty($jobTitles)) {
            return new IceResponse(IceResponse::ERROR, 'No job titles found. Please create at least one job title first.');
        }
        if (empty($employmentStatuses)) {
            return new IceResponse(IceResponse::ERROR, 'No employment statuses found. Please create at least one employment status first.');
        }

        $employeeDetails = [
            'career' => 0,
            'education' => 0,
            'certifications' => 0,
            'languages' => 0,
            'dependents' => 0,
            'emergencyContacts' => 0
        ];

        // Check if IceHrm Employee (ID 1) exists - managers will report to this employee
        $topLevelSupervisorId = null;
        $topEmployee = new Employee();
        $topEmployee->Load('id = ?', [1]);
        if ($topEmployee->id) {
            $topLevelSupervisorId = $topEmployee->id;
        }

        // Calculate number of supervisors (roughly 1 supervisor per 4-5 employees, minimum 1)
        $supervisorCount = max(1, (int) ceil($count / 5));
        $regularCount = $count - $supervisorCount;

        $supervisorIds = [];

        // Step 1: Create managers first (they report to IceHrm Employee if exists)
        for ($i = 0; $i < $supervisorCount; $i++) {
            try {
                $employee = self::createMockEmployee($departments, $jobTitles, $employmentStatuses, $topLevelSupervisorId);
                if ($employee && $employee->id) {
                    self::trackDemoEntry('Employees', $employee->id, 'employee');
                    $createdIds[] = $employee->id;
                    $supervisorIds[] = $employee->id;

                    // Create user account for manager with "Manager" user_level
                    $user = self::createUserForEmployee($employee, 'Manager');
                    if ($user && $user->id) {
                        self::trackDemoEntryDirect('Users', $user->id, 'user');
                        $createdUserIds[] = $user->id;
                    }

                    // Generate employee details (career, education, etc.)
                    $details = self::generateEmployeeDetails($employee->id, $departments, $jobTitles, $employmentStatuses);
                    $employeeDetails['career'] += $details['career'];
                    $employeeDetails['education'] += $details['education'];
                    $employeeDetails['certifications'] += $details['certifications'];
                    $employeeDetails['languages'] += $details['languages'];
                    $employeeDetails['dependents'] += $details['dependents'];
                    $employeeDetails['emergencyContacts'] += $details['emergencyContacts'];
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        // Step 2: Create regular employees and distribute them evenly among managers
        for ($i = 0; $i < $regularCount; $i++) {
            try {
                // Evenly distribute: assign supervisor using round-robin
                $supervisorId = !empty($supervisorIds) ? $supervisorIds[$i % count($supervisorIds)] : null;

                $employee = self::createMockEmployee($departments, $jobTitles, $employmentStatuses, $supervisorId);
                if ($employee && $employee->id) {
                    self::trackDemoEntry('Employees', $employee->id, 'employee');
                    $createdIds[] = $employee->id;

                    // Create user account for regular employee with "Employee" user_level
                    $user = self::createUserForEmployee($employee, 'Employee');
                    if ($user && $user->id) {
                        self::trackDemoEntryDirect('Users', $user->id, 'user');
                        $createdUserIds[] = $user->id;
                    }

                    // Generate employee details (career, education, etc.)
                    $details = self::generateEmployeeDetails($employee->id, $departments, $jobTitles, $employmentStatuses);
                    $employeeDetails['career'] += $details['career'];
                    $employeeDetails['education'] += $details['education'];
                    $employeeDetails['certifications'] += $details['certifications'];
                    $employeeDetails['languages'] += $details['languages'];
                    $employeeDetails['dependents'] += $details['dependents'];
                    $employeeDetails['emergencyContacts'] += $details['emergencyContacts'];
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'created' => count($createdIds),
                'employeeIds' => $createdIds,
                'supervisorIds' => $supervisorIds,
                'usersCreated' => count($createdUserIds),
                'employeeDetails' => $employeeDetails,
                'errors' => $errors
            ]
        );
    }

    /**
     * Create today's attendance for a random slice of ACTIVE employees.
     *
     * Unlike generateAttendance (which spans days for demo employees only), this
     * targets the whole active workforce so "who's in today" style views look
     * realistic. Employees who already have a record for today are skipped.
     *
     * @param int $percentage 1–100, the share of active employees to clock in today
     */
    public static function generateTodayAttendance(int $percentage = 100): IceResponse
    {
        $percentage = max(1, min(100, $percentage));

        $employee = new Employee();
        $activeEmployees = $employee->Find('status = ?', ['Active']);
        if (empty($activeEmployees)) {
            return new IceResponse(IceResponse::ERROR, 'No active employees found.');
        }

        $employeeIds = array_map(function ($e) {
            return $e->id;
        }, $activeEmployees);

        // Randomly pick the requested share (round to the nearest whole employee,
        // at least one so the action never silently does nothing).
        shuffle($employeeIds);
        $targetCount = (int) max(1, round(count($employeeIds) * $percentage / 100));
        $selected = array_slice($employeeIds, 0, $targetCount);

        $today = new \DateTime();
        $todayStr = $today->format('Y-m-d');

        $createdCount = 0;
        $skipped = 0;
        $errors = [];

        foreach ($selected as $employeeId) {
            if (DemoModeTracker::isLimitReached()) {
                break;
            }

            // Skip anyone who already has an attendance record for today.
            if (self::hasAttendanceOn($employeeId, $todayStr)) {
                $skipped++;
                continue;
            }

            try {
                $attendance = self::createMockAttendance($employeeId, $today);
                if ($attendance && $attendance->id) {
                    self::trackDemoEntry('Attendance', $attendance->id, 'attendance');
                    $createdCount++;
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'created' => $createdCount,
                'skipped' => $skipped,
                'targeted' => count($selected),
                'totalActive' => count($employeeIds),
                'errors' => $errors,
            ]
        );
    }

    /**
     * Generate dependents (1–3) and emergency contacts (1–2) for a selected
     * number of randomly chosen active employees.
     */
    public static function generateFamilyData(int $employeeCount = 10): IceResponse
    {
        $employee = new Employee();
        $activeEmployees = $employee->Find('status = ?', ['Active']);
        if (empty($activeEmployees)) {
            return new IceResponse(IceResponse::ERROR, 'No active employees found.');
        }

        $employeeIds = array_map(function ($e) {
            return $e->id;
        }, $activeEmployees);
        shuffle($employeeIds);
        $selected = array_slice($employeeIds, 0, max(1, $employeeCount));

        $dependents = 0;
        $contacts = 0;
        $errors = [];

        foreach ($selected as $employeeId) {
            if (DemoModeTracker::isLimitReached()) {
                break;
            }
            try {
                $depCount = rand(1, 3);
                for ($i = 0; $i < $depCount; $i++) {
                    $dependent = self::createMockDependent($employeeId);
                    if ($dependent && $dependent->id) {
                        self::trackDemoEntry('EmployeeDependents', $dependent->id, 'employee_data');
                        $dependents++;
                    }
                }
                $contactCount = rand(1, 2);
                for ($i = 0; $i < $contactCount; $i++) {
                    $contact = self::createMockEmergencyContact($employeeId);
                    if ($contact && $contact->id) {
                        self::trackDemoEntry('EmergencyContacts', $contact->id, 'employee_data');
                        $contacts++;
                    }
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'employees' => count($selected),
                'dependents' => $dependents,
                'emergencyContacts' => $contacts,
                'errors' => $errors,
            ]
        );
    }

    /**
     * Generate random teams (team extension) with members drawn from the active
     * workforce. Each team gets a random lead (added as its Leader member) plus
     * the requested number of regular members.
     */
    public static function generateTeams(int $teamCount = 5, int $membersPerTeam = 6): IceResponse
    {
        if (!class_exists('TeamAdmin\\Common\\Model\\Team')) {
            return new IceResponse(IceResponse::ERROR, 'The team extension is not installed.');
        }

        $employee = new Employee();
        $activeEmployees = $employee->Find('status = ?', ['Active']);
        if (empty($activeEmployees)) {
            return new IceResponse(IceResponse::ERROR, 'No active employees found.');
        }
        $employeeIds = array_map(function ($e) {
            return $e->id;
        }, $activeEmployees);

        $departments = self::getAvailableDepartments();

        $teamNames = [
            'Platform Engineering', 'Customer Success', 'Growth Marketing', 'Core Product',
            'Quality Assurance', 'People Operations', 'Data & Analytics', 'Mobile Squad',
            'Infrastructure', 'Design Systems', 'Sales Enablement', 'Support Heroes',
            'Innovation Lab', 'Security Champions', 'Release Squad', 'Partner Integrations',
            'Field Operations', 'Revenue Ops', 'Brand Studio', 'Automation Guild',
        ];
        shuffle($teamNames);
        $teamColors = ['#1677ff', '#52c41a', '#fa8c16', '#eb2f96', '#722ed1', '#13c2c2', '#f5222d', '#a0d911'];

        $teamsCreated = 0;
        $membersCreated = 0;
        $errors = [];

        $teamClass = 'TeamAdmin\\Common\\Model\\Team';
        $memberClass = 'TeamAdmin\\Common\\Model\\TeamMember';

        for ($t = 0; $t < $teamCount; $t++) {
            if (DemoModeTracker::isLimitReached()) {
                break;
            }
            try {
                $name = $teamNames[$t % count($teamNames)] . ($t >= count($teamNames) ? ' ' . ($t + 1) : '');
                $leadId = $employeeIds[array_rand($employeeIds)];

                /** @var \Model\BaseModel $team */
                $team = new $teamClass();
                $team->name = $name;
                $team->description = 'Demo team: ' . $name;
                $team->lead = $leadId;
                $team->department = !empty($departments) ? $departments[array_rand($departments)] : null;
                $team->status = 'Active';
                $team->color = $teamColors[array_rand($teamColors)];
                $team->created = date('Y-m-d H:i:s');
                $team->updated = date('Y-m-d H:i:s');
                if (!$team->Save()) {
                    $errors[] = 'Could not save team ' . $name;
                    continue;
                }
                self::trackDemoEntry('EmployeeTeams', $team->id, 'team');
                $teamsCreated++;

                // Lead joins as Leader, then random unique members join as Member.
                $pool = $employeeIds;
                shuffle($pool);
                $memberIds = array_slice(array_values(array_unique(array_merge([$leadId], $pool))), 0, max(1, $membersPerTeam));
                foreach ($memberIds as $idx => $memberId) {
                    /** @var \Model\BaseModel $member */
                    $member = new $memberClass();
                    $member->team = $team->id;
                    $member->member = $memberId;
                    $member->role = ($memberId == $leadId) ? 'Leader' : 'Member';
                    $member->joined_date = date('Y-m-d', strtotime('-' . rand(0, 720) . ' days'));
                    $member->status = 'Active';
                    $member->created = date('Y-m-d H:i:s');
                    $member->updated = date('Y-m-d H:i:s');
                    if ($member->Save()) {
                        self::trackDemoEntry('EmployeeTeamMembers', $member->id, 'team_member');
                        $membersCreated++;
                    }
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'teams' => $teamsCreated,
                'members' => $membersCreated,
                'errors' => $errors,
            ]
        );
    }

    /**
     * Generate sample Task Lists (tasks extension) with realistic titles,
     * descriptions and checklist tasks assigned to different employees. Each
     * list is backed by an editor-extension document (Content) whose `checklist`
     * blocks carry the per-employee assignments — exactly the structure the
     * editor UI produces and TaskList::findAssignees() parses (an item's
     * `user_name` holds the "First Last ( id:NN )" token).
     */
    public static function generateTaskLists(int $count = 8): IceResponse
    {
        $taskListClass = 'TasksAdmin\\Common\\Model\\TaskList';
        $assignmentClass = 'TasksAdmin\\Common\\Model\\TaskListAssignment';
        $contentClass = 'EditorUser\\Common\\Model\\Content';

        if (!class_exists($taskListClass)) {
            return new IceResponse(IceResponse::ERROR, 'The task lists extension is not installed.');
        }
        if (!class_exists($contentClass)) {
            return new IceResponse(IceResponse::ERROR, 'The editor extension is not installed.');
        }

        $employee = new Employee();
        $activeEmployees = $employee->Find('status = ?', ['Active']);
        if (empty($activeEmployees)) {
            return new IceResponse(IceResponse::ERROR, 'No active employees found.');
        }

        // Assignee tokens use the same "First Last ( id:NN )" format the editor
        // produces (EditorService::getEmployeeNamesAndImages).
        $empPool = array_map(function ($e) {
            return [
                'id' => (int) $e->id,
                'name' => trim($e->first_name . ' ' . $e->last_name),
            ];
        }, $activeEmployees);

        $templates = self::getTaskListTemplates();

        $listsCreated = 0;
        $tasksCreated = 0;
        $assignmentsCreated = 0;
        $errors = [];

        for ($i = 0; $i < $count; $i++) {
            if (DemoModeTracker::isLimitReached()) {
                break;
            }
            try {
                $tpl = $templates[$i % count($templates)];
                $suffix = $i >= count($templates) ? ' ' . ((int) ($i / count($templates)) + 1) : '';
                $ownerId = $empPool[array_rand($empPool)]['id'];

                // 1) The task-list record.
                /** @var \Model\BaseModel $taskList */
                $taskList = new $taskListClass();
                $taskList->name = $tpl['title'] . $suffix;
                $taskList->employee = $ownerId;
                $taskList->type = 'General';
                $taskList->status = 'Open';
                $taskList->can_owner_edit = 1;
                $taskList->can_assignees_edit = 0;
                $taskList->created = date('Y-m-d H:i:s');
                $taskList->updated = date('Y-m-d H:i:s');
                if (!$taskList->Save()) {
                    $errors[] = 'Could not save task list ' . $tpl['title'];
                    continue;
                }
                self::trackDemoEntry('TaskList', $taskList->id, 'task_list');
                $listsCreated++;

                // 2) The editor document: title header + description paragraph +
                //    a checklist whose items are assigned to different employees.
                $pool = $empPool;
                shuffle($pool);
                $items = [];
                $assigneeStatus = []; // empId => bool (all their items checked)
                foreach ($tpl['tasks'] as $t => $taskText) {
                    $assignee = $pool[$t % count($pool)];
                    $checked = (mt_rand(0, 100) < 30); // ~30% already done
                    $items[] = [
                        'text' => $taskText,
                        'checked' => $checked,
                        'user_image' => '',
                        'user_name' => sprintf('%s ( id:%d )', $assignee['name'], $assignee['id']),
                    ];
                    $tasksCreated++;
                    if (!isset($assigneeStatus[$assignee['id']])) {
                        $assigneeStatus[$assignee['id']] = true;
                    }
                    $assigneeStatus[$assignee['id']] = $assigneeStatus[$assignee['id']] && $checked;
                }

                $document = [
                    'time' => (int) round(microtime(true) * 1000),
                    'blocks' => [
                        ['type' => 'header', 'data' => ['text' => $tpl['title'] . $suffix, 'level' => 1]],
                        ['type' => 'paragraph', 'data' => ['text' => $tpl['description']]],
                        ['type' => 'checklist', 'data' => ['items' => $items]],
                    ],
                    'version' => '2.28.2',
                ];

                /** @var \Model\BaseModel $content */
                $content = new $contentClass();
                $content->title = $tpl['title'] . $suffix;
                $content->hash = md5('TaskList' . $taskList->id . 'document_link' . microtime(true))
                    . substr(md5(mt_rand() . $taskList->id), 0, 16);
                $content->content = json_encode($document);
                $content->object_type = 'TaskList';
                $content->object_field = 'document_link';
                $content->object_id = $taskList->id;
                $content->url = null;
                $content->status = 'private';
                $content->created = date('Y-m-d H:i:s');
                $content->updated = date('Y-m-d H:i:s');
                if ($content->Save()) {
                    self::trackDemoEntry('Content', $content->id, 'task_content');
                }

                // 3) One assignment row per assignee (mirrors
                //    TaskList::syncAssignments) — created directly so no
                //    "new task" notifications fire during demo generation.
                foreach ($assigneeStatus as $empId => $allDone) {
                    /** @var \Model\BaseModel $assignment */
                    $assignment = new $assignmentClass();
                    $assignment->tasklist = $taskList->id;
                    $assignment->employee = $empId;
                    $assignment->status = $allDone ? 'Completed' : 'Pending';
                    $assignment->notify_status = 'Notified';
                    $assignment->created = date('Y-m-d H:i:s');
                    $assignment->updated = date('Y-m-d H:i:s');
                    if ($assignment->Save()) {
                        self::trackDemoEntry('TaskListAssignment', $assignment->id, 'task_assignment');
                        $assignmentsCreated++;
                    }
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'taskLists' => $listsCreated,
                'tasks' => $tasksCreated,
                'assignments' => $assignmentsCreated,
                'errors' => $errors,
            ]
        );
    }

    /**
     * Realistic task-list templates: title, description and per-task text.
     * Tasks are round-robin assigned to shuffled employees at generation time.
     */
    private static function getTaskListTemplates(): array
    {
        return [
            [
                'title' => 'New Hire Onboarding',
                'description' => 'Everything a new team member needs to get set up and productive in their first two weeks.',
                'tasks' => [
                    'Prepare workstation, laptop and dev accounts',
                    'Create email and grant system access',
                    'Assign an onboarding buddy',
                    'Schedule team introduction meetings',
                    'Complete HR paperwork and tax forms',
                    'Enrol in payroll and benefits',
                    'Review first-week goals and expectations',
                ],
            ],
            [
                'title' => 'Employee Offboarding',
                'description' => 'Steps to complete when an employee leaves, to protect data and close the loop cleanly.',
                'tasks' => [
                    'Revoke all system and application access',
                    'Collect company assets (laptop, badge, phone)',
                    'Conduct the exit interview',
                    'Process the final settlement and payslip',
                    'Transfer knowledge, documents and ownership',
                    'Remove from mailing lists and directories',
                    'Update the org chart and team records',
                ],
            ],
            [
                'title' => 'Quarterly Office Safety Audit',
                'description' => 'Workplace health & safety inspection performed each quarter.',
                'tasks' => [
                    'Inspect fire extinguishers and alarms',
                    'Test emergency lighting and exit routes',
                    'Check and restock first-aid kits',
                    'Verify evacuation signage is visible',
                    'Review the incident log',
                    'Update safety training records',
                ],
            ],
            [
                'title' => 'Website Redesign Launch',
                'description' => 'Cross-functional tasks to ship the new marketing website.',
                'tasks' => [
                    'Finalise homepage copy and imagery',
                    'QA across browsers and devices',
                    'Migrate existing blog content',
                    'Set up analytics and tracking',
                    'Prepare the launch announcement',
                    'Monitor traffic and errors post-launch',
                ],
            ],
            [
                'title' => 'Annual Performance Review Cycle',
                'description' => 'Coordinate the company-wide performance review process.',
                'tasks' => [
                    'Publish the review timeline',
                    'Assign reviewers to each employee',
                    'Collect self-assessments',
                    'Schedule manager 1-on-1s',
                    'Calibrate ratings across teams',
                    'Communicate outcomes and next steps',
                ],
            ],
            [
                'title' => 'Team Offsite Planning',
                'description' => 'Plan and run the annual team offsite.',
                'tasks' => [
                    'Book the venue and confirm dates',
                    'Arrange travel and accommodation',
                    'Plan the agenda and sessions',
                    'Order catering and collect dietary needs',
                    'Send invitations and track RSVPs',
                    'Prepare the team-building activities',
                ],
            ],
            [
                'title' => 'IT Security Compliance',
                'description' => 'Annual security compliance and hardening checklist.',
                'tasks' => [
                    'Rotate service credentials and keys',
                    'Review user access permissions',
                    'Patch servers and dependencies',
                    'Run a phishing simulation',
                    'Update the security policy',
                    'Complete company-wide security training',
                ],
            ],
            [
                'title' => 'Product Release v2.0',
                'description' => 'Coordinate the v2.0 product release end to end.',
                'tasks' => [
                    'Freeze the feature branch',
                    'Run the full regression suite',
                    'Update release notes and docs',
                    'Notify customers of the changes',
                    'Deploy to production',
                    'Monitor error rates and prepare rollback',
                ],
            ],
            [
                'title' => 'Office Relocation',
                'description' => 'Move the team to the new office space.',
                'tasks' => [
                    'Finalise the new lease',
                    'Coordinate movers and logistics',
                    'Set up network and IT infrastructure',
                    'Update the address on all records',
                    'Notify vendors and partners',
                    'Plan the new seating chart',
                ],
            ],
            [
                'title' => 'Recruitment Drive',
                'description' => 'Fill the open roles for this quarter.',
                'tasks' => [
                    'Publish the job openings',
                    'Screen incoming applications',
                    'Schedule interview rounds',
                    'Collect and compare interview feedback',
                    'Prepare and send offers',
                    'Kick off onboarding for new hires',
                ],
            ],
            [
                'title' => 'Q3 Marketing Campaign',
                'description' => 'Plan and launch the Q3 demand-generation campaign.',
                'tasks' => [
                    'Define target audience and goals',
                    'Draft campaign messaging and creative',
                    'Set up landing pages and forms',
                    'Schedule social and email sends',
                    'Brief the sales team',
                    'Review results and report ROI',
                ],
            ],
        ];
    }

    /**
     * Whether an employee already has an attendance record whose in_time falls
     * on the given date (Y-m-d).
     */
    private static function hasAttendanceOn(int $employeeId, string $date): bool
    {
        $attendance = new Attendance();
        $existing = $attendance->Find(
            'employee = ? AND in_time >= ? AND in_time <= ?',
            [$employeeId, $date . ' 00:00:00', $date . ' 23:59:59']
        );
        return !empty($existing);
    }

    /**
     * Generate attendance records for demo employees
     */
    public static function generateAttendance(int $daysBack = 30, int $maxRecords = 0): IceResponse
    {
        $createdCount = 0;
        $errors = [];

        $demoEmployees = self::getDemoEmployees();
        if (empty($demoEmployees)) {
            return new IceResponse(IceResponse::ERROR, 'No demo employees found. Please generate employees first.');
        }

        $today = new \DateTime();

        foreach ($demoEmployees as $employeeId) {
            for ($d = $daysBack; $d >= 0; $d--) {
                // Check max records limit or demo entries limit
                if ($maxRecords > 0 && $createdCount >= $maxRecords) {
                    break 2; // Break out of both loops
                }
                if (DemoModeTracker::isLimitReached()) {
                    break 2;
                }

                $date = clone $today;
                $date->modify("-{$d} days");

                // Skip weekends
                if ($date->format('N') >= 6) {
                    continue;
                }

                // Random chance to skip a day (simulating leave/absence)
                if (rand(1, 100) <= 10) {
                    continue;
                }

                try {
                    $attendance = self::createMockAttendance($employeeId, $date);
                    if ($attendance && $attendance->id) {
                        self::trackDemoEntry('Attendance', $attendance->id, 'attendance');
                        $createdCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'created' => $createdCount,
                'errors' => $errors
            ]
        );
    }

    /**
     * Generate timesheet entries for demo employees with proper project assignments
     */
    public static function generateTimesheets(int $weeksBack = 4, int $maxTimesheets = 0): IceResponse
    {
        $createdTimesheets = 0;
        $createdEntries = 0;
        $errors = [];

        $demoEmployees = self::getDemoEmployees();
        if (empty($demoEmployees)) {
            return new IceResponse(IceResponse::ERROR, 'No demo employees found. Please generate employees first.');
        }

        // Get demo projects for time entries
        $demoProjects = self::getDemoProjects();

        $today = new \DateTime();

        foreach ($demoEmployees as $employeeId) {
            // Get projects assigned to this employee
            $employeeProjects = self::getEmployeeProjectIds($employeeId);
            // If employee has no projects, use demo projects
            $projectsToUse = !empty($employeeProjects) ? $employeeProjects : $demoProjects;

            for ($w = $weeksBack; $w >= 0; $w--) {
                // Check max timesheets limit or demo entries limit
                if ($maxTimesheets > 0 && $createdTimesheets >= $maxTimesheets) {
                    break 2;
                }
                if (DemoModeTracker::isLimitReached()) {
                    break 2;
                }

                try {
                    // Calculate week start (Monday) and end (Sunday)
                    $weekStart = clone $today;
                    $weekStart->modify("-{$w} weeks");
                    $weekStart->modify('monday this week');

                    $weekEnd = clone $weekStart;
                    $weekEnd->modify('+6 days');

                    // Create timesheet
                    $timesheet = self::createMockTimesheet($employeeId, $weekStart, $weekEnd);
                    if ($timesheet && $timesheet->id) {
                        self::trackDemoEntry('EmployeeTimeSheets', $timesheet->id, 'timesheet');
                        $createdTimesheets++;

                        // Create time entries for each weekday (Mon-Fri)
                        for ($d = 0; $d < 5; $d++) {
                            $entryDate = clone $weekStart;
                            $entryDate->modify("+{$d} days");

                            // Pick a random project for this entry
                            $projectId = !empty($projectsToUse) ? $projectsToUse[array_rand($projectsToUse)] : null;

                            $entry = self::createMockTimeEntryWithProject($employeeId, $timesheet->id, $entryDate, $projectId);
                            if ($entry && $entry->id) {
                                self::trackDemoEntry('EmployeeTimeEntry', $entry->id, 'timesheet_entry');
                                $createdEntries++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'timesheets' => $createdTimesheets,
                'entries' => $createdEntries,
                'errors' => $errors
            ]
        );
    }

    /**
     * Generate leave requests for demo employees with proper leave days
     */
    public static function generateLeaveRequests(int $countPerEmployee = 5, int $maxRecords = 0, bool $futureOnly = false): IceResponse
    {
        // Check if leave module exists
        if (!class_exists('Leaves\Common\Model\EmployeeLeave')) {
            return new IceResponse(IceResponse::ERROR, 'Leave module is not installed.');
        }

        $createdCount = 0;
        $leaveDaysCount = 0;
        $errors = [];

        $demoEmployees = self::getDemoEmployees();
        if (empty($demoEmployees)) {
            return new IceResponse(IceResponse::ERROR, 'No demo employees found. Please generate employees first.');
        }

        // Get leave types and periods
        $leaveTypes = self::getAvailableLeaveTypes();
        $leavePeriods = self::getActiveLeavePeriods();

        if (empty($leaveTypes)) {
            return new IceResponse(IceResponse::ERROR, 'No leave types found.');
        }
        if (empty($leavePeriods)) {
            return new IceResponse(IceResponse::ERROR, 'No active leave periods found.');
        }

        foreach ($demoEmployees as $employeeId) {
            for ($i = 0; $i < $countPerEmployee; $i++) {
                // Check max records limit or demo entries limit
                if ($maxRecords > 0 && $createdCount >= $maxRecords) {
                    break 2;
                }
                if (DemoModeTracker::isLimitReached()) {
                    break 2;
                }

                try {
                    // When futureOnly, every request is in the future; otherwise
                    // ~50% are future and the rest are in the past.
                    $isFuture = $futureOnly || (rand(1, 100) <= 50);
                    $result = self::createMockLeaveRequestWithDays($employeeId, $leaveTypes, $leavePeriods, $isFuture);
                    if ($result && isset($result['leave']) && $result['leave']->id) {
                        self::trackDemoEntry('EmployeeLeaves', $result['leave']->id, 'leave');
                        $createdCount++;
                        $leaveDaysCount += $result['daysCreated'] ?? 0;
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'created' => $createdCount,
                'leaveDays' => $leaveDaysCount,
                'errors' => $errors
            ]
        );
    }

    /**
     * Generate demo expense requests for demo employees, using the existing
     * expense categories and payment methods. Only available when the Expenses
     * module is installed.
     */
    public static function generateExpenseRequests(int $countPerEmployee = 3, int $maxRecords = 0): IceResponse
    {
        if (!class_exists('Expenses\Common\Model\EmployeeExpense')) {
            return new IceResponse(IceResponse::ERROR, 'Expense module is not installed.');
        }

        $demoEmployees = self::getDemoEmployees();
        if (empty($demoEmployees)) {
            return new IceResponse(IceResponse::ERROR, 'No demo employees found. Please generate employees first.');
        }

        $conn = \Classes\BaseService::getInstance()->getDB();
        $categoryIds = self::idColumn($conn->Execute('SELECT id FROM ExpensesCategories'));
        $paymentIds = self::idColumn($conn->Execute('SELECT id FROM ExpensesPaymentMethods'));

        // Only use currencies the system actually allows.
        $currencyIds = [];
        $allowedCurrencies = \Classes\SettingsManager::getInstance()->getSetting('System: Allowed Currencies');
        $decodedCurrencies = is_string($allowedCurrencies) ? json_decode($allowedCurrencies, true) : $allowedCurrencies;
        if (is_array($decodedCurrencies)) {
            foreach ($decodedCurrencies as $cid) {
                $currencyIds[] = intval($cid);
            }
        }

        if (empty($categoryIds)) {
            return new IceResponse(IceResponse::ERROR, 'No expense categories found. Please add at least one category first.');
        }
        if (empty($paymentIds)) {
            return new IceResponse(IceResponse::ERROR, 'No payment methods found. Please add at least one payment method first.');
        }

        $payees = [
            'Office Supplies Inc.', 'City Cab', 'Grand Hotel', 'AirTravel Co.', 'Cafe Central',
            'Tech Store', 'Fuel Station', 'Print Shop', 'Conference Org', 'Parking Services',
        ];
        $statuses = ['Pending', 'Approved', 'Rejected'];

        $createdCount = 0;
        $errors = [];

        foreach ($demoEmployees as $employeeId) {
            for ($i = 0; $i < $countPerEmployee; $i++) {
                if ($maxRecords > 0 && $createdCount >= $maxRecords) {
                    break 2;
                }
                if (DemoModeTracker::isLimitReached()) {
                    break 2;
                }

                try {
                    $expense = new \Expenses\Common\Model\EmployeeExpense();
                    $expense->employee = $employeeId;
                    $expense->expense_date = date('Y-m-d', strtotime('-' . rand(1, 90) . ' days'));
                    $expense->payment_method = $paymentIds[array_rand($paymentIds)];
                    $expense->category = $categoryIds[array_rand($categoryIds)];
                    $expense->transaction_no = 'DEMO-' . rand(100000, 999999);
                    $expense->payee = $payees[array_rand($payees)];
                    $expense->notes = 'Demo expense request';
                    $expense->amount = rand(1000, 50000) / 100;
                    $expense->currency = !empty($currencyIds) ? $currencyIds[array_rand($currencyIds)] : null;
                    $expense->status = $statuses[array_rand($statuses)];
                    $expense->created = date('Y-m-d H:i:s');
                    $expense->updated = date('Y-m-d H:i:s');
                    $ok = $expense->Save();
                    if ($ok && !empty($expense->id)) {
                        self::trackDemoEntry('EmployeeExpenses', $expense->id, 'expense');
                        $createdCount++;
                    } else {
                        $errors[] = $expense->ErrorMsg();
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            ['created' => $createdCount, 'errors' => $errors]
        );
    }

    /**
     * Generate demo overtime requests for demo employees, using the existing
     * overtime categories. Each request is an evening shift on a random past day
     * with 1-4 hours of overtime and a random status. Only available when the
     * Overtime module is installed.
     */
    public static function generateOvertimeRequests(int $countPerEmployee = 3, int $maxRecords = 0): IceResponse
    {
        if (!class_exists('Overtime\Common\Model\EmployeeOvertime')) {
            return new IceResponse(IceResponse::ERROR, 'Overtime module is not installed.');
        }

        $demoEmployees = self::getDemoEmployees();
        if (empty($demoEmployees)) {
            return new IceResponse(IceResponse::ERROR, 'No demo employees found. Please generate employees first.');
        }

        $conn = \Classes\BaseService::getInstance()->getDB();
        $categoryIds = self::idColumn($conn->Execute('SELECT id FROM OvertimeCategories'));
        if (empty($categoryIds)) {
            return new IceResponse(IceResponse::ERROR, 'No overtime categories found. Please add at least one category first.');
        }
        // Optional project to attribute the overtime to (most requests have one).
        $projectIds = self::idColumn($conn->Execute('SELECT id FROM Projects'));

        $notes = [
            'Project deadline crunch', 'Covered a colleague\'s shift', 'Month-end reporting',
            'Production incident response', 'Client demo preparation', 'System maintenance window',
            'Inventory stock-take', 'Weekend release deployment', 'On-call support',
        ];
        $statuses = ['Pending', 'Approved', 'Rejected'];

        $createdCount = 0;
        $errors = [];

        foreach ($demoEmployees as $employeeId) {
            for ($i = 0; $i < $countPerEmployee; $i++) {
                if ($maxRecords > 0 && $createdCount >= $maxRecords) {
                    break 2;
                }
                if (DemoModeTracker::isLimitReached()) {
                    break 2;
                }

                try {
                    // An evening shift on a random past day, 1-4 hours of overtime.
                    $day = date('Y-m-d', strtotime('-' . rand(1, 90) . ' days'));
                    $startHour = rand(17, 20);
                    $hours = rand(1, 4);
                    $start = sprintf('%s %02d:00:00', $day, $startHour);
                    $end = date('Y-m-d H:i:s', strtotime($start . ' +' . $hours . ' hours'));

                    $overtime = new \Overtime\Common\Model\EmployeeOvertime();
                    $overtime->employee = $employeeId;
                    $overtime->start_time = $start;
                    $overtime->end_time = $end;
                    $overtime->category = $categoryIds[array_rand($categoryIds)];
                    // ~70% of requests are attributed to a project.
                    $overtime->project = (!empty($projectIds) && rand(1, 100) <= 70)
                        ? $projectIds[array_rand($projectIds)] : null;
                    $overtime->notes = $notes[array_rand($notes)];
                    $overtime->status = $statuses[array_rand($statuses)];
                    $overtime->created = date('Y-m-d H:i:s');
                    $overtime->updated = date('Y-m-d H:i:s');
                    $ok = $overtime->Save();
                    if ($ok && !empty($overtime->id)) {
                        self::trackDemoEntry('EmployeeOvertime', $overtime->id, 'overtime');
                        $createdCount++;
                    } else {
                        $errors[] = $overtime->ErrorMsg();
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            ['created' => $createdCount, 'errors' => $errors]
        );
    }

    /**
     * Generate job positions (only when the recruitment jobpositions extension
     * is installed). Uses the recruitment-setup master data (employment types,
     * experience levels, job functions, education levels, industries) when the
     * jobsetup extension is present.
     */
    public static function generateJobPositions(int $count = 5): IceResponse
    {
        if (!class_exists('JobPositions\Common\Model\Job')) {
            return new IceResponse(IceResponse::ERROR, 'Recruitment module is not installed.');
        }

        $conn = \Classes\BaseService::getInstance()->getDB();
        $lookup = function (string $table) use ($conn): array {
            try {
                return self::idColumn($conn->Execute("SELECT id FROM `$table`"));
            } catch (\Exception $e) {
                return [];
            }
        };
        $employmentTypes = $lookup('EmployementType');
        $industries = $lookup('Industry');
        $experienceLevels = $lookup('ExperienceLevel');
        $jobFunctions = $lookup('JobFunction');
        $educationLevels = $lookup('EducationLevel');
        $companies = $lookup('CompanyStructures');

        $titles = [
            'Software Engineer', 'Senior Accountant', 'Marketing Specialist', 'HR Coordinator',
            'Sales Executive', 'Data Analyst', 'Product Manager', 'Customer Support Agent',
            'Operations Manager', 'UX Designer', 'DevOps Engineer', 'Content Writer',
        ];

        $createdCount = 0;
        $errors = [];

        for ($i = 0; $i < $count; $i++) {
            if (DemoModeTracker::isLimitReached()) {
                break;
            }
            try {
                $title = $titles[array_rand($titles)];
                $job = new \JobPositions\Common\Model\Job();
                $job->title = $title;
                $job->code = 'DEMO' . rand(10000, 99999);
                $job->shortDescription = "We are looking for a $title to join our team.";
                $job->description = "As a $title you will work with a cross-functional team on impactful projects.";
                $job->requirements = 'Relevant experience and good communication skills.';
                $job->display = '';
                $job->status = 'Active';
                $job->showSalary = 'No';
                $job->closingDate = date('Y-m-d H:i:s', strtotime('+' . rand(30, 90) . ' days'));
                $job->employementType = !empty($employmentTypes) ? $employmentTypes[array_rand($employmentTypes)] : null;
                $job->industry = !empty($industries) ? $industries[array_rand($industries)] : null;
                $job->experienceLevel = !empty($experienceLevels) ? $experienceLevels[array_rand($experienceLevels)] : null;
                $job->jobFunction = !empty($jobFunctions) ? $jobFunctions[array_rand($jobFunctions)] : null;
                $job->educationLevel = !empty($educationLevels) ? $educationLevels[array_rand($educationLevels)] : null;
                $job->company = !empty($companies) ? $companies[array_rand($companies)] : null;
                $ok = $job->Save();
                if ($ok && !empty($job->id)) {
                    self::trackDemoEntry('Job', $job->id, 'job');
                    $createdCount++;
                } else {
                    $errors[] = $job->ErrorMsg();
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            ['created' => $createdCount, 'errors' => $errors]
        );
    }

    /**
     * Generate candidates applying to the demo job positions (only when the
     * recruitment candidates extension is installed).
     */
    public static function generateCandidates(int $countPerJob = 3, int $maxRecords = 0): IceResponse
    {
        if (!class_exists('Candidates\Common\Model\Candidate')) {
            return new IceResponse(IceResponse::ERROR, 'Recruitment (candidates) module is not installed.');
        }

        $demoJobs = self::getDemoEntriesByType('job');
        if (empty($demoJobs)) {
            return new IceResponse(IceResponse::ERROR, 'No demo job positions found. Please generate job positions first.');
        }

        $conn = \Classes\BaseService::getInstance()->getDB();
        try {
            $stageIds = self::idColumn($conn->Execute('SELECT id FROM HiringPipeline'));
        } catch (\Exception $e) {
            $stageIds = [];
        }

        $firstNames = ['Alice', 'Ben', 'Chloe', 'Daniel', 'Erin', 'Felix', 'Grace', 'Hasan', 'Isla', 'Jorge', 'Kavya', 'Liam'];
        $lastNames = ['Anderson', 'Baker', 'Costa', 'Dias', 'Evans', 'Fernando', 'Garcia', 'Hoffman', 'Iqbal', 'Jansen', 'Kumar', 'Lopez'];

        $createdCount = 0;
        $errors = [];

        foreach ($demoJobs as $jobId) {
            for ($i = 0; $i < $countPerJob; $i++) {
                if ($maxRecords > 0 && $createdCount >= $maxRecords) {
                    break 2;
                }
                if (DemoModeTracker::isLimitReached()) {
                    break 2;
                }
                try {
                    $first = $firstNames[array_rand($firstNames)];
                    $last = $lastNames[array_rand($lastNames)];
                    $candidate = new \Candidates\Common\Model\Candidate();
                    $candidate->first_name = $first;
                    $candidate->last_name = $last;
                    $candidate->email = strtolower($first . '.' . $last) . rand(100, 999) . '@example.com';
                    $candidate->mobile_phone = '07' . rand(10000000, 99999999);
                    $candidate->gender = rand(0, 1) ? 'Male' : 'Female';
                    $candidate->cv_title = "$first $last - CV";
                    $candidate->head_line = 'Demo candidate profile';
                    $candidate->source = 'Applied';
                    $candidate->jobId = $jobId;
                    $candidate->hiringStage = !empty($stageIds) ? $stageIds[array_rand($stageIds)] : null;
                    $candidate->totalYearsOfExperience = rand(1, 12);
                    $candidate->created = date('Y-m-d H:i:s');
                    $candidate->updated = date('Y-m-d H:i:s');
                    $ok = $candidate->Save();
                    if (!$ok || empty($candidate->id)) {
                        $errors[] = $candidate->ErrorMsg();
                        continue;
                    }
                    self::trackDemoEntry('Candidates', $candidate->id, 'candidate');
                    $createdCount++;

                    // The matching application ties the candidate to the job.
                    if (class_exists('Candidates\Common\Model\Application')) {
                        $application = new \Candidates\Common\Model\Application();
                        $application->job = $jobId;
                        $application->candidate = $candidate->id;
                        $application->created = date('Y-m-d H:i:s');
                        $application->notes = 'Demo application';
                        if ($application->Save() && !empty($application->id)) {
                            self::trackDemoEntry('Applications', $application->id, 'application');
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            ['created' => $createdCount, 'errors' => $errors]
        );
    }

    private static $goalTitles = [
        'Improve customer response time', 'Complete leadership training',
        'Deliver the Q3 product release', 'Mentor a junior team member',
        'Reduce process turnaround by 15%', 'Obtain a professional certification',
        'Grow cross-team collaboration', 'Increase documentation coverage',
        'Launch the new onboarding flow', 'Cut support ticket backlog',
    ];

    private static $goalDescriptions = [
        'Own this objective through the review period and track progress regularly.',
        'Break the work into milestones and review progress with the manager monthly.',
        'A stretch goal aimed at building new skills and delivering measurable impact.',
        'Focus on consistent, incremental improvement across the period.',
    ];

    private static $answerSentences = [
        'Consistently delivered high-quality work throughout the period.',
        'Showed strong ownership and followed through on commitments.',
        'Collaborated well with the wider team and shared knowledge openly.',
        'Handled challenges calmly and found practical solutions.',
        'There is room to grow in prioritisation, but overall a solid contribution.',
        'Communicated clearly and kept stakeholders well informed.',
        'Took initiative on several projects without being asked.',
    ];

    /**
     * Generate performance reviews (only when the leave_and_performance
     * Performance module is installed). Each review is created for an existing
     * active employee over a 6-month review period, with a random status of
     * Pending, Submitted or Completed. Linked to each review are 1–3 employee
     * goals and 1–2 peer-feedback entries. Self-feedback and peer-feedback
     * questionnaire answers (stored in CustomFieldValues) and the manager's
     * reviewer feedback are filled in progressively based on the review status.
     */
    public static function generatePerformanceReviews(int $count = 10): IceResponse
    {
        if (!class_exists('Performance\\Common\\Model\\PerformanceReview')) {
            return new IceResponse(IceResponse::ERROR, 'Performance module is not installed.');
        }

        $employee = new Employee();
        $activeEmployees = $employee->Find('status = ?', ['Active']);
        if (count($activeEmployees) < 2) {
            return new IceResponse(
                IceResponse::ERROR,
                'At least two active employees are required to generate performance reviews.'
            );
        }

        // Index existing employees by id for quick name / supervisor lookup.
        $employeeIds = [];
        $employeeName = [];
        $employeeSupervisor = [];
        foreach ($activeEmployees as $emp) {
            $eid = intval($emp->id);
            $employeeIds[] = $eid;
            $employeeName[$eid] = trim($emp->first_name . ' ' . $emp->last_name);
            $employeeSupervisor[$eid] = !empty($emp->supervisor) ? intval($emp->supervisor) : null;
        }

        $conn = \Classes\BaseService::getInstance()->getDB();

        // Resolve the self-feedback and peer-feedback templates.
        $selfTemplate = self::findReviewTemplate(['self', 'employee'], $conn);
        if ($selfTemplate === null) {
            return new IceResponse(
                IceResponse::ERROR,
                'No review template found. Please create an Employee Feedback Template first.'
            );
        }
        $peerTemplate = self::findReviewTemplate(['peer'], $conn);
        if ($peerTemplate === null) {
            $peerTemplate = $selfTemplate; // fall back to the same template for peer feedback
        }

        $selfItems = self::parseTemplateItems($selfTemplate['items']);
        $peerItems = self::parseTemplateItems($peerTemplate['items']);

        $statuses = ['Pending', 'Submitted', 'Completed'];

        $reviewsCreated = 0;
        $goalsCreated = 0;
        $feedbackCreated = 0;
        $errors = [];

        for ($i = 0; $i < $count; $i++) {
            if (DemoModeTracker::isLimitReached()) {
                break;
            }

            try {
                $employeeId = $employeeIds[array_rand($employeeIds)];
                $status = $statuses[array_rand($statuses)];

                // A clean 6-month review period anchored on the first of a month
                // somewhere in the last ~18 months (0, 6, 12 or 18 months back).
                $monthsBack = 6 * rand(0, 3);
                $periodStart = date('Y-m-01 00:00:00', strtotime("-{$monthsBack} months"));
                $periodEnd = date('Y-m-t 23:59:59', strtotime($periodStart . ' +5 months'));

                $name = $employeeName[$employeeId] . ' - from ' . date('Y-m-d', strtotime($periodStart))
                    . ' to ' . date('Y-m-d', strtotime($periodEnd));

                $reviewClass = 'Performance\\Common\\Model\\PerformanceReview';
                $review = new $reviewClass();
                $review->name = substr($name, 0, 150);
                $review->employee = $employeeId;
                $review->coordinator = $employeeSupervisor[$employeeId];
                $review->attendees = '';
                $review->form = $selfTemplate['id'];
                $review->status = $status;
                $review->review_date = ($status === 'Completed') ? $periodEnd : null;
                $review->review_period_start = $periodStart;
                $review->review_period_end = $periodEnd;
                $review->self_assessment_due = $periodEnd;
                // Reviewer feedback (notes) is only written once the review completes.
                $review->notes = ($status === 'Completed') ? self::randomReviewerFeedback() : '';
                $review->created = date('Y-m-d H:i:s');
                $review->updated = date('Y-m-d H:i:s');

                if (!$review->Save() || empty($review->id)) {
                    $errors[] = 'Failed to save review: ' . $review->ErrorMsg();
                    continue;
                }
                self::trackDemoEntry('PerformanceReviews', $review->id, 'performance_review');
                $reviewsCreated++;

                // Self-feedback answers exist once the employee has submitted.
                if ($status === 'Submitted' || $status === 'Completed') {
                    self::saveQuestionnaireAnswers($conn, 'PerformanceReview', (int)$review->id, $selfItems);
                }

                // 1–3 goals linked to this review.
                $goalClass = 'Performance\\Common\\Model\\EmployeeGoal';
                $goalCount = rand(1, 3);
                for ($g = 0; $g < $goalCount; $g++) {
                    if (DemoModeTracker::isLimitReached()) {
                        break;
                    }
                    $goal = new $goalClass();
                    $goal->employee = $employeeId;
                    $goal->review = $review->id;
                    $goal->status = (rand(0, 4) === 0) ? 'Private' : 'Public';
                    $goal->title = self::$goalTitles[array_rand(self::$goalTitles)];
                    $goal->description = self::$goalDescriptions[array_rand(self::$goalDescriptions)];
                    // The employee rates their own progress once submitted; the
                    // manager rating only lands when the review is completed.
                    if ($status === 'Submitted' || $status === 'Completed') {
                        $goal->employee_rating = rand(4, 10) * 10;
                        $goal->employee_feedback = 'Made good progress on this goal.';
                    }
                    if ($status === 'Completed') {
                        $goal->manager_rating = rand(4, 10) * 10;
                        $goal->manager_feedback = 'Solid contribution against this objective.';
                    }
                    $goal->created = date('Y-m-d H:i:s');
                    $goal->updated = date('Y-m-d H:i:s');
                    if ($goal->Save() && !empty($goal->id)) {
                        self::trackDemoEntry('EmployeeGoals', $goal->id, 'employee_goal');
                        $goalsCreated++;
                    }
                }

                // 1–2 peer-feedback entries from other employees.
                $peerCandidates = array_values(array_filter($employeeIds, function ($id) use ($employeeId) {
                    return $id !== $employeeId;
                }));
                shuffle($peerCandidates);
                $peerCount = min(rand(1, 2), count($peerCandidates));
                for ($p = 0; $p < $peerCount; $p++) {
                    if (DemoModeTracker::isLimitReached()) {
                        break;
                    }
                    $reviewerId = $peerCandidates[$p];
                    // Pending reviews still have their feedback pending; otherwise
                    // most peers have already submitted.
                    $feedbackStatus = ($status === 'Pending')
                        ? 'Pending'
                        : ((rand(0, 3) === 0) ? 'Pending' : 'Submitted');

                    $feedbackClass = 'Performance\\Common\\Model\\ReviewFeedback';
                    $feedback = new $feedbackClass();
                    $feedback->employee = $reviewerId;   // the peer giving feedback
                    $feedback->review = $review->id;
                    $feedback->subject = $employeeId;     // the person being reviewed
                    $feedback->form = $peerTemplate['id'];
                    $feedback->status = $feedbackStatus;
                    $feedback->dueon = $periodEnd;
                    $feedback->rating = ($feedbackStatus === 'Submitted') ? rand(50, 95) : 0;
                    $feedback->created = date('Y-m-d H:i:s');
                    $feedback->updated = date('Y-m-d H:i:s');
                    if ($feedback->Save() && !empty($feedback->id)) {
                        self::trackDemoEntry('ReviewFeedbacks', $feedback->id, 'review_feedback');
                        $feedbackCreated++;
                        if ($feedbackStatus === 'Submitted') {
                            self::saveQuestionnaireAnswers($conn, 'ReviewFeedback', (int)$feedback->id, $peerItems);
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'created' => $reviewsCreated,
                'reviews' => $reviewsCreated,
                'goals' => $goalsCreated,
                'peerFeedback' => $feedbackCreated,
                'errors' => $errors,
            ]
        );
    }

    /**
     * Find a review template whose name matches one of the given keywords,
     * falling back to the first template. Returns ['id' => int, 'items' => json].
     */
    private static function findReviewTemplate(array $keywords, $conn): ?array
    {
        $rows = $conn->Execute('SELECT id, name, items FROM ReviewTemplates ORDER BY id');
        if (empty($rows)) {
            return null;
        }
        $all = [];
        foreach ($rows as $r) {
            $all[] = [
                'id' => intval($r['id']),
                'name' => strtolower((string)$r['name']),
                'items' => $r['items'],
            ];
        }
        foreach ($all as $tpl) {
            foreach ($keywords as $kw) {
                if (strpos($tpl['name'], strtolower($kw)) !== false) {
                    return $tpl;
                }
            }
        }
        return $all[0];
    }

    /**
     * Parse a review template's items JSON into a simple list of answerable
     * fields: [['name' => fieldName, 'type' => fieldType, 'options' => []], ...].
     */
    private static function parseTemplateItems($itemsJson): array
    {
        $items = [];
        if (empty($itemsJson)) {
            return $items;
        }
        $decoded = json_decode($itemsJson, true);
        if (!is_array($decoded)) {
            return $items;
        }
        foreach ($decoded as $it) {
            if (empty($it['name'])) {
                continue;
            }
            $options = [];
            if (!empty($it['field_options'])) {
                $options = array_values(array_filter(array_map(
                    'trim',
                    preg_split('/\r\n|\r|\n/', $it['field_options'])
                )));
            }
            $items[] = [
                'name' => $it['name'],
                'type' => $it['field_type'] ?? 'textarea',
                'options' => $options,
            ];
        }
        return $items;
    }

    /**
     * Store questionnaire answers for a review (type 'PerformanceReview') or a
     * peer-feedback entry (type 'ReviewFeedback') in CustomFieldValues, keyed by
     * the template item names. These rows are cleaned up automatically when the
     * parent review / feedback record is deleted (see deleteRecord).
     */
    private static function saveQuestionnaireAnswers($conn, string $type, int $objectId, array $items): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ($items as $item) {
            if (!empty($item['options'])) {
                $value = $item['options'][array_rand($item['options'])];
            } else {
                $value = self::$answerSentences[array_rand(self::$answerSentences)];
            }
            $conn->Execute(
                'INSERT INTO CustomFieldValues (type, name, object_id, value, created, updated) '
                . 'VALUES (?, ?, ?, ?, ?, ?)',
                [$type, $item['name'], (string)$objectId, $value, $now, $now]
            );
        }
    }

    /** A short block of manager reviewer feedback (HTML) for a completed review. */
    private static function randomReviewerFeedback(): string
    {
        $options = [
            '<p>A <b>strong</b> review period overall.</p><ul><li>Delivered key objectives on time</li>'
                . '<li>Great collaboration with the team</li></ul><p>Keep building on this momentum.</p>',
            '<p>Solid, dependable performance.</p><ul><li>Consistent quality of work</li>'
                . '<li>Positive attitude</li></ul><p>Next focus: take on more ownership of larger initiatives.</p>',
            '<p>Good progress this period with clear areas to grow.</p><ul><li>Reliable delivery</li>'
                . '<li>Willingness to learn</li></ul>',
        ];
        return $options[array_rand($options)];
    }

    /** Record ids tracked for a given demo data type. */
    private static function getDemoEntriesByType(string $dataType): array
    {
        $demoEntry = new DemoDataEntry();
        $entries = $demoEntry->Find('data_type = ?', [$dataType]);
        return array_map(function ($entry) {
            return intval($entry->record_id);
        }, $entries);
    }

    /** Extract an integer id column from an ADOdb result set. */
    private static function idColumn($rows): array
    {
        $ids = [];
        if (!empty($rows)) {
            foreach ($rows as $r) {
                $ids[] = intval($r['id']);
            }
        }
        return $ids;
    }

    /**
     * Generate payroll records for demo employees
     */
    public static function generatePayroll(string $name, string $dateStart, string $dateEnd): IceResponse
    {
        $errors = [];

        $demoEmployees = self::getDemoEmployees();
        if (empty($demoEmployees)) {
            return new IceResponse(IceResponse::ERROR, 'No demo employees found. Please generate employees first.');
        }

        // Get required lookup data
        $payFrequencies = self::getAvailablePayFrequencies();
        $departments = self::getAvailableDepartments();
        $payrollColumns = self::getAvailablePayrollColumns();

        if (empty($payFrequencies)) {
            return new IceResponse(IceResponse::ERROR, 'No pay frequencies found.');
        }
        if (empty($departments)) {
            return new IceResponse(IceResponse::ERROR, 'No departments found.');
        }

        try {
            // Create payroll
            $payroll = new Payroll();
            $payroll->name = $name;
            $payroll->pay_period = $payFrequencies[array_rand($payFrequencies)];
            $payroll->department = $departments[array_rand($departments)];
            $payroll->date_start = $dateStart;
            $payroll->date_end = $dateEnd;
            $payroll->status = 'Draft';
            $payroll->columns = json_encode($payrollColumns);
            $payroll->created = date('Y-m-d H:i:s');
            $payroll->updated = date('Y-m-d H:i:s');
            $ok = $payroll->Save();

            if (!$ok) {
                return new IceResponse(IceResponse::ERROR, 'Failed to create payroll record.');
            }

            self::trackDemoEntry('Payroll', $payroll->id, 'payroll');

            // Create payroll data for each demo employee
            $dataCount = 0;
            foreach ($demoEmployees as $employeeId) {
                foreach ($payrollColumns as $columnId) {
                    $payrollData = new PayrollData();
                    $payrollData->payroll = $payroll->id;
                    $payrollData->employee = $employeeId;
                    $payrollData->payroll_item = $columnId;
                    $payrollData->amount = rand(1000, 5000);
                    $payrollData->Save();

                    if ($payrollData->id) {
                        self::trackDemoEntry('PayrollData', $payrollData->id, 'payroll_data');
                        $dataCount++;
                    }
                }
            }

            return new IceResponse(
                IceResponse::SUCCESS,
                [
                    'payrollId' => $payroll->id,
                    'payrollDataCount' => $dataCount,
                    'errors' => $errors
                ]
            );
        } catch (\Exception $e) {
            return new IceResponse(IceResponse::ERROR, 'Failed to create payroll: ' . $e->getMessage());
        }
    }

    /**
     * Generate demo clients
     */
    public static function generateClients(int $count = 5): IceResponse
    {
        $createdIds = [];
        $errors = [];

        $usedNames = [];
        for ($i = 0; $i < $count; $i++) {
            try {
                // Pick a unique name
                $name = self::$clientNames[array_rand(self::$clientNames)];
                while (in_array($name, $usedNames) && count($usedNames) < count(self::$clientNames)) {
                    $name = self::$clientNames[array_rand(self::$clientNames)];
                }
                $usedNames[] = $name;

                $client = new Client();
                $client->name = $name;
                $client->details = 'Demo client - ' . $name;
                $client->address = rand(100, 9999) . ' ' . self::$streets[array_rand(self::$streets)] . ', ' . self::$cities[array_rand(self::$cities)];
                $client->contact_number = '+1' . rand(2000000000, 9999999999);
                $client->contact_email = strtolower(str_replace(' ', '', $name)) . '@example.com';
                $client->status = 'Active';
                $client->created = date('Y-m-d H:i:s');
                $client->updated = date('Y-m-d H:i:s');

                $ok = $client->Save();
                if ($ok && $client->id) {
                    self::trackDemoEntry('Clients', $client->id, 'client');
                    $createdIds[] = $client->id;
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'created' => count($createdIds),
                'clientIds' => $createdIds,
                'errors' => $errors
            ]
        );
    }

    /**
     * Generate demo projects and assign employees
     */
    public static function generateProjects(int $count = 5): IceResponse
    {
        $createdIds = [];
        $employeeProjectsCount = 0;
        $errors = [];

        // Get demo clients (optional - projects can exist without clients)
        $demoClients = self::getDemoClients();

        // Get demo employees for assignment
        $demoEmployees = self::getDemoEmployees();

        $usedNames = [];
        for ($i = 0; $i < $count; $i++) {
            try {
                // Pick a unique name
                $name = self::$projectNames[array_rand(self::$projectNames)];
                while (in_array($name, $usedNames) && count($usedNames) < count(self::$projectNames)) {
                    $name = self::$projectNames[array_rand(self::$projectNames)];
                }
                $usedNames[] = $name;

                $project = new Project();
                $project->name = $name;
                // Assign client if available, otherwise null
                $project->client = !empty($demoClients) ? $demoClients[array_rand($demoClients)] : null;
                $project->details = 'Demo project - ' . $name;
                $project->status = 'Active';
                $project->created = date('Y-m-d H:i:s');

                $ok = $project->Save();
                if ($ok && $project->id) {
                    self::trackDemoEntry('Projects', $project->id, 'project');
                    $createdIds[] = $project->id;

                    // Assign 3-8 random employees to this project
                    if (!empty($demoEmployees)) {
                        $numEmployees = min(rand(3, 8), count($demoEmployees));
                        $assignedEmployees = array_rand(array_flip($demoEmployees), $numEmployees);
                        if (!is_array($assignedEmployees)) {
                            $assignedEmployees = [$assignedEmployees];
                        }

                        foreach ($assignedEmployees as $empId) {
                            try {
                                $empProject = new EmployeeProject();
                                $empProject->employee = $empId;
                                $empProject->project = $project->id;
                                $empProject->status = 'Current';
                                $empProject->date_start = date('Y-m-d', strtotime('-' . rand(30, 180) . ' days'));
                                $empProject->details = 'Assigned to ' . $name;
                                $ok = $empProject->Save();

                                if ($ok && $empProject->id) {
                                    self::trackDemoEntry('EmployeeProjects', $empProject->id, 'employee_project');
                                    $employeeProjectsCount++;
                                }
                            } catch (\Exception $e) {
                                $errors[] = 'EmployeeProject: ' . $e->getMessage();
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'created' => count($createdIds),
                'projectIds' => $createdIds,
                'employeeProjects' => $employeeProjectsCount,
                'errors' => $errors
            ]
        );
    }

    /**
     * Generate projects with explicit employee and client IDs (for quick setup flow)
     */
    public static function generateProjectsWithIds(int $count, array $employeeIds, array $clientIds): IceResponse
    {
        $createdIds = [];
        $employeeProjectsCount = 0;
        $errors = [];

        $usedNames = [];
        for ($i = 0; $i < $count; $i++) {
            try {
                $name = self::$projectNames[array_rand(self::$projectNames)];
                while (in_array($name, $usedNames) && count($usedNames) < count(self::$projectNames)) {
                    $name = self::$projectNames[array_rand(self::$projectNames)];
                }
                $usedNames[] = $name;

                $project = new Project();
                $project->name = $name;
                $project->client = !empty($clientIds) ? $clientIds[array_rand($clientIds)] : null;
                $project->details = 'Demo project - ' . $name;
                $project->status = 'Active';
                $project->created = date('Y-m-d H:i:s');

                $ok = $project->Save();
                if ($ok && $project->id) {
                    self::trackDemoEntry('Projects', $project->id, 'project');
                    $createdIds[] = $project->id;

                    // Assign 3-8 random employees to this project
                    if (!empty($employeeIds)) {
                        $numEmployees = min(rand(3, 8), count($employeeIds));
                        $assignedEmployees = array_rand(array_flip($employeeIds), $numEmployees);
                        if (!is_array($assignedEmployees)) {
                            $assignedEmployees = [$assignedEmployees];
                        }

                        foreach ($assignedEmployees as $empId) {
                            try {
                                $empProject = new EmployeeProject();
                                $empProject->employee = $empId;
                                $empProject->project = $project->id;
                                $empProject->status = 'Current';
                                $empProject->date_start = date('Y-m-d', strtotime('-' . rand(30, 180) . ' days'));
                                $empProject->details = 'Assigned to ' . $name;
                                $ok = $empProject->Save();

                                if ($ok && $empProject->id) {
                                    self::trackDemoEntry('EmployeeProjects', $empProject->id, 'employee_project');
                                    $employeeProjectsCount++;
                                }
                            } catch (\Exception $e) {
                                $errors[] = 'EmployeeProject: ' . $e->getMessage();
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'created' => count($createdIds),
                'projectIds' => $createdIds,
                'employeeProjects' => $employeeProjectsCount,
                'errors' => $errors
            ]
        );
    }

    /**
     * Generate attendance with explicit employee IDs
     */
    public static function generateAttendanceWithIds(int $daysBack, int $maxRecords, array $employeeIds): IceResponse
    {
        $createdCount = 0;
        $errors = [];

        if (empty($employeeIds)) {
            return new IceResponse(IceResponse::ERROR, 'No employee IDs provided.');
        }

        $today = new \DateTime();

        foreach ($employeeIds as $employeeId) {
            for ($d = $daysBack; $d >= 0; $d--) {
                if ($maxRecords > 0 && $createdCount >= $maxRecords) {
                    break 2;
                }
                if (DemoModeTracker::isLimitReached()) {
                    break 2;
                }

                $date = clone $today;
                $date->modify("-{$d} days");

                if ($date->format('N') >= 6) {
                    continue;
                }

                if (rand(1, 100) <= 10) {
                    continue;
                }

                try {
                    $attendance = self::createMockAttendance($employeeId, $date);
                    if ($attendance && $attendance->id) {
                        self::trackDemoEntry('Attendance', $attendance->id, 'attendance');
                        $createdCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return new IceResponse(IceResponse::SUCCESS, ['created' => $createdCount, 'errors' => $errors]);
    }

    /**
     * Generate leave requests with explicit employee IDs
     */
    public static function generateLeaveRequestsWithIds(int $countPerEmployee, int $maxRecords, array $employeeIds, bool $futureOnly = false): IceResponse
    {
        if (!class_exists('Leaves\Common\Model\EmployeeLeave')) {
            return new IceResponse(IceResponse::ERROR, 'Leave module is not installed.');
        }

        $createdCount = 0;
        $leaveDaysCount = 0;
        $errors = [];

        if (empty($employeeIds)) {
            return new IceResponse(IceResponse::ERROR, 'No employee IDs provided.');
        }

        $leaveTypes = self::getAvailableLeaveTypes();
        $leavePeriods = self::getActiveLeavePeriods();

        if (empty($leaveTypes)) {
            return new IceResponse(IceResponse::ERROR, 'No leave types found.');
        }
        if (empty($leavePeriods)) {
            return new IceResponse(IceResponse::ERROR, 'No active leave periods found.');
        }

        foreach ($employeeIds as $employeeId) {
            for ($i = 0; $i < $countPerEmployee; $i++) {
                if ($maxRecords > 0 && $createdCount >= $maxRecords) {
                    break 2;
                }
                if (DemoModeTracker::isLimitReached()) {
                    break 2;
                }

                try {
                    // When futureOnly, every request is in the future; otherwise
                    // ~50% are future and the rest are in the past.
                    $isFuture = $futureOnly || (rand(1, 100) <= 50);
                    $result = self::createMockLeaveRequestWithDays($employeeId, $leaveTypes, $leavePeriods, $isFuture);
                    if ($result && isset($result['leave']) && $result['leave']->id) {
                        self::trackDemoEntry('EmployeeLeaves', $result['leave']->id, 'leave');
                        $createdCount++;
                        $leaveDaysCount += $result['daysCreated'] ?? 0;
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return new IceResponse(IceResponse::SUCCESS, [
            'created' => $createdCount,
            'leaveDays' => $leaveDaysCount,
            'errors' => $errors
        ]);
    }

    /**
     * Generate timesheets with explicit employee and project IDs
     */
    public static function generateTimesheetsWithIds(int $weeksBack, int $maxTimesheets, array $employeeIds, array $projectIds): IceResponse
    {
        $createdTimesheets = 0;
        $createdEntries = 0;
        $errors = [];

        if (empty($employeeIds)) {
            return new IceResponse(IceResponse::ERROR, 'No employee IDs provided.');
        }

        $today = new \DateTime();

        foreach ($employeeIds as $employeeId) {
            for ($w = $weeksBack; $w >= 0; $w--) {
                if ($maxTimesheets > 0 && $createdTimesheets >= $maxTimesheets) {
                    break 2;
                }
                if (DemoModeTracker::isLimitReached()) {
                    break 2;
                }

                try {
                    $weekStart = clone $today;
                    $weekStart->modify("-{$w} weeks");
                    $weekStart->modify('monday this week');

                    $weekEnd = clone $weekStart;
                    $weekEnd->modify('+6 days');

                    $timesheet = self::createMockTimesheet($employeeId, $weekStart, $weekEnd);
                    if ($timesheet && $timesheet->id) {
                        self::trackDemoEntry('EmployeeTimeSheets', $timesheet->id, 'timesheet');
                        $createdTimesheets++;

                        for ($d = 0; $d < 5; $d++) {
                            $entryDate = clone $weekStart;
                            $entryDate->modify("+{$d} days");

                            // Use provided project IDs directly
                            $projectId = !empty($projectIds) ? $projectIds[array_rand($projectIds)] : null;

                            $entry = self::createMockTimeEntryWithProject($employeeId, $timesheet->id, $entryDate, $projectId);
                            if ($entry && $entry->id) {
                                self::trackDemoEntry('EmployeeTimeEntry', $entry->id, 'timesheet_entry');
                                $createdEntries++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        return new IceResponse(IceResponse::SUCCESS, [
            'timesheets' => $createdTimesheets,
            'entries' => $createdEntries,
            'errors' => $errors
        ]);
    }

    /**
     * Delete all demo data entries
     */
    public static function deleteAllDemoData(): IceResponse
    {
        // Disable demo mode first to prevent tracking during deletion
        DemoModeTracker::disableDemoMode();

        $deleted = [
            'employees' => 0,
            'users' => 0,
            'employee_data' => 0,
            'attendance' => 0,
            'timesheets' => 0,
            'timesheet_entries' => 0,
            'leave' => 0,
            'leave_day' => 0,
            'payroll' => 0,
            'payroll_data' => 0,
            'projects' => 0,
            'clients' => 0,
            'employee_projects' => 0
        ];
        $errors = [];

        $demoEntry = new DemoDataEntry();
        $entries = $demoEntry->Find('1=1 ORDER BY id DESC');

        foreach ($entries as $entry) {
            // Try to delete the actual record (may fail if already deleted or missing)
            try {
                self::deleteRecord($entry->table_name, $entry->record_id);
            } catch (\Exception $e) {
                $errors[] = "Failed to delete actual record {$entry->table_name}:{$entry->record_id} - " . $e->getMessage();
            }

            // Always delete the DemoDataEntry regardless of whether actual record existed
            try {
                $deleted[$entry->data_type] = ($deleted[$entry->data_type] ?? 0) + 1;
                $entry->Delete();
            } catch (\Exception $e) {
                $errors[] = "Failed to delete DemoDataEntry {$entry->id} - " . $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'deleted' => $deleted,
                'errors' => $errors
            ]
        );
    }

    /**
     * Delete demo data by type
     */
    public static function deleteDemoDataByType(string $dataType): IceResponse
    {
        $deletedCount = 0;
        $errors = [];

        $demoEntry = new DemoDataEntry();
        $entries = $demoEntry->Find('data_type = ? ORDER BY id DESC', [$dataType]);

        foreach ($entries as $entry) {
            // Try to delete the actual record (may fail if already deleted or missing)
            try {
                self::deleteRecord($entry->table_name, $entry->record_id);
            } catch (\Exception $e) {
                $errors[] = "Failed to delete actual record {$entry->table_name}:{$entry->record_id} - " . $e->getMessage();
            }

            // Always delete the DemoDataEntry regardless of whether actual record existed
            try {
                $entry->Delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to delete DemoDataEntry {$entry->id} - " . $e->getMessage();
            }
        }

        return new IceResponse(
            IceResponse::SUCCESS,
            [
                'deleted' => $deletedCount,
                'errors' => $errors
            ]
        );
    }

    /**
     * Get demo data statistics
     */
    public static function getStats(): IceResponse
    {
        $stats = [
            'employee' => 0,
            'user' => 0,
            'employee_data' => 0,
            'attendance' => 0,
            'timesheet' => 0,
            'timesheet_entry' => 0,
            'leave' => 0,
            'leave_day' => 0,
            'payroll' => 0,
            'payroll_data' => 0,
            'project' => 0,
            'client' => 0,
            'employee_project' => 0,
            'expense' => 0,
            'job' => 0,
            'candidate' => 0,
            'application' => 0,
            'team' => 0,
            'team_member' => 0,
            'performance_review' => 0,
            'employee_goal' => 0,
            'review_feedback' => 0,
            'task_list' => 0,
            'task_assignment' => 0,
            'task_content' => 0
        ];

        $demoEntry = new DemoDataEntry();
        $entries = $demoEntry->Find('1=1');

        foreach ($entries as $entry) {
            $stats[$entry->data_type] = ($stats[$entry->data_type] ?? 0) + 1;
        }

        // Which optional modules are installed (so the UI can show/hide the
        // matching demo-data generators). The pro generators (all backed by
        // extensions-pro/ modules) are additionally gated on a Pro/Cloud build:
        // when IS_ICEHRM_PRO/IS_CLOUD are off, only free-extension sections show.
        // (class_exists alone is not enough for leave_and_performance, whose
        // classes remain autoloadable via the composer PSR-4 fallback.) Overtime
        // is a core module, so it is not pro-gated.
        $isPro = function_exists('iceProExtensionsEnabled')
            ? iceProExtensionsEnabled()
            : (defined('IS_ICEHRM_PRO') && IS_ICEHRM_PRO);
        $stats['modules'] = [
            'expense' => $isPro && class_exists('Expenses\Common\Model\EmployeeExpense'),
            'overtime' => class_exists('Overtime\Common\Model\EmployeeOvertime'),
            'jobposition' => $isPro && class_exists('JobPositions\Common\Model\Job'),
            'candidate' => $isPro && class_exists('Candidates\Common\Model\Candidate'),
            'team' => $isPro && class_exists('TeamAdmin\Common\Model\Team'),
            'performance' => $isPro && class_exists('Performance\Common\Model\PerformanceReview'),
            'tasks' => $isPro && class_exists('TasksAdmin\Common\Model\TaskList')
                && class_exists('EditorUser\Common\Model\Content'),
            'leave' => $isPro && class_exists('Leaves\Common\Model\EmployeeLeave'),
            'payroll' => $isPro && class_exists('Payroll\Common\Model\Payroll'),
        ];

        return new IceResponse(IceResponse::SUCCESS, $stats);
    }

    // ==================== Helper Methods ====================

    /**
     * A realistic random joined date: tenure of 0–15 years, landing on a random
     * day of the calendar (not today's month/day) and never in the future.
     */
    public static function generateRealisticJoinedDate(): string
    {
        $tenureYears = rand(0, 15);
        $joinYear = (int) date('Y') - $tenureYears;
        $dayOfYear = rand(0, 364);
        $ts = strtotime($joinYear . '-01-01 +' . $dayOfYear . ' days');
        if ($ts === false || $ts > time()) {
            // Pull a future/invalid date back into the recent past.
            $ts = time() - (rand(0, 200) * 86400);
        }
        return date('Y-m-d', $ts);
    }

    /**
     * A realistic random birthday for someone who joined on $joinedDate: they
     * were a working adult (aged 20–45) when hired, and the birthday lands on a
     * random day of the year so celebrations are spread across the calendar
     * rather than all sharing the generation day's month/day.
     */
    public static function generateRealisticBirthday(?string $joinedDate = null): string
    {
        $joinTs = $joinedDate ? strtotime($joinedDate) : false;
        if ($joinTs === false) {
            $joinTs = time();
        }
        $ageAtJoin = rand(20, 45);
        $birthYear = (int) date('Y', $joinTs) - $ageAtJoin;
        $dayOfYear = rand(0, 364);
        return date('Y-m-d', strtotime($birthYear . '-01-01 +' . $dayOfYear . ' days'));
    }

    private static function createMockEmployee($departments, $jobTitles, $employmentStatuses, ?int $supervisorId = null): ?Employee
    {
        $firstName = self::$firstNames[array_rand(self::$firstNames)];
        $lastName = self::$lastNames[array_rand(self::$lastNames)];
        $employeeId = 'DEMO' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        $employee = new Employee();
        $employee->employee_id = $employeeId;
        $employee->first_name = $firstName;
        $employee->last_name = $lastName;
        $employee->middle_name = '';
        $employee->gender = rand(0, 1) ? 'Male' : 'Female';
        $employee->marital_status = ['Single', 'Married', 'Divorced'][rand(0, 2)];
        $employee->work_email = strtolower($firstName . '.' . $lastName . '@demo.icehrm.com');
        $employee->private_email = strtolower($firstName . rand(1, 99) . '@example.com');
        $employee->mobile_phone = '+1' . rand(2000000000, 9999999999);
        $employee->address1 = rand(100, 9999) . ' ' . self::$streets[array_rand(self::$streets)];
        $employee->city = self::$cities[array_rand(self::$cities)];
        $employee->postal_code = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
        $employee->country = 'US';
        $employee->department = $departments[array_rand($departments)];
        $employee->job_title = $jobTitles[array_rand($jobTitles)];
        $employee->employment_status = $employmentStatuses[array_rand($employmentStatuses)];
        // Realistic joined date + birthday consistent with it (adult at hiring,
        // birthdays spread across the calendar rather than all on the same day).
        $employee->joined_date = self::generateRealisticJoinedDate();
        $employee->birthday = self::generateRealisticBirthday($employee->joined_date);
        $employee->supervisor = $supervisorId;
        $employee->status = 'Active';
        $employee->created = date('Y-m-d H:i:s');
        $employee->updated = date('Y-m-d H:i:s');

        $ok = $employee->Save();
        return $ok ? $employee : null;
    }

    private static function createMockAttendance(int $employeeId, \DateTime $date): ?Attendance
    {
        // Random work hours between 8:00 and 9:30 start
        $inHour = rand(8, 9);
        $inMinute = rand(0, 59);
        $inTime = $date->format('Y-m-d') . ' ' . str_pad($inHour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($inMinute, 2, '0', STR_PAD_LEFT) . ':00';

        // Work 7-9 hours
        $workHours = rand(7, 9);
        $outHour = $inHour + $workHours;
        $outMinute = rand(0, 59);
        $outTime = $date->format('Y-m-d') . ' ' . str_pad($outHour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($outMinute, 2, '0', STR_PAD_LEFT) . ':00';

        $attendance = new Attendance();
        $attendance->employee = $employeeId;
        $attendance->in_time = $inTime;
        $attendance->out_time = $outTime;
        $attendance->note = 'Demo attendance record';
        $attendance->created = date('Y-m-d H:i:s');
        $attendance->updated = date('Y-m-d H:i:s');

        $ok = $attendance->Save();
        return $ok ? $attendance : null;
    }

    private static function createMockTimesheet(int $employeeId, \DateTime $weekStart, \DateTime $weekEnd): ?EmployeeTimeSheet
    {
        $timesheet = new EmployeeTimeSheet();
        $timesheet->employee = $employeeId;
        $timesheet->date_start = $weekStart->format('Y-m-d');
        $timesheet->date_end = $weekEnd->format('Y-m-d');
        $timesheet->status = ['Pending', 'Approved', 'Submitted'][rand(0, 2)];

        $ok = $timesheet->Save();
        return $ok ? $timesheet : null;
    }

    private static function createMockTimeEntry(int $employeeId, int $timesheetId, \DateTime $date): ?EmployeeTimeEntry
    {
        $startHour = rand(8, 9);
        $endHour = $startHour + rand(7, 9);

        $entry = new EmployeeTimeEntry();
        $entry->employee = $employeeId;
        $entry->timesheet = $timesheetId;
        $entry->date_start = $date->format('Y-m-d') . ' ' . str_pad($startHour, 2, '0', STR_PAD_LEFT) . ':00:00';
        $entry->date_end = $date->format('Y-m-d') . ' ' . str_pad($endHour, 2, '0', STR_PAD_LEFT) . ':00:00';
        $entry->time_start = str_pad($startHour, 2, '0', STR_PAD_LEFT) . ':00';
        $entry->time_end = str_pad($endHour, 2, '0', STR_PAD_LEFT) . ':00';
        $entry->details = 'Demo work entry for ' . $date->format('Y-m-d');
        $entry->status = 'Active';
        $entry->created = date('Y-m-d H:i:s');

        $ok = $entry->Save();
        return $ok ? $entry : null;
    }

    /**
     * Create a mock leave request (without leave days)
     * Ensures the leave falls entirely within a single leave period
     */
    private static function createMockLeaveRequest(int $employeeId, array $leaveTypes, array $leavePeriods)
    {
        if (empty($leavePeriods)) {
            return null;
        }

        $leaveTypeId = $leaveTypes[array_rand($leaveTypes)];

        // Pick a random leave period first
        $leavePeriod = $leavePeriods[array_rand($leavePeriods)];
        $leavePeriodId = $leavePeriod['id'];
        $periodStart = new \DateTime($leavePeriod['date_start']);
        $periodEnd = new \DateTime($leavePeriod['date_end']);

        // Calculate the available date range within this leave period
        $today = new \DateTime();
        $effectiveEnd = $periodEnd < $today ? $periodEnd : $today;

        if ($periodStart > $today || $periodStart > $effectiveEnd) {
            return null;
        }

        $daysInRange = $periodStart->diff($effectiveEnd)->days;
        if ($daysInRange < 1) {
            return null;
        }

        $maxLeaveDays = min(5, $daysInRange);
        $leaveDays = rand(1, $maxLeaveDays);

        $maxStartOffset = max(0, $daysInRange - $leaveDays);
        $startOffset = rand(0, $maxStartOffset);

        $dateStart = clone $periodStart;
        $dateStart->modify("+{$startOffset} days");

        $dateEnd = clone $dateStart;
        $dateEnd->modify("+" . ($leaveDays - 1) . " days");

        if ($dateStart < $periodStart || $dateEnd > $periodEnd) {
            return null;
        }

        $leaveClass = 'Leaves\Common\Model\EmployeeLeave';
        $leave = new $leaveClass();
        $leave->employee = $employeeId;
        $leave->leave_type = $leaveTypeId;
        $leave->leave_period = $leavePeriodId;
        $leave->date_start = $dateStart->format('Y-m-d');
        $leave->date_end = $dateEnd->format('Y-m-d');
        $leave->details = 'Demo leave request';
        $leave->status = ['Pending', 'Approved', 'Rejected'][rand(0, 2)];
        $leave->created = date('Y-m-d H:i:s');
        $leave->updated = date('Y-m-d H:i:s');

        $ok = $leave->Save();
        return $ok ? $leave : null;
    }

    private static function trackDemoEntry(string $tableName, int $recordId, string $dataType): void
    {
        // Skip manual tracking if demo mode is enabled, as BaseModel will track automatically
        if (DemoModeTracker::isDemoModeEnabled()) {
            return;
        }

        // Skip if we've reached the maximum number of demo entries
        if (DemoModeTracker::isLimitReached()) {
            return;
        }

        $entry = new DemoDataEntry();
        $entry->table_name = $tableName;
        $entry->record_id = $recordId;
        $entry->data_type = $dataType;
        $entry->created = date('Y-m-d H:i:s');
        $entry->Save();
    }

    /**
     * Track demo entry directly (bypasses demo mode check for excluded tables like Users)
     */
    private static function trackDemoEntryDirect(string $tableName, int $recordId, string $dataType): void
    {
        // Skip if we've reached the maximum number of demo entries
        if (DemoModeTracker::isLimitReached()) {
            return;
        }

        $entry = new DemoDataEntry();
        $entry->table_name = $tableName;
        $entry->record_id = $recordId;
        $entry->data_type = $dataType;
        $entry->created = date('Y-m-d H:i:s');
        $entry->Save();
    }

    /**
     * Create a user account for an employee
     * Uses random non-existing domain emails to prevent email triggers
     */
    private static function createUserForEmployee(Employee $employee, string $userLevel): ?User
    {
        // Generate unique username from employee name
        $baseUsername = strtolower($employee->first_name . '.' . $employee->last_name);
        $username = $baseUsername . rand(100, 999);

        // Use non-existing domain to prevent any email sending
        $fakeEmailDomains = [
            'demo.invalid',
            'test.invalid',
            'example.invalid',
            'fake.invalid',
            'noreply.invalid'
        ];
        $email = $username . '@' . $fakeEmailDomains[array_rand($fakeEmailDomains)];

        // Check if username or email already exists
        $existingUser = new User();
        $existing = $existingUser->Find('username = ? OR email = ?', [$username, $email]);
        if (!empty($existing)) {
            // Add more randomness if collision
            $username = $baseUsername . rand(1000, 9999);
            $email = $username . '@' . $fakeEmailDomains[array_rand($fakeEmailDomains)];
        }

        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->employee = $employee->id;
        $user->user_level = $userLevel;
        // Set a random password hash (users won't actually log in with these)
        $user->password = PasswordManager::createPasswordHash('demo' . rand(10000, 99999));
        $user->created = date('Y-m-d H:i:s');
        $user->updated = date('Y-m-d H:i:s');

        $ok = $user->Save();
        return $ok ? $user : null;
    }

    private static function deleteRecord(string $tableName, int $recordId): void
    {
        $modelMap = [
            'Employees' => Employee::class,
            'Users' => User::class,
            'Attendance' => Attendance::class,
            'EmployeeTimeSheets' => EmployeeTimeSheet::class,
            'EmployeeTimeEntry' => EmployeeTimeEntry::class,
            'Payroll' => Payroll::class,
            'PayrollData' => PayrollData::class,
            'Projects' => Project::class,
            'Clients' => Client::class,
            'EmployeeProjects' => EmployeeProject::class,
            'EmployeeCareer' => EmployeeCareer::class,
            'EmployeeEducations' => EmployeeEducation::class,
            'EmployeeCertifications' => EmployeeCertification::class,
            'EmployeeLanguages' => EmployeeLanguage::class,
            'EmployeeDependents' => EmployeeDependent::class,
            'EmergencyContacts' => EmergencyContact::class,
        ];

        // Handle leave separately due to extension dependency
        if ($tableName === 'EmployeeLeaves' && class_exists('Leaves\Common\Model\EmployeeLeave')) {
            $leaveClass = 'Leaves\Common\Model\EmployeeLeave';
            $model = new $leaveClass();
            $model->Load('id = ?', [$recordId]);
            if ($model->id) {
                $model->Delete();
            }
            return;
        }

        // Handle leave days separately due to extension dependency
        // Note: Class is EmployeeLeaveDay (singular), table is EmployeeLeaveDays (plural)
        if ($tableName === 'EmployeeLeaveDays' && class_exists('Leaves\Common\Model\EmployeeLeaveDay')) {
            $leaveDayClass = 'Leaves\Common\Model\EmployeeLeaveDay';
            $model = new $leaveDayClass();
            $model->Load('id = ?', [$recordId]);
            if ($model->id) {
                $model->Delete();
            }
            return;
        }

        // Handle expenses separately due to extension dependency
        if ($tableName === 'EmployeeExpenses' && class_exists('Expenses\Common\Model\EmployeeExpense')) {
            $expenseClass = 'Expenses\Common\Model\EmployeeExpense';
            $model = new $expenseClass();
            $model->Load('id = ?', [$recordId]);
            if ($model->id) {
                $model->Delete();
            }
            return;
        }

        // Handle team records separately due to extension dependency
        $teamMap = [
            'EmployeeTeams' => 'TeamAdmin\Common\Model\Team',
            'EmployeeTeamMembers' => 'TeamAdmin\Common\Model\TeamMember',
        ];
        if (isset($teamMap[$tableName])) {
            $className = $teamMap[$tableName];
            if (class_exists($className)) {
                $model = new $className();
                $model->Load('id = ?', [$recordId]);
                if ($model->id) {
                    $model->Delete();
                }
            }
            return;
        }

        // Handle task-list records + their editor documents separately due to
        // extension dependency (tasks + editor extensions).
        $taskMap = [
            'TaskList' => 'TasksAdmin\Common\Model\TaskList',
            'TaskListAssignment' => 'TasksAdmin\Common\Model\TaskListAssignment',
            'Content' => 'EditorUser\Common\Model\Content',
        ];
        if (isset($taskMap[$tableName])) {
            $className = $taskMap[$tableName];
            if (class_exists($className)) {
                $model = new $className();
                $model->Load('id = ?', [$recordId]);
                if ($model->id) {
                    $model->Delete();
                }
            }
            return;
        }

        // Handle recruitment records separately due to extension dependency
        $recruitmentMap = [
            'Job' => 'JobPositions\Common\Model\Job',
            'Candidates' => 'Candidates\Common\Model\Candidate',
            'Applications' => 'Candidates\Common\Model\Application',
        ];
        if (isset($recruitmentMap[$tableName])) {
            $className = $recruitmentMap[$tableName];
            if (class_exists($className)) {
                $model = new $className();
                $model->Load('id = ?', [$recordId]);
                if ($model->id) {
                    $model->Delete();
                }
            }
            return;
        }

        // Handle performance records separately due to extension dependency.
        $performanceMap = [
            'PerformanceReviews' => 'Performance\Common\Model\PerformanceReview',
            'EmployeeGoals' => 'Performance\Common\Model\EmployeeGoal',
            'ReviewFeedbacks' => 'Performance\Common\Model\ReviewFeedback',
        ];
        if (isset($performanceMap[$tableName])) {
            $className = $performanceMap[$tableName];
            if (class_exists($className)) {
                $model = new $className();
                $model->Load('id = ?', [$recordId]);
                if ($model->id) {
                    $model->Delete();
                }
            }
            // Clean up questionnaire answers keyed to this review / feedback.
            // (Goals carry no answers, and are skipped to avoid id collisions.)
            if ($tableName === 'PerformanceReviews' || $tableName === 'ReviewFeedbacks') {
                $answerType = ($tableName === 'ReviewFeedbacks') ? 'ReviewFeedback' : 'PerformanceReview';
                \Classes\BaseService::getInstance()->getDB()->Execute(
                    'DELETE FROM CustomFieldValues WHERE type = ? AND object_id = ?',
                    [$answerType, (string)$recordId]
                );
            }
            return;
        }

        if (isset($modelMap[$tableName])) {
            $className = $modelMap[$tableName];
            $model = new $className();
            $model->Load('id = ?', [$recordId]);
            if ($model->id) {
                $model->Delete();
            }
        }
    }

    private static function getDemoEmployees(): array
    {
        $demoEntry = new DemoDataEntry();
        $entries = $demoEntry->Find('data_type = ?', ['employee']);
        return array_map(function ($entry) {
            return $entry->record_id;
        }, $entries);
    }

    private static function getDemoClients(): array
    {
        $demoEntry = new DemoDataEntry();
        $entries = $demoEntry->Find('data_type = ?', ['client']);
        return array_map(function ($entry) {
            return $entry->record_id;
        }, $entries);
    }

    private static function getDemoProjects(): array
    {
        $demoEntry = new DemoDataEntry();
        $entries = $demoEntry->Find('data_type = ?', ['project']);
        return array_map(function ($entry) {
            return $entry->record_id;
        }, $entries);
    }

    private static function getAvailableDepartments(): array
    {
        $dept = new CompanyStructure();
        $depts = $dept->Find('1=1');
        return array_map(function ($d) {
            return $d->id;
        }, $depts);
    }

    private static function getAvailableJobTitles(): array
    {
        $job = new JobTitle();
        $jobs = $job->Find('1=1');
        return array_map(function ($j) {
            return $j->id;
        }, $jobs);
    }

    private static function getAvailableEmploymentStatuses(): array
    {
        $status = new EmploymentStatus();
        $statuses = $status->Find('1=1');
        return array_map(function ($s) {
            return $s->id;
        }, $statuses);
    }

    private static function getAvailableLeaveTypes(): array
    {
        if (!class_exists('Leaves\Common\Model\LeaveType')) {
            return [];
        }
        $leaveTypeClass = 'Leaves\Common\Model\LeaveType';
        $leaveType = new $leaveTypeClass();
        $types = $leaveType->Find('1=1');
        return array_map(function ($t) {
            return $t->id;
        }, $types);
    }

    /**
     * Get active leave periods with their full data (id, date_start, date_end)
     * @return array Array of leave period objects with id, date_start, date_end
     */
    private static function getActiveLeavePeriods(): array
    {
        if (!class_exists('Leaves\Common\Model\LeavePeriod')) {
            return [];
        }
        $leavePeriodClass = 'Leaves\Common\Model\LeavePeriod';
        $period = new $leavePeriodClass();
        $periods = $period->Find("status = ?", ['Active']);
        return array_map(function ($p) {
            return [
                'id' => $p->id,
                'date_start' => $p->date_start,
                'date_end' => $p->date_end
            ];
        }, $periods);
    }

    private static function getAvailablePayFrequencies(): array
    {
        $freq = new PayFrequency();
        $freqs = $freq->Find('1=1');
        return array_map(function ($f) {
            return $f->id;
        }, $freqs);
    }

    private static function getAvailablePayrollColumns(): array
    {
        $col = new PayrollColumn();
        $cols = $col->Find("enabled = ?", ['Yes']);
        return array_map(function ($c) {
            return $c->id;
        }, $cols);
    }

    /**
     * Create a time entry with project assignment
     */
    private static function createMockTimeEntryWithProject(int $employeeId, int $timesheetId, \DateTime $date, ?int $projectId = null): ?EmployeeTimeEntry
    {
        $startHour = rand(8, 9);
        $endHour = $startHour + rand(7, 9);

        $entry = new EmployeeTimeEntry();
        $entry->employee = $employeeId;
        $entry->timesheet = $timesheetId;
        $entry->project = $projectId;
        $entry->date_start = $date->format('Y-m-d') . ' ' . str_pad($startHour, 2, '0', STR_PAD_LEFT) . ':00:00';
        $entry->date_end = $date->format('Y-m-d') . ' ' . str_pad($endHour, 2, '0', STR_PAD_LEFT) . ':00:00';
        $entry->time_start = str_pad($startHour, 2, '0', STR_PAD_LEFT) . ':00';
        $entry->time_end = str_pad($endHour, 2, '0', STR_PAD_LEFT) . ':00';
        $entry->details = 'Demo work entry for ' . $date->format('Y-m-d');
        $entry->status = 'Active';
        $entry->created = date('Y-m-d H:i:s');

        $ok = $entry->Save();
        return $ok ? $entry : null;
    }

    /**
     * Create a leave request with proper EmployeeLeaveDays records
     * Ensures the leave falls entirely within a single leave period
     */
    private static function createMockLeaveRequestWithDays(int $employeeId, array $leaveTypes, array $leavePeriods, bool $isFuture = false): ?array
    {
        // Note: Class is EmployeeLeaveDay (singular), table is EmployeeLeaveDays (plural)
        if (!class_exists('Leaves\Common\Model\EmployeeLeave') || !class_exists('Leaves\Common\Model\EmployeeLeaveDay')) {
            return null;
        }

        if (empty($leavePeriods)) {
            return null;
        }

        $leaveTypeId = $leaveTypes[array_rand($leaveTypes)];

        // Past leaves use [periodStart .. today]; future leaves use
        // [tomorrow .. periodEnd].
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = (clone $today)->modify('+1 day');

        // Only pick from periods that can actually hold a date in the requested
        // direction. Without this, future requests keep landing on long-past
        // periods (most demo periods are historical) and almost never succeed.
        $eligiblePeriods = array_values(array_filter($leavePeriods, function ($p) use ($today, $tomorrow, $isFuture) {
            if ($isFuture) {
                return new \DateTime($p['date_end']) >= $tomorrow;
            }
            return new \DateTime($p['date_start']) <= $today;
        }));
        if (empty($eligiblePeriods)) {
            return null;
        }

        $leavePeriod = $eligiblePeriods[array_rand($eligiblePeriods)];
        $leavePeriodId = $leavePeriod['id'];
        $periodStart = new \DateTime($leavePeriod['date_start']);
        $periodEnd = new \DateTime($leavePeriod['date_end']);

        if ($isFuture) {
            $rangeStart = clone $today;
            $rangeStart->modify('+1 day');
            if ($periodStart > $rangeStart) {
                $rangeStart = clone $periodStart;
            }
            $rangeEnd = clone $periodEnd;
            // This period has no room left in the future (it already ended).
            if ($rangeStart > $rangeEnd) {
                return null;
            }
        } else {
            $rangeStart = clone $periodStart;
            $rangeEnd = $periodEnd < $today ? clone $periodEnd : clone $today;
            // The period hasn't started yet, or the range is invalid.
            if ($periodStart > $today || $rangeStart > $rangeEnd) {
                return null;
            }
        }

        // Calculate days available in the range
        $daysInRange = $rangeStart->diff($rangeEnd)->days;
        if ($daysInRange < 1) {
            return null;
        }

        // Random leave duration (1-5 days, but not exceeding the range)
        $maxLeaveDays = min(5, $daysInRange);
        $leaveDays = rand(1, $maxLeaveDays);

        // Pick a random start date within the range, ensuring there's room for the leave duration
        $maxStartOffset = max(0, $daysInRange - $leaveDays);
        $startOffset = rand(0, $maxStartOffset);

        $dateStart = clone $rangeStart;
        $dateStart->modify("+{$startOffset} days");

        $dateEnd = clone $dateStart;
        $dateEnd->modify("+" . ($leaveDays - 1) . " days");

        // Double-check that leave dates fall within the period
        if ($dateStart < $periodStart || $dateEnd > $periodEnd) {
            return null;
        }

        // Create the leave request
        $leaveClass = 'Leaves\Common\Model\EmployeeLeave';
        $leave = new $leaveClass();
        $leave->employee = $employeeId;
        $leave->leave_type = $leaveTypeId;
        $leave->leave_period = $leavePeriodId;
        $leave->date_start = $dateStart->format('Y-m-d');
        $leave->date_end = $dateEnd->format('Y-m-d');
        $leave->details = 'Demo leave request';
        // Upcoming leaves are realistically Pending/Approved (not Rejected).
        $leave->status = $isFuture
            ? ['Pending', 'Approved'][rand(0, 1)]
            : ['Pending', 'Approved', 'Rejected'][rand(0, 2)];

        $ok = $leave->Save();
        if (!$ok || !$leave->id) {
            return null;
        }

        // Create leave day records for each day of the leave
        // Class is EmployeeLeaveDay (singular)
        $leaveDayClass = 'Leaves\Common\Model\EmployeeLeaveDay';
        $leaveTypeOptions = ['Full Day', 'Half Day - Morning', 'Half Day - Afternoon'];
        $daysCreated = 0;

        $currentDate = clone $dateStart;
        while ($currentDate <= $dateEnd) {
            // Skip weekends
            if ($currentDate->format('N') < 6) {
                $leaveDay = new $leaveDayClass();
                $leaveDay->employee_leave = $leave->id;
                $leaveDay->leave_date = $currentDate->format('Y-m-d');
                // Most days are full day, some half days
                $leaveDay->leave_type = rand(1, 100) <= 80 ? 'Full Day' : $leaveTypeOptions[rand(1, 2)];

                if ($leaveDay->Save()) {
                    self::trackDemoEntry('EmployeeLeaveDays', $leaveDay->id, 'leave_day');
                    $daysCreated++;
                }
            }
            $currentDate->modify('+1 day');
        }

        return [
            'leave' => $leave,
            'daysCreated' => $daysCreated
        ];
    }

    /**
     * Get project IDs assigned to an employee via EmployeeProjects
     */
    private static function getEmployeeProjectIds(int $employeeId): array
    {
        $empProject = new EmployeeProject();
        $assignments = $empProject->Find('employee = ?', [$employeeId]);
        return array_map(function ($ep) {
            return $ep->project;
        }, $assignments);
    }

    // ==================== Employee Details Generation ====================

    /**
     * Generate employee details (career, education, certifications, languages, dependents, emergency contacts)
     */
    private static function generateEmployeeDetails(int $employeeId, array $departments, array $jobTitles, array $employmentStatuses): array
    {
        $details = [
            'career' => 0,
            'education' => 0,
            'certifications' => 0,
            'languages' => 0,
            'dependents' => 0,
            'emergencyContacts' => 0
        ];

        // Generate 1-3 career history entries
        $careerCount = rand(1, 3);
        for ($i = 0; $i < $careerCount; $i++) {
            $career = self::createMockCareerHistory($employeeId, $departments, $jobTitles, $employmentStatuses, $i);
            if ($career && $career->id) {
                self::trackDemoEntry('EmployeeCareer', $career->id, 'employee_data');
                $details['career']++;
            }
        }

        // Generate 1-2 education entries (if lookup data exists)
        $educations = self::getAvailableEducations();
        if (!empty($educations)) {
            $eduCount = rand(1, 2);
            for ($i = 0; $i < $eduCount; $i++) {
                $education = self::createMockEducation($employeeId, $educations);
                if ($education && $education->id) {
                    self::trackDemoEntry('EmployeeEducations', $education->id, 'employee_data');
                    $details['education']++;
                }
            }
        }

        // Generate 0-2 certifications (if lookup data exists)
        $certifications = self::getAvailableCertifications();
        if (!empty($certifications)) {
            $certCount = rand(0, 2);
            for ($i = 0; $i < $certCount; $i++) {
                $cert = self::createMockCertification($employeeId, $certifications);
                if ($cert && $cert->id) {
                    self::trackDemoEntry('EmployeeCertifications', $cert->id, 'employee_data');
                    $details['certifications']++;
                }
            }
        }

        // Generate 1-3 language entries (if lookup data exists)
        $languages = self::getAvailableLanguages();
        if (!empty($languages)) {
            $langCount = min(rand(1, 3), count($languages));
            $selectedLanguages = array_rand(array_flip($languages), $langCount);
            if (!is_array($selectedLanguages)) {
                $selectedLanguages = [$selectedLanguages];
            }
            foreach ($selectedLanguages as $langId) {
                $lang = self::createMockLanguage($employeeId, $langId);
                if ($lang && $lang->id) {
                    self::trackDemoEntry('EmployeeLanguages', $lang->id, 'employee_data');
                    $details['languages']++;
                }
            }
        }

        // Generate 0-3 dependents
        $depCount = rand(0, 3);
        for ($i = 0; $i < $depCount; $i++) {
            $dependent = self::createMockDependent($employeeId);
            if ($dependent && $dependent->id) {
                self::trackDemoEntry('EmployeeDependents', $dependent->id, 'employee_data');
                $details['dependents']++;
            }
        }

        // Generate 1-2 emergency contacts
        $contactCount = rand(1, 2);
        for ($i = 0; $i < $contactCount; $i++) {
            $contact = self::createMockEmergencyContact($employeeId);
            if ($contact && $contact->id) {
                self::trackDemoEntry('EmergencyContacts', $contact->id, 'employee_data');
                $details['emergencyContacts']++;
            }
        }

        return $details;
    }

    /**
     * Create a mock career history entry
     */
    private static function createMockCareerHistory(int $employeeId, array $departments, array $jobTitles, array $employmentStatuses, int $index): ?EmployeeCareer
    {
        // Calculate dates based on index (older entries for higher index)
        $yearsAgo = ($index + 1) * rand(1, 3);
        $duration = rand(1, 3); // 1-3 years in the role

        $dateStart = new \DateTime();
        $dateStart->modify("-{$yearsAgo} years");

        $dateEnd = null;
        if ($index > 0) {
            // Not the current position, so set an end date
            $dateEnd = clone $dateStart;
            $dateEnd->modify("+{$duration} years");
        }

        $career = new EmployeeCareer();
        $career->employee = $employeeId;
        $career->job_title = $jobTitles[array_rand($jobTitles)];
        $career->employment_status = $employmentStatuses[array_rand($employmentStatuses)];
        $career->department = $departments[array_rand($departments)];
        $career->date_start = $dateStart->format('Y-m-d');
        $career->date_end = $dateEnd ? $dateEnd->format('Y-m-d') : null;
        $career->details = self::$careerDetails[array_rand(self::$careerDetails)];
        $career->created = date('Y-m-d H:i:s');
        $career->updated = date('Y-m-d H:i:s');

        $ok = $career->Save();
        return $ok ? $career : null;
    }

    /**
     * Create a mock education entry
     */
    private static function createMockEducation(int $employeeId, array $educationIds): ?EmployeeEducation
    {
        // Random education dates (graduated 1-15 years ago, studied 3-5 years)
        $yearsAgoGraduated = rand(1, 15);
        $studyDuration = rand(3, 5);

        $dateEnd = new \DateTime();
        $dateEnd->modify("-{$yearsAgoGraduated} years");

        $dateStart = clone $dateEnd;
        $dateStart->modify("-{$studyDuration} years");

        $education = new EmployeeEducation();
        $education->education_id = $educationIds[array_rand($educationIds)];
        $education->employee = $employeeId;
        $education->institute = self::$universities[array_rand(self::$universities)];
        $education->date_start = $dateStart->format('Y-m-d');
        $education->date_end = $dateEnd->format('Y-m-d');

        $ok = $education->Save();
        return $ok ? $education : null;
    }

    /**
     * Create a mock certification entry
     */
    private static function createMockCertification(int $employeeId, array $certificationIds): ?EmployeeCertification
    {
        // Random certification dates (earned 0-10 years ago)
        $yearsAgo = rand(0, 10);

        $dateStart = new \DateTime();
        $dateStart->modify("-{$yearsAgo} years");

        // Certification validity (some may have expired, some ongoing)
        $dateEnd = null;
        if (rand(0, 1)) {
            $dateEnd = clone $dateStart;
            $dateEnd->modify("+" . rand(2, 5) . " years");
        }

        $cert = new EmployeeCertification();
        $cert->certification_id = $certificationIds[array_rand($certificationIds)];
        $cert->employee = $employeeId;
        $cert->institute = self::$universities[array_rand(self::$universities)];
        $cert->date_start = $dateStart->format('Y-m-d');
        $cert->date_end = $dateEnd ? $dateEnd->format('Y-m-d') : null;

        $ok = $cert->Save();
        return $ok ? $cert : null;
    }

    /**
     * Create a mock language entry
     */
    private static function createMockLanguage(int $employeeId, int $languageId): ?EmployeeLanguage
    {
        $proficiencies = self::$languageProficiencyLevels;

        $lang = new EmployeeLanguage();
        $lang->language_id = $languageId;
        $lang->employee = $employeeId;
        $lang->reading = $proficiencies[array_rand($proficiencies)];
        $lang->speaking = $proficiencies[array_rand($proficiencies)];
        $lang->writing = $proficiencies[array_rand($proficiencies)];
        $lang->understanding = $proficiencies[array_rand($proficiencies)];

        $ok = $lang->Save();
        return $ok ? $lang : null;
    }

    /**
     * Create a mock dependent entry
     */
    private static function createMockDependent(int $employeeId): ?EmployeeDependent
    {
        $relationship = self::$relationships[array_rand(self::$relationships)];

        // Generate age based on relationship
        switch ($relationship) {
            case 'Child':
                $yearsOld = rand(1, 18);
                break;
            case 'Spouse':
                $yearsOld = rand(25, 55);
                break;
            case 'Parent':
                $yearsOld = rand(50, 80);
                break;
            default:
                $yearsOld = rand(1, 60);
        }

        $dob = new \DateTime();
        $dob->modify("-{$yearsOld} years");

        $dependent = new EmployeeDependent();
        $dependent->employee = $employeeId;
        $dependent->name = self::$firstNames[array_rand(self::$firstNames)] . ' ' . self::$lastNames[array_rand(self::$lastNames)];
        $dependent->relationship = $relationship;
        $dependent->dob = $dob->format('Y-m-d');
        $dependent->id_number = strtoupper(substr(md5(uniqid()), 0, 10));

        $ok = $dependent->Save();
        return $ok ? $dependent : null;
    }

    /**
     * Create a mock emergency contact entry
     */
    private static function createMockEmergencyContact(int $employeeId): ?EmergencyContact
    {
        $contact = new EmergencyContact();
        $contact->employee = $employeeId;
        $contact->name = self::$firstNames[array_rand(self::$firstNames)] . ' ' . self::$lastNames[array_rand(self::$lastNames)];
        $contact->relationship = self::$emergencyRelationships[array_rand(self::$emergencyRelationships)];
        $contact->home_phone = '+1' . rand(2000000000, 9999999999);
        $contact->work_phone = rand(0, 1) ? '+1' . rand(2000000000, 9999999999) : '';
        $contact->mobile_phone = '+1' . rand(2000000000, 9999999999);

        $ok = $contact->Save();
        return $ok ? $contact : null;
    }

    /**
     * Get available education IDs from lookup table
     */
    private static function getAvailableEducations(): array
    {
        $edu = new Education();
        $edus = $edu->Find('1=1');
        return array_map(function ($e) {
            return $e->id;
        }, $edus);
    }

    /**
     * Get available certification IDs from lookup table
     */
    private static function getAvailableCertifications(): array
    {
        $cert = new Certification();
        $certs = $cert->Find('1=1');
        return array_map(function ($c) {
            return $c->id;
        }, $certs);
    }

    /**
     * Get available language IDs from lookup table
     */
    private static function getAvailableLanguages(): array
    {
        $lang = new Language();
        $langs = $lang->Find('1=1');
        return array_map(function ($l) {
            return $l->id;
        }, $langs);
    }
}
