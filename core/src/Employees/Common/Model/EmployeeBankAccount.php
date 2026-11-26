<?php
namespace Employees\Common\Model;

use Classes\BaseService;
use Classes\Crypt\IceCrypt;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

/**
 * Bank accounts an employee is paid into.
 *
 * Account numbers and IBANs are encrypted at rest with APP_SEC (the same key the
 * encrypted settings use). Only Admins see them in full; the owning employee sees
 * "****" plus the last four characters. Editing a record without touching a masked
 * value keeps the stored value.
 */
class EmployeeBankAccount extends BaseModel
{
    public $table = 'EmployeeBankAccounts';

    const MASK = '****';

    private static $secretFields = array(
        'account_number' => 'account_number_last4',
        'iban' => 'iban_last4',
    );

    public function getAdminAccess()
    {
        return array("get", "element", "add", "save", "delete");
    }

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array("get", "element", "add", "save", "delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('bank_accounts', 'user'),
        ];
    }

    public function executePreSaveActions($obj)
    {
        return $this->prepareForSave($obj, null);
    }

    public function executePreUpdateActions($obj)
    {
        $stored = new EmployeeBankAccount();
        $stored->Load('id = ?', array($obj->id));
        return $this->prepareForSave($obj, empty($stored->id) ? null : $stored);
    }

    public function executePostSaveActions($obj)
    {
        $this->keepSinglePrimary($obj);
    }

    public function executePostUpdateActions($obj)
    {
        $this->keepSinglePrimary($obj);
    }

    public function postProcessGetData($obj)
    {
        return $this->presentSecrets($obj);
    }

    public function postProcessGetElement($obj)
    {
        return $this->presentSecrets($obj);
    }

    public function postProcessGetList($obj)
    {
        return $this->presentSecrets($obj);
    }

    private function prepareForSave($obj, $stored)
    {
        if (empty($obj->account_holder) || empty($obj->bank_name)) {
            return new IceResponse(IceResponse::ERROR, 'Account holder and bank name are required');
        }

        foreach (self::$secretFields as $field => $last4Field) {
            $value = isset($obj->$field) ? trim((string) $obj->$field) : '';

            if ($value !== '' && strpos($value, '*') !== false) {
                // The masked value came back unchanged from the form: keep what is stored.
                $obj->$field = $stored ? $stored->$field : null;
                $obj->$last4Field = $stored ? $stored->$last4Field : null;
                continue;
            }

            if ($value === '') {
                $obj->$field = null;
                $obj->$last4Field = null;
                continue;
            }

            $encrypted = IceCrypt::encrypt($value, APP_SEC);
            if ($encrypted === false) {
                return new IceResponse(IceResponse::ERROR, 'Could not store the bank details securely');
            }
            $compact = preg_replace('/\s+/', '', $value);
            $obj->$field = $encrypted;
            $obj->$last4Field = substr($compact, -4);
        }

        if ($obj->is_primary !== 'Yes') {
            $obj->is_primary = 'No';
        }

        // Optional salary split. MySQL strict mode rejects '' for a DECIMAL column.
        $split = isset($obj->split_value) ? trim((string) $obj->split_value) : '';
        if ($split === '' || empty($obj->split_type)) {
            $obj->split_type = empty($obj->split_type) ? null : $obj->split_type;
            $obj->split_value = $split === '' ? null : $split;
        }
        if ($obj->split_value !== null && !is_numeric($obj->split_value)) {
            return new IceResponse(IceResponse::ERROR, 'Split value must be a number');
        }
        if ($obj->split_type === 'Percentage' && $obj->split_value !== null
            && ((float) $obj->split_value < 0 || (float) $obj->split_value > 100)
        ) {
            return new IceResponse(IceResponse::ERROR, 'Split percentage must be between 0 and 100');
        }

        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    private function keepSinglePrimary($obj)
    {
        if ($obj->is_primary !== 'Yes' || empty($obj->employee)) {
            return;
        }
        BaseService::getInstance()->getDB()->Execute(
            "UPDATE EmployeeBankAccounts SET is_primary = 'No' WHERE employee = ? AND id <> ?",
            array($obj->employee, $obj->id)
        );
    }

    private function presentSecrets($obj)
    {
        $user = BaseService::getInstance()->getCurrentUser();
        $showFull = !empty($user) && $user->user_level === 'Admin';

        foreach (self::$secretFields as $field => $last4Field) {
            if (empty($obj->$field)) {
                $obj->$field = '';
                continue;
            }
            if ($showFull) {
                $plain = IceCrypt::decrypt($obj->$field, APP_SEC);
                $obj->$field = $plain === false ? '' : $plain;
            } else {
                $obj->$field = self::MASK . (string) $obj->$last4Field;
            }
        }

        return $obj;
    }

    /**
     * A team list is not offered: managers have no access to bank details.
     */
    public function allowsSubordinateList()
    {
        return false;
    }
}
