<?php
namespace Employees\Rest;

use Classes\BaseService;
use Classes\CustomFieldManager;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;
use FieldNames\Common\Model\CustomField;
use Metadata\Common\Model\CustomFieldValue;
use Users\Common\Model\User;
use Utils\LogManager;

/**
 * REST API for employee custom fields.
 *
 * Two levels are managed here:
 *  - Field DEFINITIONS (CustomFields rows, type = 'Employee'): create / list / delete.
 *  - Field VALUES (CustomFieldValues rows) for a given employee: set / read / delete.
 *
 * All mutating operations are admin-only. Deleting a definition cascades to its values
 * (CustomField::executePostDeleteActions).
 *
 * Routes are registered in EmployeesAdminManager::setupRestEndPoints():
 *   GET    employees/custom-fields                    -> listFields
 *   POST   employees/custom-fields                    -> createField
 *   DELETE employees/custom-fields/(:num)             -> deleteField
 *   GET    employees/(:num)/custom-fields             -> getValues
 *   PUT    employees/(:num)/custom-fields/(:any)      -> setValue
 *   DELETE employees/(:num)/custom-fields/(:any)      -> deleteValue
 */
class CustomFieldRestEndPoint extends RestEndPoint
{
    const FIELD_TYPE = 'Employee';

    /** Field definition types the UI knows how to render. */
    private static $allowedFieldTypes = [
        'text', 'textarea', 'select', 'select2', 'select2multi',
        'date', 'datetime', 'time', 'fileupload',
    ];

    private function requireAdmin(User $user)
    {
        if (empty($user) || $user->user_level !== 'Admin') {
            return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
        }
        return null;
    }

