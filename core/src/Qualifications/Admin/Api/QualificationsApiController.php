<?php

namespace Qualifications\Admin\Api;

use Classes\BaseService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\IndustryData;
use Classes\RestEndPoint;
use Qualifications\Common\Model\Certification;
use Qualifications\Common\Model\Skill;

/**
 * REST endpoints for the Qualifications module. Currently: list industries and
 * generate skills for a selected industry (from Classes\IndustryData). Admin-only.
 */
class QualificationsApiController extends IceApiController
{
    public function registerEndPoints()
    {
        // Industries available for the "generate skills" action.
        self::register(REST_API_PATH . 'qualifications/industries', self::GET, function () {
            if (!self::requireAdmin()) {
                return;
            }
            (new RestEndPoint())->sendResponse(
                new IceResponse(IceResponse::SUCCESS, IndustryData::getIndustries())
            );
        });

        // Generate skills for an industry (body: { industry: <key> }).
        // Existing skills (matched by name, case-insensitive) are skipped.
        self::register(REST_API_PATH . 'qualifications/generate-skills', self::POST, function () {
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

            $industryLabel = IndustryData::getIndustry($industry)['label'];

            // Optionally remove existing skills first — but only those NOT
            // attached to an employee (assigned ones are protected).
            $deleted = 0;
            $keptAssigned = 0;
            if ($deleteExisting) {
                $deleted = self::deleteUnused(new Skill(), 'EmployeeSkills', 'skill_id', $keptAssigned);
            }

            $skills = IndustryData::getSkills($industry);
            $created = 0;
            $skipped = 0;
            foreach ($skills as $skill) {
                $existing = new Skill();
                $existing->Load('LOWER(name) = ?', array(strtolower($skill)));
                if (!empty($existing->id)) {
                    $skipped++;
                    continue;
                }
                $model = new Skill();
                $model->name = substr($skill, 0, 100);
                $model->description = substr($skill . ' — a key skill in the ' . $industryLabel . ' industry.', 0, 400);
                if ($model->Save()) {
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
                'total' => count($skills),
                'industry' => $industryLabel,
            )));
        });

        // Generate certifications for an industry (body: { industry: <key> }).
        // Existing certifications (matched by name, case-insensitive) are skipped.
        self::register(REST_API_PATH . 'qualifications/generate-certifications', self::POST, function () {
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

            $industryLabel = IndustryData::getIndustry($industry)['label'];

            // Optionally remove existing certifications first — but only those NOT
            // attached to an employee (assigned ones are protected).
            $deleted = 0;
            $keptAssigned = 0;
            if ($deleteExisting) {
                $deleted = self::deleteUnused(
                    new Certification(),
                    'EmployeeCertifications',
                    'certification_id',
                    $keptAssigned
                );
            }

            $certifications = IndustryData::getCertifications($industry);
            $created = 0;
            $skipped = 0;
            foreach ($certifications as $certification) {
                $existing = new Certification();
                $existing->Load('LOWER(name) = ?', array(strtolower($certification)));
                if (!empty($existing->id)) {
                    $skipped++;
                    continue;
                }
                $model = new Certification();
                $model->name = substr($certification, 0, 100);
                $model->description = substr(
                    $certification . ' — a recognized professional certification in the '
                    . $industryLabel . ' industry.',
                    0,
                    400
                );
                if ($model->Save()) {
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
                'total' => count($certifications),
                'industry' => $industryLabel,
            )));
        });
    }

    /**
     * Delete every row of $model's table whose id is NOT referenced by $joinTable.
     * $keptAssigned is set (by reference) to how many were kept because they are
     * still attached to an employee. Returns the number deleted.
     */
    private static function deleteUnused($model, $joinTable, $joinColumn, &$keptAssigned)
    {
        $db = BaseService::getInstance()->getDB();
        $assignedRows = $db->Execute(
            "SELECT DISTINCT $joinColumn AS ref FROM $joinTable WHERE $joinColumn IS NOT NULL"
        );
        $assignedIds = array();
        foreach ((is_array($assignedRows) ? $assignedRows : array()) as $r) {
            $assignedIds[(string) $r['ref']] = true;
        }
        $deleted = 0;
        $keptAssigned = 0;
        foreach ($model->Find('1 = 1') as $existing) {
            if (isset($assignedIds[(string) $existing->id])) {
                $keptAssigned++;
                continue;
            }
            $existing->Delete();
            $deleted++;
        }
        return $deleted;
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
