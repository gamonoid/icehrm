<?php
namespace TimeSheets\Rest;

use Classes\BaseService;
use Classes\Data\Query\DataQuery;
use Classes\Data\Query\Filter;
use Classes\FileService;
use Classes\IceConstants;
use Classes\IceResponse;
use Classes\PermissionManager;
use Classes\RestEndPoint;
use Classes\SettingsManager;
use Employees\Common\Model\Employee;
use Projects\Common\Model\Project;
use TimeSheets\Common\Model\EmployeeTimeEntry;
use TimeSheets\Common\Model\EmployeeTimeSheet;
use Users\Common\Model\User;
use Utils\CalendarTools;
use Utils\LogManager;

class TimesheetRestEndPoint extends RestEndPoint
{
    const ELEMENT_NAME = 'EmployeeTimeSheet';

    /**
     * Get current user's timesheets
     */
    public function getMyTimesheets(User $user)
    {
        $query = new DataQuery('EmployeeTimeSheet');
        $query->addFilter(new Filter('employee', $user->employee));
        $query->setOrderBy('date_start desc');

        $limit = self::DEFAULT_LIMIT;
        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $limit = intval($_GET['limit']);
        }
        $query->setLength($limit);

        // Optional status filter
        if (!empty($_GET['status'])) {
            $query->addFilter(new Filter('status', $_GET['status']));
        }