    /**
     * List all employee custom field definitions.
     */
    public function listFields(User $user)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }

        $customField = new CustomField();
        $fields = $customField->Find('type = ? order by display_order, id', [self::FIELD_TYPE]);

        return new IceResponse(IceResponse::SUCCESS, array_map([$this, 'cleanFieldObject'], $fields));
    }

    /**
     * Create an employee custom field definition.
     * Body: { name, field_type, field_label, field_options?, field_validation?,
     *         display?, display_section?, display_order? }
     */
    public function createField(User $user)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }

        $body = $this->getRequestBody();
        if (empty($body['name']) || empty($body['field_type']) || empty($body['field_label'])) {
            return new IceResponse(
                IceResponse::ERROR,
                'name, field_type and field_label are required',
                400
            );
        }

        $name = trim($body['name']);
        // The CustomFields.name column is varchar(20); reject early with a clear message.
        if (strlen($name) > 20) {
            return new IceResponse(IceResponse::ERROR, 'name must be 20 characters or fewer', 400);
        }
        if (!in_array($body['field_type'], self::$allowedFieldTypes, true)) {
            return new IceResponse(
                IceResponse::ERROR,
                'field_type must be one of: '.implode(', ', self::$allowedFieldTypes),
                400
            );
        }

        $options = $body['field_options'] ?? null;
        if (is_array($options)) {
            $options = json_encode($options);
        }

        $display = $body['display'] ?? 'Form';
        if (!in_array($display, ['Form', 'Table and Form', 'Hidden'], true)) {
            $display = 'Form';
        }

        $field = new CustomField();
        $field->type = self::FIELD_TYPE;
        $field->name = $name;
        $field->field_type = $body['field_type'];
        $field->field_label = $body['field_label'];
        $field->field_validation = $body['field_validation'] ?? null;
        $field->field_options = $options;
        $field->display = $display;
        $field->display_section = $body['display_section'] ?? null;
        $field->display_order = isset($body['display_order']) ? intval($body['display_order']) : 0;
        $field->created = date('Y-m-d H:i:s');
        $field->updated = date('Y-m-d H:i:s');

        // Reuse the model's own duplicate/reserved-name validation.
        $validation = $field->validateSave($field);
        if ($validation->getStatus() === IceResponse::ERROR) {
            return new IceResponse(IceResponse::ERROR, $validation->getData(), 400);
        }

        if (!$field->Save()) {
            LogManager::getInstance()->error('Error creating custom field: '.$field->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, 'Could not create custom field', 500);
        }

        return new IceResponse(IceResponse::SUCCESS, $this->cleanFieldObject($field));
    }

    /**
     * Delete an employee custom field definition by id (cascades to its values).
     */
    public function deleteField(User $user, $id)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }

        $field = new CustomField();
        $field->Load('id = ? and type = ?', [$id, self::FIELD_TYPE]);
        if (empty($field->id) || $field->id != $id) {
            return new IceResponse(IceResponse::ERROR, 'Custom field not found', 404);
        }

        // Delete through BaseService so executePostDeleteActions() runs — it cascades to
        // the field's CustomFieldValues — and the delete is audited. A raw model Delete()
        // skips both.
        $response = BaseService::getInstance()->deleteElement('CustomField', $id);
        if ($response->getStatus() !== IceResponse::SUCCESS) {
            return new IceResponse(IceResponse::ERROR, $response->getData(), 500);
        }

        return new IceResponse(IceResponse::SUCCESS, 'Custom field deleted');
    }

    /**
     * Read all custom field values for one employee.
     */
    public function getValues(User $user, $employeeId)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }

        $employeeCheck = $this->loadEmployee($employeeId);
        if ($employeeCheck->getStatus() === IceResponse::ERROR) {
            return $employeeCheck;
        }

        $values = (new CustomFieldManager())->getCustomFields(self::FIELD_TYPE, $employeeId);
        $out = [];
        foreach ($values as $v) {
            $out[] = ['name' => $v->name, 'value' => $v->value, 'updated' => $v->updated];
        }

        return new IceResponse(IceResponse::SUCCESS, $out);
    }

    /**
     * Set/update a custom field value for an employee.
     * Body: { value }
     */
    public function setValue(User $user, $employeeId, $name)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }

        $employeeCheck = $this->loadEmployee($employeeId);
        if ($employeeCheck->getStatus() === IceResponse::ERROR) {
            return $employeeCheck;
        }

        // The field must be defined for employees before a value can be set.
        $field = new CustomField();
        $field->Load('type = ? and name = ?', [self::FIELD_TYPE, $name]);
        if (empty($field->id) || $field->name !== $name) {
            return new IceResponse(IceResponse::ERROR, "Custom field '$name' is not defined", 404);
        }

        $body = $this->getRequestBody();
        if (!array_key_exists('value', (array)$body)) {
            return new IceResponse(IceResponse::ERROR, 'value is required', 400);
        }
        $value = $body['value'];
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $ok = (new CustomFieldManager())->addCustomField(self::FIELD_TYPE, $employeeId, $name, $value);
        if (!$ok) {
            return new IceResponse(IceResponse::ERROR, 'Could not save custom field value', 500);
        }

        return new IceResponse(IceResponse::SUCCESS, ['name' => $name, 'value' => $value]);
    }

    /**
     * Delete a custom field value for an employee (the definition is untouched).
     */
    public function deleteValue(User $user, $employeeId, $name)
    {
        $denied = $this->requireAdmin($user);
        if ($denied !== null) {
            return $denied;
        }

        $employeeCheck = $this->loadEmployee($employeeId);
        if ($employeeCheck->getStatus() === IceResponse::ERROR) {
            return $employeeCheck;
        }

        $value = new CustomFieldValue();
        $value->Load(
            'type = ? and name = ? and object_id = ?',
            [self::FIELD_TYPE, $name, $employeeId]
        );
        if (empty($value->id)) {
            return new IceResponse(IceResponse::ERROR, 'Custom field value not found', 404);
        }

        if (!$value->Delete()) {
            LogManager::getInstance()->error('Error deleting custom field value: '.$value->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, 'Could not delete custom field value', 500);
        }

        return new IceResponse(IceResponse::SUCCESS, 'Custom field value deleted');
    }

    private function loadEmployee($employeeId)
    {
        $employee = new Employee();
        $employee->Load('id = ?', [$employeeId]);
        if (empty($employee->id) || $employee->id != $employeeId) {
            return new IceResponse(IceResponse::ERROR, 'Employee not found', 404);
        }
        return new IceResponse(IceResponse::SUCCESS, $employee);
    }

    private function cleanFieldObject($field)
    {
        return [
            'id' => $field->id,
            'type' => $field->type,
            'name' => $field->name,
            'field_type' => $field->field_type,
            'field_label' => $field->field_label,
            'field_validation' => $field->field_validation,
            'field_options' => $field->field_options,
            'display' => $field->display,
            'display_section' => $field->display_section,
            'display_order' => $field->display_order,
        ];
    }
}
