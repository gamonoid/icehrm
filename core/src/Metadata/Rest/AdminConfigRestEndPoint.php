<?php
namespace Metadata\Rest;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Users\Common\Model\User;

/**
 * Generic admin CRUD over a curated whitelist of configuration / master-data models
 * (leave types, holidays, pay grades, job titles, expense categories, …).
 *
 * These tables historically had no REST endpoints — only the session-only legacy admin CRUD.
 * This exposes list / describe / create-update / delete for them over the token API, reusing
 * the framework's own BaseService::addElement()/deleteElement() (so per-model validation,
 * pre/post-save hooks, audit logging and delete guards all still run).
 *
 * Only the whitelisted models below are reachable — arbitrary tables cannot be edited.
 * Admin only.
 */
class AdminConfigRestEndPoint extends RestEndPoint
{
    /**
     * Whitelisted configuration/master-data models (registered short model names).
     * Value = a short human label. Transactional/sensitive models (Employee, User,
     * EmployeeLeave, Attendance, Payroll data, …) are intentionally excluded.
     */
    private static $allowed = [
        // Leave configuration
        'LeaveType' => 'Leave types',
        'LeaveRule' => 'Leave rules',
        'LeavePeriod' => 'Leave periods',
        'LeaveGroup' => 'Leave groups',
        'HoliDay' => 'Holidays',
        'WorkDay' => 'Work days',
        // Employee master data
        'PayGrade' => 'Pay grades',
        'JobTitle' => 'Job titles',
        'EmploymentStatus' => 'Employment statuses',
        'EmployementType' => 'Employment types',
        'Nationality' => 'Nationalities',
        'Ethnicity' => 'Ethnicities',
        'ImmigrationStatus' => 'Immigration statuses',
        'EducationLevel' => 'Education levels',
        'ExperienceLevel' => 'Experience levels',
        'Skill' => 'Skills',
        'Language' => 'Languages',
        'Benifit' => 'Benefits',
        // Expense configuration
        'ExpensesCategory' => 'Expense categories',
        'ExpensesPaymentMethod' => 'Expense payment methods',
        // Overtime / recruitment / org
        'OvertimeCategory' => 'Overtime categories',
        'JobFunction' => 'Job functions',
        'Industry' => 'Industries',
        'CompanyStructure' => 'Company structure (departments)',
        'Timezone' => 'Timezones',
    ];

    private function requireAdmin(User $user)
    {
        if (empty($user) || $user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
        }
        return null;
    }

    private function resolveModel($model)
    {
        if (!isset(self::$allowed[$model])) {
            return null;
        }
        $fq = BaseService::getInstance()->getFullQualifiedModelClassName($model);
        if (!class_exists($fq)) {
            return null;
        }
        return $fq;
    }

    /** GET meta/config — list the models this endpoint can manage. */
    public function listModels(User $user)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }
        $out = [];
        foreach (self::$allowed as $name => $label) {
            $out[] = ['model' => $name, 'label' => $label];
        }
        return new IceResponse(IceResponse::SUCCESS, $out);
    }

    /** GET meta/config/{model} — list all rows of a config model. */
    public function listConfig(User $user, $model)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }
        $fq = $this->resolveModel($model);
        if ($fq === null) {
            return new IceResponse(IceResponse::ERROR, "Model '$model' is not a manageable config model", 400);
        }

        $obj = new $fq();
        $keys = array_keys($obj->getObjectKeys());
        $rows = $obj->Find('1 = 1 order by id');
        $out = [];
        foreach ($rows as $row) {
            $item = [];
            foreach ($keys as $k) {
                $item[$k] = $row->$k;
            }
            $out[] = $item;
        }
        return new IceResponse(IceResponse::SUCCESS, $out);
    }

    /** GET meta/config/{model}/describe — the field names (and form field defs) to send. */
    public function describeConfig(User $user, $model)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }
        $fq = $this->resolveModel($model);
        if ($fq === null) {
            return new IceResponse(IceResponse::ERROR, "Model '$model' is not a manageable config model", 400);
        }

        $obj = new $fq();
        $formFields = [];
        if (method_exists($obj, 'getFormFields')) {
            try {
                $formFields = $obj->getFormFields();
            } catch (\Throwable $e) {
                $formFields = [];
            }
        }
        return new IceResponse(IceResponse::SUCCESS, [
            'model' => $model,
            'label' => self::$allowed[$model],
            'columns' => array_keys($obj->getObjectKeys()),
            'formFields' => $formFields,
        ]);
    }

    /** POST meta/config/{model} — create (no id) or update (with id) a config row. */
    public function saveConfig(User $user, $model)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }
        $fq = $this->resolveModel($model);
        if ($fq === null) {
            return new IceResponse(IceResponse::ERROR, "Model '$model' is not a manageable config model", 400);
        }

        $body = $this->getRequestBody();
        if (!is_array($body)) {
            return new IceResponse(IceResponse::ERROR, 'A JSON object body is required', 400);
        }

        // addElement runs the model's validateSave, pre/post-save hooks and audit logging.
        return BaseService::getInstance()->addElement($model, $body);
    }

    /** DELETE meta/config/{model}/{id} — delete a config row (model delete guards apply). */
    public function deleteConfig(User $user, $model, $id)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }
        $fq = $this->resolveModel($model);
        if ($fq === null) {
            return new IceResponse(IceResponse::ERROR, "Model '$model' is not a manageable config model", 400);
        }

        return BaseService::getInstance()->deleteElement($model, $id);
    }
}
