<?php
namespace Classes\Migration;

use Model\Setting;

class v20200430_270008_partial_leave_setting extends AbstractMigration
{

    public function up()
    {

        $setting = new Setting();
        $setting->Load('name = ?', array('Leave: Limit Allowed Partial Leave Days'));
        if (empty($setting->id)) {
            $setting->name = 'Leave: Limit Allowed Partial Leave Days';
            $setting->value = '';
            $setting->description = 'Allow only a selected set of partial leave days (if empty we allow all partial leave day types)';
            $setting->meta = '["value", {"label":"Value","type":"select2multi","source": [["Full Day","Full Day"], ["Half Day - Morning","Half Day - Morning"], ["Half Day - Afternoon","Half Day - Afternoon"], ["1 Hour - Morning","1 Hour - Morning"], ["2 Hours - Morning","2 Hours - Morning"],[ "3 Hours - Morning","3 Hours - Morning"],[ "1 Hour - Afternoon","1 Hour - Afternoon"],[ "2 Hours - Afternoon","2 Hours - Afternoon"],[ "3 Hours - Afternoon","3 Hours - Afternoon"]]}]';
            $setting->category = 'Leave';
            $setting->Save();
        }

        return true;
    }

    public function down()
    {
        return true;
    }

}
