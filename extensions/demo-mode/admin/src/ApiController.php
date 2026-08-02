<?php
namespace DemoModeAdmin;

use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Classes\SettingsManager;

class ApiController extends IceApiController
{
	public function registerEndPoints() {
        // Check if should show demo mode prompt (first-time user)
        self::register(
            REST_API_PATH . 'demo-mode/should-prompt', self::GET, function ($pathParams = null) {
                $response = DemoDataService::shouldShowDemoPrompt();
                (new RestEndPoint())->sendResponse($response);
        });

        // Get demo mode status
        self::register(
            REST_API_PATH . 'demo-mode/status', self::GET, function ($pathParams = null) {
                $enabled = DemoModeTracker::isDemoModeEnabled();
                (new RestEndPoint())->sendResponse(
                    new IceResponse(IceResponse::SUCCESS, ['enabled' => $enabled])
                );
        });

        // Enable demo mode
        self::register(
            REST_API_PATH . 'demo-mode/enable', self::POST, function ($pathParams = null) {
                $result = DemoModeTracker::enableDemoMode();
                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        $result ? IceResponse::SUCCESS : IceResponse::ERROR,
                        $result ? 'Demo mode enabled' : 'Failed to enable demo mode'
                    )
                );
        });

        // Disable demo mode
        self::register(
            REST_API_PATH . 'demo-mode/disable', self::POST, function ($pathParams = null) {
                $result = DemoModeTracker::disableDemoMode();
                (new RestEndPoint())->sendResponse(
                    new IceResponse(
                        $result ? IceResponse::SUCCESS : IceResponse::ERROR,
                        $result ? 'Demo mode disabled' : 'Failed to disable demo mode'
                    )
                );
        });

        // Generate all demo data at once (for first-time setup)
        self::register(
            REST_API_PATH . 'demo-mode/generate-all', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $futureLeaveOnly = !empty($data['futureLeaveOnly']);
                $response = DemoDataService::generateAllDemoData($futureLeaveOnly);
                $restEndpoint->sendResponse($response);
        });

        // Get demo data statistics
        self::register(
            REST_API_PATH . 'demo-mode/stats', self::GET, function ($pathParams = null) {
                $response = DemoDataService::getStats();
                (new RestEndPoint())->sendResponse($response);
        });

        // Generate employees
        self::register(
            REST_API_PATH . 'demo-mode/employees', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 10;
                $response = DemoDataService::generateEmployees($count);
                $restEndpoint->sendResponse($response);
        });

        // Generate attendance
        self::register(
            REST_API_PATH . 'demo-mode/attendance', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $daysBack = isset($data['daysBack']) ? (int)$data['daysBack'] : 30;
                $response = DemoDataService::generateAttendance($daysBack);
                $restEndpoint->sendResponse($response);
        });

        // Generate today's attendance for a percentage of active employees
        self::register(
            REST_API_PATH . 'demo-mode/attendance-today', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $percentage = isset($data['percentage']) ? (int)$data['percentage'] : 100;
                $response = DemoDataService::generateTodayAttendance($percentage);
                $restEndpoint->sendResponse($response);
        });

        // Generate dependents + emergency contacts for N random active employees
        self::register(
            REST_API_PATH . 'demo-mode/family-data', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 10;
                $response = DemoDataService::generateFamilyData($count);
                $restEndpoint->sendResponse($response);
        });

        // Generate random teams with members (team extension)
        self::register(
            REST_API_PATH . 'demo-mode/teams', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $teams = isset($data['teams']) ? (int)$data['teams'] : 5;
                $membersPerTeam = isset($data['membersPerTeam']) ? (int)$data['membersPerTeam'] : 6;
                $response = DemoDataService::generateTeams($teams, $membersPerTeam);
                $restEndpoint->sendResponse($response);
        });

        // Generate task lists (tasks extension + editor documents)
        self::register(
            REST_API_PATH . 'demo-mode/task-lists', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 8;
                $response = DemoDataService::generateTaskLists($count);
                $restEndpoint->sendResponse($response);
        });

        // Generate timesheets
        self::register(
            REST_API_PATH . 'demo-mode/timesheets', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $weeksBack = isset($data['weeksBack']) ? (int)$data['weeksBack'] : 4;
                $response = DemoDataService::generateTimesheets($weeksBack);
                $restEndpoint->sendResponse($response);
        });

        // Generate leave requests
        self::register(
            REST_API_PATH . 'demo-mode/leave', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 5;
                $futureOnly = !empty($data['futureOnly']);
                $response = DemoDataService::generateLeaveRequests($count, 0, $futureOnly);
                $restEndpoint->sendResponse($response);
        });

        // Generate payroll
        self::register(
            REST_API_PATH . 'demo-mode/payroll', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();

                if (empty($data['name']) || empty($data['dateStart']) || empty($data['dateEnd'])) {
                    $restEndpoint->sendResponse(
                        new IceResponse(IceResponse::ERROR, 'Name, dateStart, and dateEnd are required')
                    );
                    return;
                }

                $response = DemoDataService::generatePayroll(
                    $data['name'],
                    $data['dateStart'],
                    $data['dateEnd']
                );
                $restEndpoint->sendResponse($response);
        });

        // Delete all demo data
        self::register(
            REST_API_PATH . 'demo-mode/data', self::DELETE, function ($pathParams = null) {
                $response = DemoDataService::deleteAllDemoData();
                (new RestEndPoint())->sendResponse($response);
        });

        // Delete demo data by type
        self::register(
            REST_API_PATH . 'demo-mode/data/(:any)', self::DELETE, function ($dataType) {
                $response = DemoDataService::deleteDemoDataByType($dataType);
                (new RestEndPoint())->sendResponse($response);
        });

        // Generate expense requests (only if the Expenses module is installed)
        self::register(
            REST_API_PATH . 'demo-mode/expenses', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 3;
                $response = DemoDataService::generateExpenseRequests($count);
                $restEndpoint->sendResponse($response);
        });

        // Generate overtime requests (only if the Overtime module is installed)
        self::register(
            REST_API_PATH . 'demo-mode/overtime', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 3;
                $response = DemoDataService::generateOvertimeRequests($count);
                $restEndpoint->sendResponse($response);
        });

        // Generate job positions (only if the recruitment module is installed)
        self::register(
            REST_API_PATH . 'demo-mode/jobs', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 5;
                $response = DemoDataService::generateJobPositions($count);
                $restEndpoint->sendResponse($response);
        });

        // Generate candidates for demo job positions (recruitment module only)
        self::register(
            REST_API_PATH . 'demo-mode/candidates', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 3;
                $response = DemoDataService::generateCandidates($count);
                $restEndpoint->sendResponse($response);
        });

        // Generate performance reviews with linked goals and peer feedback
        // (only if the leave_and_performance Performance module is installed)
        self::register(
            REST_API_PATH . 'demo-mode/performance-reviews', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 10;
                $response = DemoDataService::generatePerformanceReviews($count);
                $restEndpoint->sendResponse($response);
        });

        // Generate clients
        self::register(
            REST_API_PATH . 'demo-mode/clients', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 5;
                $response = DemoDataService::generateClients($count);
                $restEndpoint->sendResponse($response);
        });

        // Generate projects
        self::register(
            REST_API_PATH . 'demo-mode/projects', self::POST, function ($pathParams = null) {
                $restEndpoint = new RestEndPoint();
                $data = $restEndpoint->getRequestBody();
                $count = isset($data['count']) ? (int)$data['count'] : 5;
                $response = DemoDataService::generateProjects($count);
                $restEndpoint->sendResponse($response);
        });
	}
}