        return $this->listTimesheets($query);
    }

    /**
     * Get timesheet details by ID
     */
    public function getTimesheetDetails(User $user, $timesheetId)
    {
        $timesheet = new EmployeeTimeSheet();
        $timesheet->Load('id = ?', [$timesheetId]);

        if (empty($timesheet->id)) {
            return new IceResponse(IceResponse::ERROR, 'Timesheet not found', 404);
        }

        // Check permission
        if (!$this->canAccessTimesheet($user, $timesheet)) {
            return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_PERMISSION_DENIED, 403);
        }

        // Get time entries
        $timeEntry = new EmployeeTimeEntry();
        $entries = $timeEntry->Find('timesheet = ? order by date_start', [$timesheetId]);

        // Populate project names
        $entries = BaseService::getInstance()->populateMapping(
            $entries,
            ['project' => ['Project', 'id', 'name']]
        );

        // Get employee info
        $employee = new Employee();
        $employee->Load('id = ?', [$timesheet->employee]);
        $employee = FileService::getInstance()->updateSmallProfileImage($employee);

        // Build response
        $timesheetData = $this->cleanObject($timesheet);
        $timesheetData->total_time = $timesheet->getTotalTime();
        $timesheetData->total_minutes = $timesheet->getTotalTimeMinutes();
        $timesheetData->days = $this->getTimesheetDays($timesheet);

        $entriesData = [];
        foreach ($entries as $entry) {
            $entryData = $this->cleanObject($entry);
            $entryData->duration_minutes = $this->calculateDurationMinutes($entry->date_start, $entry->date_end);
            $entryData->duration_hours = round($entryData->duration_minutes / 60, 2);
            $entriesData[] = $entryData;
        }

        return new IceResponse(IceResponse::SUCCESS, [
            'timesheet' => $timesheetData,
            'entries' => $entriesData,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'image' => $employee->image
            ]
        ]);
    }

    /**
     * Create a timesheet for the current week (Sunday to Saturday)
     * Uses same logic as TimeSheetsInitialize::init
     */
    public function createCurrentWeekTimesheet(User $user)
    {
        $empId = $user->employee;

        if (empty($empId)) {
            return new IceResponse(IceResponse::ERROR, 'Employee not found', 404);
        }

        // Calculate current week boundaries (Sunday to Saturday)
        if (date('w', strtotime("now")) == 0) {
            $start = date("Y-m-d", strtotime("now"));
        } else {
            $start = date("Y-m-d", strtotime("last Sunday"));
        }

        if (date('w', strtotime("now")) == 6) {
            $end = date("Y-m-d", strtotime("now"));
        } else {
            $end = date("Y-m-d", strtotime("next Saturday"));
        }

        // Check if timesheet already exists
        $existingTimesheet = new EmployeeTimeSheet();
        $existingTimesheet->Load(
            "employee = ? and date_start = ? and date_end = ?",
            [$empId, $start, $end]
        );

        if ($existingTimesheet->date_start == $start && $existingTimesheet->employee == $empId) {
            // Return existing timesheet
            $timesheetData = $this->cleanObject($existingTimesheet);
            $timesheetData->total_time = $existingTimesheet->getTotalTime();
            $timesheetData->days = $this->getTimesheetDays($existingTimesheet);
            return new IceResponse(IceResponse::SUCCESS, [
                'timesheet' => $timesheetData,
                'created' => false,
                'message' => 'Timesheet already exists for current week'
            ]);
        }

        // Create new timesheet
        $timesheet = new EmployeeTimeSheet();
        $timesheet->employee = $empId;
        $timesheet->date_start = $start;
        $timesheet->date_end = $end;
        $timesheet->status = 'Pending';

        $ok = $timesheet->Save();
        if (!$ok) {
            LogManager::getInstance()->error('Error creating timesheet: ' . $timesheet->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, 'Error creating timesheet', 500);
        }

        $timesheetData = $this->cleanObject($timesheet);
        $timesheetData->days = $this->getTimesheetDays($timesheet);

        return new IceResponse(IceResponse::SUCCESS, [
            'timesheet' => $timesheetData,
            'created' => true
        ], 201);
    }

    /**
     * Create a timesheet for the previous week based on an existing timesheet
     * Uses same logic as TimeSheetsActionManager::createPreviousTimesheet
     */
    public function createPreviousWeekTimesheet(User $user, $timesheetId)
    {
        $employee = new Employee();
        $employee->Load('id = ?', [$user->employee]);

        if (empty($employee->id)) {
            return new IceResponse(IceResponse::ERROR, 'Employee not found', 404);
        }

        // Load reference timesheet
        $timesheet = new EmployeeTimeSheet();
        $timesheet->Load("id = ?", [$timesheetId]);

        if ($timesheet->id != $timesheetId) {
            return new IceResponse(IceResponse::ERROR, 'Timesheet not found', 404);
        }

        // Check permission
        if ($timesheet->employee != $employee->id && $user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "You don't have permissions to add this Timesheet", 403);
        }

        // Calculate previous week dates
        $end = date("Y-m-d", strtotime("last Saturday", strtotime($timesheet->date_start)));
        $start = date("Y-m-d", strtotime("last Sunday", strtotime($end)));

        // Check if timesheet already exists
        $tempTimeSheet = new EmployeeTimeSheet();
        $tempTimeSheet->Load("employee = ? and date_start = ?", [$employee->id, $start]);

        if ($employee->id == $tempTimeSheet->employee) {
            $timesheetData = $this->cleanObject($tempTimeSheet);
            $timesheetData->total_time = $tempTimeSheet->getTotalTime();
            $timesheetData->days = $this->getTimesheetDays($tempTimeSheet);
            return new IceResponse(IceResponse::SUCCESS, [
                'timesheet' => $timesheetData,
                'created' => false,
                'message' => 'Timesheet already exists for previous week'
            ]);
        }

        // Create new timesheet
        $newTimeSheet = new EmployeeTimeSheet();
        $newTimeSheet->employee = $employee->id;
        $newTimeSheet->date_start = $start;
        $newTimeSheet->date_end = $end;
        $newTimeSheet->status = "Pending";

        $ok = $newTimeSheet->Save();
        if (!$ok) {
            LogManager::getInstance()->error("Error creating time sheet: " . $newTimeSheet->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, "Error creating Timesheet", 500);
        }

        $timesheetData = $this->cleanObject($newTimeSheet);
        $timesheetData->days = $this->getTimesheetDays($newTimeSheet);

        return new IceResponse(IceResponse::SUCCESS, [
            'timesheet' => $timesheetData,
            'created' => true
        ], 201);
    }

    /**
     * Create a timesheet for the next week based on an existing timesheet
     * Uses same logic as TimeSheetsActionManager::createNextWeekTimesheet
     */
    public function createNextWeekTimesheet(User $user, $timesheetId)
    {
        $employee = new Employee();
        $employee->Load('id = ?', [$user->employee]);

        if (empty($employee->id)) {
            return new IceResponse(IceResponse::ERROR, 'Employee not found', 404);
        }

        // Load reference timesheet
        $timesheet = new EmployeeTimeSheet();
        $timesheet->Load("id = ?", [$timesheetId]);

        if ($timesheet->id != $timesheetId) {
            return new IceResponse(IceResponse::ERROR, 'Timesheet not found', 404);
        }

        // Check permission
        if ($timesheet->employee != $employee->id && $user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, "You don't have permissions to add this Timesheet", 403);
        }

        // Calculate next week dates
        $start = date("Y-m-d", strtotime("next Sunday", strtotime($timesheet->date_end)));
        $end = date("Y-m-d", strtotime("next Saturday", strtotime($start)));

        // Check if timesheet already exists
        $tempTimeSheet = new EmployeeTimeSheet();
        $tempTimeSheet->Load("employee = ? and date_start = ?", [$employee->id, $start]);

        if ($employee->id == $tempTimeSheet->employee) {
            $timesheetData = $this->cleanObject($tempTimeSheet);
            $timesheetData->total_time = $tempTimeSheet->getTotalTime();
            $timesheetData->days = $this->getTimesheetDays($tempTimeSheet);
            return new IceResponse(IceResponse::SUCCESS, [
                'timesheet' => $timesheetData,
                'created' => false,
                'message' => 'Timesheet already exists for next week'
            ]);
        }

        // Create new timesheet
        $newTimeSheet = new EmployeeTimeSheet();
        $newTimeSheet->employee = $employee->id;
        $newTimeSheet->date_start = $start;
        $newTimeSheet->date_end = $end;
        $newTimeSheet->status = "Pending";

        $ok = $newTimeSheet->Save();
        if (!$ok) {
            LogManager::getInstance()->error("Error creating time sheet: " . $newTimeSheet->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, "Error creating Timesheet", 500);
        }

        $timesheetData = $this->cleanObject($newTimeSheet);
        $timesheetData->days = $this->getTimesheetDays($newTimeSheet);

        return new IceResponse(IceResponse::SUCCESS, [
            'timesheet' => $timesheetData,
            'created' => true
        ], 201);
    }

    /**
     * Add a time entry to a timesheet
     */
    public function addTimeEntry(User $user, $timesheetId)
    {
        $body = $this->getRequestBody();

        // Load and validate timesheet
        $timesheet = new EmployeeTimeSheet();
        $timesheet->Load('id = ?', [$timesheetId]);

        if (empty($timesheet->id)) {
            return new IceResponse(IceResponse::ERROR, 'Timesheet not found', 404);
        }

        // Check permission
        if ($timesheet->employee != $user->employee && $user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_PERMISSION_DENIED, 403);
        }

        // Check timesheet status
        if ($timesheet->status === 'Approved') {
            return new IceResponse(IceResponse::ERROR, 'Cannot add entries to approved timesheet', 400);
        }

        // Validate required fields
        if (empty($body['date']) || empty($body['start_time']) || empty($body['end_time'])) {
            return new IceResponse(
                IceResponse::ERROR,
                'Required fields: date, start_time, end_time',
                400
            );
        }

        // Build datetime values
        $dateStart = $body['date'] . ' ' . $body['start_time'];
        $dateEnd = $body['date'] . ' ' . $body['end_time'];

        // Validate times
        if (strtotime($dateEnd) <= strtotime($dateStart)) {
            return new IceResponse(IceResponse::ERROR, 'End time must be after start time', 400);
        }

        // Check if date is within timesheet period
        $entryDate = $body['date'];
        if ($entryDate < $timesheet->date_start || $entryDate > $timesheet->date_end) {
            return new IceResponse(
                IceResponse::ERROR,
                'Date must be within timesheet period (' . $timesheet->date_start . ' to ' . $timesheet->date_end . ')',
                400
            );
        }

        // Validate project if provided
        if (!empty($body['project'])) {
            $project = new Project();
            $project->Load('id = ?', [$body['project']]);
            if (empty($project->id)) {
                return new IceResponse(IceResponse::ERROR, 'Project not found', 404);
            }
        }

        // Create time entry
        $timeEntry = new EmployeeTimeEntry();
        $timeEntry->employee = $timesheet->employee;
        $timeEntry->timesheet = $timesheetId;
        $timeEntry->project = !empty($body['project']) ? $body['project'] : null;
        $timeEntry->date_start = $dateStart;
        $timeEntry->date_end = $dateEnd;
        $timeEntry->time_start = $body['start_time'];
        $timeEntry->time_end = $body['end_time'];
        $timeEntry->details = $body['details'] ?? '';
        $timeEntry->status = 'Active';
        $timeEntry->created = date('Y-m-d H:i:s');

        // Run model validation (attendance cross-check)
        $validationResult = $timeEntry->validateSave($timeEntry);
        if ($validationResult->getStatus() !== IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::ERROR, $validationResult->getData(), 400);
        }

        $ok = $timeEntry->Save();
        if (!$ok) {
            LogManager::getInstance()->error('Error saving time entry: ' . $timeEntry->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, 'Error saving time entry', 500);
        }

        $entryData = $this->cleanObject($timeEntry);
        $entryData->duration_minutes = $this->calculateDurationMinutes($dateStart, $dateEnd);
        $entryData->duration_hours = round($entryData->duration_minutes / 60, 2);

        return new IceResponse(IceResponse::SUCCESS, $entryData, 201);
    }

    /**
     * Update a time entry
     */
    public function updateTimeEntry(User $user, $entryId)
    {
        $body = $this->getRequestBody();

        $timeEntry = new EmployeeTimeEntry();
        $timeEntry->Load('id = ?', [$entryId]);

        if (empty($timeEntry->id)) {
            return new IceResponse(IceResponse::ERROR, 'Time entry not found', 404);
        }

        // Load timesheet to check permissions and status
        $timesheet = new EmployeeTimeSheet();
        $timesheet->Load('id = ?', [$timeEntry->timesheet]);

        if ($timesheet->employee != $user->employee && $user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_PERMISSION_DENIED, 403);
        }

        if ($timesheet->status === 'Approved') {
            return new IceResponse(IceResponse::ERROR, 'Cannot modify entries in approved timesheet', 400);
        }

        // Update fields if provided
        if (!empty($body['date']) && !empty($body['start_time']) && !empty($body['end_time'])) {
            $timeEntry->date_start = $body['date'] . ' ' . $body['start_time'];
            $timeEntry->date_end = $body['date'] . ' ' . $body['end_time'];
            $timeEntry->time_start = $body['start_time'];
            $timeEntry->time_end = $body['end_time'];
        }

        if (isset($body['project'])) {
            $timeEntry->project = $body['project'] ?: null;
        }

        if (isset($body['details'])) {
            $timeEntry->details = $body['details'];
        }

        $ok = $timeEntry->Save();
        if (!$ok) {
            return new IceResponse(IceResponse::ERROR, 'Error updating time entry', 500);
        }

        $entryData = $this->cleanObject($timeEntry);
        $entryData->duration_minutes = $this->calculateDurationMinutes($timeEntry->date_start, $timeEntry->date_end);
        $entryData->duration_hours = round($entryData->duration_minutes / 60, 2);

        return new IceResponse(IceResponse::SUCCESS, $entryData);
    }

    /**
     * Delete a time entry
     */
    public function deleteTimeEntry(User $user, $entryId)
    {
        $timeEntry = new EmployeeTimeEntry();
        $timeEntry->Load('id = ?', [$entryId]);

        if (empty($timeEntry->id)) {
            return new IceResponse(IceResponse::ERROR, 'Time entry not found', 404);
        }

        // Load timesheet to check permissions and status
        $timesheet = new EmployeeTimeSheet();
        $timesheet->Load('id = ?', [$timeEntry->timesheet]);

        if ($timesheet->employee != $user->employee && $user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_PERMISSION_DENIED, 403);
        }

        if ($timesheet->status === 'Approved') {
            return new IceResponse(IceResponse::ERROR, 'Cannot delete entries from approved timesheet', 400);
        }

        $timeEntry->Delete();

        return new IceResponse(IceResponse::SUCCESS, ['message' => 'Time entry deleted']);
    }

    /**
     * Submit a timesheet for approval
     */
    public function submitTimesheet(User $user, $timesheetId)
    {
        return $this->changeTimesheetStatus($user, $timesheetId, 'Submitted');
    }

    /**
     * Get submitted timesheets from direct reports (for managers)
     */
    public function getDirectReportsTimesheets(User $user)
    {
        $currentEmployee = new Employee();
        $currentEmployee->Load('id = ?', [$user->employee]);

        if (empty($currentEmployee->id)) {
            return new IceResponse(IceResponse::ERROR, 'Employee not found', 404);
        }

        // Find direct reports
        $employee = new Employee();
        $directReports = $employee->Find('supervisor = ? AND status = ?', [$currentEmployee->id, 'Active']);

        if (empty($directReports)) {
            return new IceResponse(IceResponse::SUCCESS, ['data' => []]);
        }

        $directReportIds = array_map(function ($emp) {
            return $emp->id;
        }, $directReports);

        $query = new DataQuery('EmployeeTimeSheet');
        $query->addFilter(new Filter('employee', $directReportIds, 'in'));
        $query->setOrderBy('date_start desc');

        // Optional status filter (default to Submitted for approval workflow)
        $status = $_GET['status'] ?? null;
        if (!empty($status)) {
            $query->addFilter(new Filter('status', $status));
        }

        $limit = self::DEFAULT_LIMIT;
        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $limit = intval($_GET['limit']);
        }
        $query->setLength($limit);

        return $this->listTimesheets($query, true);
    }

    /**
     * Get pending timesheets from direct reports
     */
    public function getPendingTimesheets(User $user)
    {
        $_GET['status'] = 'Submitted';
        return $this->getDirectReportsTimesheets($user);
    }

    /**
     * Approve a timesheet
     */
    public function approveTimesheet(User $user, $timesheetId)
    {
        return $this->changeTimesheetStatus($user, $timesheetId, 'Approved');
    }

    /**
     * Reject a timesheet
     */
    public function rejectTimesheet(User $user, $timesheetId)
    {
        return $this->changeTimesheetStatus($user, $timesheetId, 'Rejected');
    }

    /**
     * Get available projects for time entries
     */
    public function getProjects(User $user)
    {
        $allProjectsAllowed = SettingsManager::getInstance()->getSetting(
            'Projects: Make All Projects Available to Employees'
        ) == '1';

        $project = new Project();

        if ($allProjectsAllowed || $user->user_level === 'Admin') {
            $projects = $project->Find('status = ? order by name', ['Active']);
        } else {
            $projects = $project->Find(
                'id in (select project from EmployeeProjects where employee = ?) and status = ? order by name',
                [$user->employee, 'Active']
            );
        }

        $projectList = [];
        foreach ($projects as $p) {
            $projectList[] = [
                'id' => $p->id,
                'name' => $p->name
            ];
        }

        return new IceResponse(IceResponse::SUCCESS, ['data' => $projectList]);
    }

    // ==================== Helper Methods ====================

    /**
     * Change timesheet status with proper validation
     */
    private function changeTimesheetStatus(User $user, $timesheetId, $newStatus)
    {
        $timesheet = new EmployeeTimeSheet();
        $timesheet->Load('id = ?', [$timesheetId]);

        if (empty($timesheet->id)) {
            return new IceResponse(IceResponse::ERROR, 'Timesheet not found', 404);
        }

        $employee = new Employee();
        $employee->Load('id = ?', [$user->employee]);

        // Permission check
        if ($newStatus === 'Submitted') {
            // Only owner can submit
            if ($timesheet->employee != $user->employee && $user->user_level !== 'Admin') {
                return new IceResponse(IceResponse::ERROR, self::RESPONSE_ERR_PERMISSION_DENIED, 403);
            }
        } else {
            // Approve/Reject - must be supervisor or admin
            $timesheetEmployee = new Employee();
            $timesheetEmployee->Load('id = ?', [$timesheet->employee]);

            if ($timesheetEmployee->supervisor != $user->employee && $user->user_level !== 'Admin') {
                return new IceResponse(
                    IceResponse::ERROR,
                    'Only the supervisor can approve or reject this timesheet',
                    403
                );
            }
        }

        $oldStatus = $timesheet->status;

        // Auto-approve if admin without supervisor
        if ($newStatus === 'Submitted' && $user->user_level === 'Admin' && empty($employee->supervisor)) {
            $newStatus = 'Approved';
        }

        if ($oldStatus === $newStatus) {
            return new IceResponse(IceResponse::SUCCESS, ['message' => 'Status unchanged']);
        }

        $timesheet->status = $newStatus;
        $ok = $timesheet->Save();

        if (!$ok) {
            LogManager::getInstance()->error('Error updating timesheet: ' . $timesheet->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, 'Error updating timesheet', 500);
        }

        // Audit log
        $timesheetEmployee = BaseService::getInstance()->getElement('Employee', $timesheet->employee, null, true);
        BaseService::getInstance()->audit(
            IceConstants::AUDIT_ACTION,
            "Timesheet [" . $timesheetEmployee->first_name . " " . $timesheetEmployee->last_name
            . " - " . date("M d, Y", strtotime($timesheet->date_start)) . " to "
            . date("M d, Y", strtotime($timesheet->date_end)) . "] status changed from: "
            . $oldStatus . " to: " . $newStatus
        );

        // Send notifications
        $this->sendStatusNotification($timesheet, $employee, $oldStatus, $newStatus);

        $timesheetData = $this->cleanObject($timesheet);
        $timesheetData->total_time = $timesheet->getTotalTime();

        return new IceResponse(IceResponse::SUCCESS, [
            'message' => "Timesheet {$newStatus}",
            'timesheet' => $timesheetData
        ]);
    }

    /**
     * Send notification when timesheet status changes
     */
    private function sendStatusNotification($timesheet, $actionEmployee, $oldStatus, $newStatus)
    {
        $dateRange = date("M d, Y", strtotime($timesheet->date_start))
            . " to " . date("M d, Y", strtotime($timesheet->date_end));

        if ($newStatus === 'Submitted' && $actionEmployee->id == $timesheet->employee) {
            // Notify supervisor
            $timesheetEmployee = new Employee();
            $timesheetEmployee->Load('id = ?', [$timesheet->employee]);

            if (!empty($timesheetEmployee->supervisor)) {
                $notificationMsg = $actionEmployee->first_name . " " . $actionEmployee->last_name
                    . " submitted timesheet for " . $dateRange;
                BaseService::getInstance()->notificationManager->addNotification(
                    $timesheetEmployee->supervisor,
                    $notificationMsg,
                    '{"type":"url","url":"g=modules&n=time_sheets&m=module_Time_Management#tabSubEmployeeTimeSheetAll"}',
                    IceConstants::NOTIFICATION_TIMESHEET
                );
            }
        } elseif ($newStatus === 'Approved' || $newStatus === 'Rejected') {
            // Notify timesheet owner
            $notificationMsg = $actionEmployee->first_name . " " . $actionEmployee->last_name
                . " " . strtolower($newStatus) . " your timesheet for " . $dateRange;
            BaseService::getInstance()->notificationManager->addNotification(
                $timesheet->employee,
                $notificationMsg,
                '{"type":"url","url":"g=modules&n=time_sheets&m=module_Time_Management#tabEmployeeTimeSheetApproved"}',
                IceConstants::NOTIFICATION_TIMESHEET
            );
        }
    }

    /**
     * List timesheets with common formatting
     */
    private function listTimesheets(DataQuery $query, $includeEmployee = false)
    {
        $page = isset($_GET['page']) && intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;
        $limit = $query->getLength() ?? self::DEFAULT_LIMIT;

        $query->setStartPage(($page - 1) * $limit);

        $data = \Classes\Data\DataReader::getData($query);
        $total = \Classes\Data\DataReader::getDataCount($query);

        $timesheets = [];
        foreach ($data as $item) {
            $ts = new EmployeeTimeSheet();
            $ts->Load('id = ?', [$item->id]);

            $timesheetData = $this->cleanObject($item);
            $timesheetData->total_time = $ts->getTotalTime();
            $timesheetData->total_minutes = $ts->getTotalTimeMinutes();

            if ($includeEmployee) {
                $employee = new Employee();
                $employee->Load('id = ?', [$item->employee]);
                $timesheetData->employee_name = $employee->first_name . ' ' . $employee->last_name;
            }

            $timesheets[] = $timesheetData;
        }

        return new IceResponse(IceResponse::SUCCESS, [
            'data' => $timesheets,
            'total' => $total,
            'page' => $page,
            'nextPage' => ($page * $limit) < $total ? $page + 1 : null
        ]);
    }

    /**
     * Check if user can access a timesheet
     */
    private function canAccessTimesheet(User $user, EmployeeTimeSheet $timesheet)
    {
        // Admin can access all
        if ($user->user_level === 'Admin') {
            return true;
        }

        // Owner can access their own
        if ($timesheet->employee == $user->employee) {
            return true;
        }

        // Supervisor can access subordinate's timesheets
        $timesheetEmployee = new Employee();
        $timesheetEmployee->Load('id = ?', [$timesheet->employee]);
        if ($timesheetEmployee->supervisor == $user->employee) {
            return true;
        }

        return false;
    }

    /**
     * Get array of days within timesheet period
     */
    private function getTimesheetDays(EmployeeTimeSheet $timesheet)
    {
        $days = [];
        $current = strtotime($timesheet->date_start);
        $end = strtotime($timesheet->date_end);

        while ($current <= $end) {
            $days[] = [
                'date' => date('Y-m-d', $current),
                'display' => date('(D) d M', $current)
            ];
            $current = strtotime('+1 day', $current);
        }

        return $days;
    }

    /**
     * Calculate duration in minutes between two datetime strings
     */
    private function calculateDurationMinutes($start, $end)
    {
        $seconds = strtotime($end) - strtotime($start);
        return max(0, round($seconds / 60));
    }
}
