<?php
namespace Advance_reportsUser;

use Advance_reportsUser\Reports\UserReportRegistry;
use Classes\BaseService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;

class ApiController extends IceApiController
{
    public function registerEndPoints()
    {
        // Get all reports grouped by category
        self::register(
            REST_API_PATH . 'user/advance_reports/reports',
            self::GET,
            function ($pathParams = null) {
                $this->getReports();
            }
        );

        // Get a specific report definition
        self::register(
            REST_API_PATH . 'user/advance_reports/reports/(:any)',
            self::GET,
            function ($reportId) {
                $this->getReportDefinition($reportId);
            }
        );

        // Generate a report
        self::register(
            REST_API_PATH . 'user/advance_reports/reports/(:any)/generate',
            self::POST,
            function ($reportId) {
                $this->generateReport($reportId);
            }
        );

        // Get remote source data for form fields
        self::register(
            REST_API_PATH . 'user/advance_reports/source/(:any)',
            self::GET,
            function ($model) {
                $this->getRemoteSourceData($model);
            }
        );
    }

    private function getReports()
    {
        $restEndpoint = new RestEndPoint();
        $registry = UserReportRegistry::getInstance();
        $grouped = $registry->getReportsByGroup();

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $grouped));
    }

    private function getReportDefinition($reportId)
    {
        $restEndpoint = new RestEndPoint();
        $registry = UserReportRegistry::getInstance();
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
        $registry = UserReportRegistry::getInstance();
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
            'Client' => 'Projects\\Common\\Model\\Client',
            'Project' => 'Projects\\Common\\Model\\Project',
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
            if (!empty($items)) {
                foreach ($items as $item) {
                    $label = '';
                    if (isset($item->name)) {
                        $label = $item->name;
                    } elseif (isset($item->title)) {
                        $label = $item->title;
                    }

                    $result[] = [
                        'value' => $item->id,
                        'label' => $label,
                    ];
                }
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
