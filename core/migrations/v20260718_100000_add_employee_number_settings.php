<?php
namespace Classes\Migration;

use Model\Setting;

class v20260718_100000_add_employee_number_settings extends AbstractMigration
{
    public function up()
    {
        // Company: Employee Number Prefix (free-text) — used to build auto
        // generated employee numbers, e.g. "IC-" gives IC-0031.
        $prefix = new Setting();
        $prefix->Load('name = ?', array('Company: Employee Number Prefix'));
        if (empty($prefix->id)) {
            $prefix->name = 'Company: Employee Number Prefix';
            $prefix->value = 'IC-';
            $prefix->description = 'Prefix used when generating employee numbers (e.g. IC- gives IC-0031).';
            $prefix->meta = '';
            $prefix->category = 'Company';
            $prefix->setting_group = null;
            $prefix->setting_order = 10;
            $prefix->Save();
        }

        // Company: Generate Employee Numbers (yes/no). When Yes, the employee
        // number is auto-generated and the field is read-only on the form.
        $generate = new Setting();
        $generate->Load('name = ?', array('Company: Generate Employee Numbers'));
        if (empty($generate->id)) {
            $generate->name = 'Company: Generate Employee Numbers';
            $generate->value = '1';
            $generate->description = 'Automatically generate employee numbers instead of entering them manually.';
            $generate->meta = '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]';
            $generate->category = 'Company';
            $generate->setting_group = null;
            $generate->setting_order = 11;
            $generate->Save();
        }

        return true;
    }

    public function down()
    {
        foreach (array('Company: Employee Number Prefix', 'Company: Generate Employee Numbers') as $name) {
            $s = new Setting();
            $s->Load('name = ?', array($name));
            if (!empty($s->id)) {
                $s->Delete();
            }
        }
        return true;
    }
}
