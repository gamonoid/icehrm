<?php

namespace Jobs\Admin\Api;

use Classes\BaseService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\IndustryData;
use Classes\RestEndPoint;
use Jobs\Common\Model\JobTitle;

/**
 * REST endpoints for the Jobs module. Currently: list industries and generate
 * job titles for a selected industry (from Classes\IndustryData). Admin-only.
 */
class JobsApiController extends IceApiController
{
    public function registerEndPoints()
    {
        // Industries available for the "generate job titles" action.
        self::register(REST_API_PATH . 'jobs/industries', self::GET, function () {
            if (!self::requireAdmin()) {
                return;
            }
            (new RestEndPoint())->sendResponse(
                new IceResponse(IceResponse::SUCCESS, IndustryData::getIndustries())
            );
        });

        // Generate job titles for an industry (body: { industry: <key> }).
        // Existing titles (matched by name, case-insensitive) are skipped.
        self::register(REST_API_PATH . 'jobs/generate-job-titles', self::POST, function () {
            if (!self::requireAdmin()) {
                return;
            }
            $body = json_decode(file_get_contents('php://input'), true);
            $industry = is_array($body) && isset($body['industry']) ? $body['industry'] : '';
            $deleteExisting = is_array($body) && !empty($body['deleteExisting']);

            if (!IndustryData::hasIndustry($industry)) {
                (new RestEndPoint())->sendResponse(
                    new IceResponse(IceResponse::ERROR, 'Please select a valid industry.')
                );
                return;
            }

            // Optionally remove existing job titles first — but only those NOT
            // assigned to any employee (assigned ones are protected, mirroring
            // JobTitle::executePreDeleteActions).
            $deleted = 0;
            $keptAssigned = 0;
            if ($deleteExisting) {
                $db = BaseService::getInstance()->getDB();
                $assignedRows = $db->Execute(
                    "SELECT DISTINCT job_title FROM Employees WHERE job_title IS NOT NULL AND job_title != ''"
                );
                $assignedIds = array();
                foreach ((is_array($assignedRows) ? $assignedRows : array()) as $r) {
                    $assignedIds[(string) $r['job_title']] = true;
                }
                foreach ((new JobTitle())->Find('1 = 1') as $existingTitle) {
                    if (isset($assignedIds[(string) $existingTitle->id])) {
                        $keptAssigned++;
                        continue;
                    }
                    $existingTitle->Delete();
                    $deleted++;
                }
            }

            $titles = IndustryData::getJobTitles($industry);
            $created = 0;
            $skipped = 0;
            foreach ($titles as $title) {
                $existing = new JobTitle();
                $existing->Load('LOWER(name) = ?', array(strtolower($title)));
                if (!empty($existing->id)) {
                    $skipped++;
                    continue;
                }
                $jobTitle = new JobTitle();
                $jobTitle->name = substr($title, 0, 100);
                $jobTitle->code = self::makeCode($title);
                $jobTitle->description = '';
                if ($jobTitle->Save()) {
                    $created++;
                } else {
                    $skipped++;
                }
            }

            (new RestEndPoint())->sendResponse(new IceResponse(IceResponse::SUCCESS, array(
                'created' => $created,
                'skipped' => $skipped,
                'deleted' => $deleted,
                'kept_assigned' => $keptAssigned,
                'total' => count($titles),
                'industry' => IndustryData::getIndustry($industry)['label'],
            )));
        });
    }

    /** Build a short (<=10 char) code from a title, e.g. "Software Engineer" -> "SE". */
    private static function makeCode($name)
    {
        $words = preg_split('/\s+/', trim($name));
        if (count($words) >= 2) {
            $code = '';
            foreach ($words as $w) {
                $code .= strtoupper(substr($w, 0, 1));
            }
        } else {
            $code = strtoupper(substr($name, 0, 4));
        }
        $code = preg_replace('/[^A-Z0-9]/', '', $code);
        return substr($code, 0, 10);
    }

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
}
