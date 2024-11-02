<?php
namespace Classes\Migration;

use FieldNames\Common\Model\FieldNameMapping;

class v20231119_340002_update_employee_fields extends AbstractMigration {

    public function up(){

        $fieldNameMapping = new FieldNameMapping();
        $fieldNameMapping->Load('name = ?', ['ssn_num']);
        if ($fieldNameMapping->name === 'ssn_num') {

            if ($fieldNameMapping->textMapped === 'SSN/NRIC'){
                $fieldNameMapping->textMapped = 'Social Insurance';
            }
            $fieldNameMapping->textOrig = 'Social Insurance';

            $fieldNameMapping->save();
        }

        $fieldNameMapping = new FieldNameMapping();
        $fieldNameMapping->Load('name = ?', ['nic_num']);
        if ($fieldNameMapping->name === 'nic_num') {

            if ($fieldNameMapping->textMapped === 'NIC'){
                $fieldNameMapping->textMapped = 'National ID';
            }
            $fieldNameMapping->textOrig = 'National ID';

            $fieldNameMapping->save();
        }

        $fieldNameMapping = new FieldNameMapping();
        $fieldNameMapping->Load('name = ?', ['driving_license']);
        if ($fieldNameMapping->name === 'driving_license') {

            if ($fieldNameMapping->textMapped === 'Driving License No'){
                $fieldNameMapping->textMapped = 'Driving License';
            }
            $fieldNameMapping->textOrig = 'Driving License';

            $fieldNameMapping->save();
        }

        $fieldNameMapping = new FieldNameMapping();
        $fieldNameMapping->Load('name = ?', ['other_id']);
        if ($fieldNameMapping->name === 'other_id') {

            if ($fieldNameMapping->textMapped === 'Other ID'){
                $fieldNameMapping->textMapped = 'Additional IDs';
            }
            $fieldNameMapping->textOrig = 'Additional IDs';

            $fieldNameMapping->save();
        }

        $sql = <<<'SQL'
ALTER TABLE Employees add COLUMN tax_id VARCHAR(50) DEFAULT NULL;
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Employees add COLUMN health_insurance VARCHAR(100) DEFAULT NULL;
SQL;
        $this->executeQuery($sql);

        $fieldNameMapping = new FieldNameMapping();
        $fieldNameMapping->Load('name = ?', ['tax_id']);
        if (empty($fieldNameMapping->id)) {
            $fieldNameMapping->type = 'Employee';
            $fieldNameMapping->name = 'tax_id';
            $fieldNameMapping->textOrig = 'Personal Tax ID';
            $fieldNameMapping->textMapped = 'Personal Tax ID';
            $fieldNameMapping->display = 'Form';
            $fieldNameMapping->Save();
        }

        $fieldNameMapping = new FieldNameMapping();
        $fieldNameMapping->Load('name = ?', ['health_insurance']);
        if (empty($fieldNameMapping->id)) {
            $fieldNameMapping->type = 'Employee';
            $fieldNameMapping->name = 'health_insurance';
            $fieldNameMapping->textOrig = 'Health Insurance';
            $fieldNameMapping->textMapped = 'Health Insurance';
            $fieldNameMapping->display = 'Form';
            $fieldNameMapping->Save();
        }


        $sql = <<<'SQL'
UPDATE FieldNameMappings set display = 'Form' where display = 'Table and Form';
SQL;
        $this->executeQuery($sql);

        $sql = <<<'SQL'
ALTER TABLE Employees add COLUMN timezone VARCHAR(100) DEFAULT NULL;
SQL;
        $this->executeQuery($sql);

        return true;
    }

    public function down(){

        return true;
    }

}
