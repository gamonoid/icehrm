<?php
namespace Advance_reportsAdmin;

use Advance_reportsAdmin\Reports\ReportRegistry;
use Classes\BaseService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;

class ApiController extends IceApiController
{
    public function registerEndPoints()
    {
        // Every endpoint below is Admin-only, matching "user_levels": ["Admin"] in
        // meta.json. RestApiAuthGate authenticates but performs no authorization, so
        // without these checks any authenticated user — down to Employee — could
        // generate company-wide reports (EmployeeDetailsReport exposes salary and
        // identification fields) or enumerate every employee via the source endpoint.

        // Get all reports grouped by category
        self::register(
            REST_API_PATH . 'advance_reports/reports',
            self::GET,
            function ($pathParams = null) {
                if (!self::requireAdmin()) {
                    return;
                }
                $this->getReports();
            }
        );

        // Get a specific report definition
        self::register(
            REST_API_PATH . 'advance_reports/reports/(:any)',
            self::GET,
            function ($reportId) {
                if (!self::requireAdmin()) {
                    return;
                }
                $this->getReportDefinition($reportId);
            }
        );

        // Generate a report
        self::register(
            REST_API_PATH . 'advance_reports/reports/(:any)/generate',
            self::POST,
            function ($reportId) {
                if (!self::requireAdmin()) {
                    return;
                }
                $this->generateReport($reportId);
            }
        );

        // Get remote source data for form fields
        self::register(
            REST_API_PATH . 'advance_reports/source/(:any)',
            self::GET,
            function ($model) {
                if (!self::requireAdmin()) {
                    return;
                }
                $this->getRemoteSourceData($model);
            }
        );
    }

    /**
     * Ensure the caller is an Admin. On failure emits a 403 and returns false.
     *
     * Mirrors ConnectionApiController::requireAdmin() — the same shape is used by the
     * other admin-only REST controllers.
     */
    private static function requireAdmin()
    {
        $user = BaseService::getInstance()->getCurrentUser();
        if (!empty($user) && isset($user->user_level) && $user->user_level === 'Admin') {
            return true;
        }

        (new RestEndPoint())->sendResponse(
            new IceResponse(IceResponse::ERROR, 'Admin access required.', 403)
        );

        return false;
    }

    private function getReports()
    {
        $restEndpoint = new RestEndPoint();
        $registry = ReportRegistry::getInstance();
        $grouped = $registry->getReportsByGroup();

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $grouped));
    }

    private function getReportDefinition($reportId)
    {
        $restEndpoint = new RestEndPoint();
        $registry = ReportRegistry::getInstance();
        $report = $registry->getReport($reportId);

        if (!$report) {
            $restEndpoint->sendResponse(
                new IceResponse(IceResponse::ERROR, 'Report not found'),
                404
            );
            return;
        }

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $report->getDefinition()));
    }

    private function generateReport($reportId)
    {
        $restEndpoint = new RestEndPoint();
        $registry = ReportRegistry::getInstance();
        $report = $registry->getReport($reportId);

        if (!$report) {
            $restEndpoint->sendResponse(
                new IceResponse(IceResponse::ERROR, 'Report not found'),
                404
            );
            return;
        }

        $params = $restEndpoint->getRequestBody();

        try {
            $data = $report->getReportData($params);

            if (empty($data)) {
                $restEndpoint->sendResponse(
                    new IceResponse(IceResponse::SUCCESS, [
                        'success' => true,
                        'message' => 'No data found for the given parameters',
                        'data' => [],
                        'count' => 0,
                    ])
                );
                return;
            }

            // Generate CSV file
            $result = $report->generateCsv($data);

            $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, [
                'success' => true,
                'file' => $result['file'],
                'url' => $result['url'],
                'data' => $data,
                'count' => count($data) - 1, // Subtract header row
            ]));
        } catch (\Exception $e) {
            $restEndpoint->sendResponse(
                new IceResponse(IceResponse::ERROR, 'Error generating report: ' . $e->getMessage()),
                500
            );
        }
    }

    private function getRemoteSourceData($model)
    {
        $restEndpoint = new RestEndPoint();

        // Map model names to actual class paths
        $modelMappings = [
            'Employee' => 'Employees\\Common\\Model\\Employee',
            'CompanyStructure' => 'Company\\Common\\Model\\CompanyStructure',
            'EmploymentStatus' => 'Employees\\Common\\Model\\EmploymentStatus',
            'JobTitle' => 'Jobs\\Common\\Model\\JobTitle',
            'LeaveType' => 'Leaves\\Common\\Model\\LeaveType',
            'LeavePeriod' => 'Leaves\\Common\\Model\\LeavePeriod',
            'OvertimeCategory' => 'Overtime\\Common\\Model\\OvertimeCategory',
            'Project' => 'Projects\\Common\\Model\\Project',
            'Client' => 'Projects\\Common\\Model\\Client',
            'AssetType' => 'Assets\\Common\\Model\\AssetType',
            'ExpenseCategory' => 'Expenses\\Common\\Model\\ExpensesCategory',
            'PaymentMethod' => 'Expenses\\Common\\Model\\ExpensesPaymentMethod',
        ];

        if (!isset($modelMappings[$model])) {
            $restEndpoint->sendResponse(
                new IceResponse(IceResponse::ERROR, 'Unknown model: ' . $model),
                400
            );
            return;
        }

        $className = $modelMappings[$model];

        if (!class_exists($className)) {
            $restEndpoint->sendResponse(
                new IceResponse(IceResponse::ERROR, 'Model class not found: ' . $className),
                404
            );
            return;
        }

        try {
            $instance = new $className();
            $items = $instance->Find("1=1 ORDER BY id", []);

            $result = [];
            foreach ($items as $item) {
                $label = '';
                if (isset($item->name)) {
                    $label = $item->name;
                } elseif (isset($item->title)) {
                    $label = $item->title;
                } elseif (isset($item->first_name)) {
                    $label = $item->first_name . ' ' . ($item->last_name ?? '');
                }

                $result[] = [
                    'value' => $item->id,
                    'label' => $label,
                ];
            }

            $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $result));
        } catch (\Exception $e) {
            $restEndpoint->sendResponse(
                new IceResponse(IceResponse::ERROR, 'Error fetching data: ' . $e->getMessage()),
                500
            );
        }
    }
}
