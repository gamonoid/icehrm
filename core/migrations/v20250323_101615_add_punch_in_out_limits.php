<?php
namespace Classes\Migration;

class v20250323_101615_add_punch_in_out_limits extends AbstractMigration {

    public function up() {
        $sql = [];
        
        // Generate time options for every 15 minutes
        $timeOptions = [];
        for ($hour = 0; $hour < 24; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 15) {
                $time = sprintf("%02d:%02d", $hour, $minute);
                $timeOptions[] = [$time, $time];
            }
        }

		$timeOptions[] = ['23:59', '23:59'];

		$metaData = ['value', [
			'label' => 'Value',
			'type' => 'select2',
			'source' => $timeOptions,
		]];

        $metaData = json_encode($metaData);
        
        $sql[] = "REPLACE INTO Settings (`name`, `value`, `description`, `meta`, `category`) 
                  VALUES (
                      'Attendance: Punch In Start Time',
                      '00:00',
                      'Define the earliest time employees can punch in for the day.',
                      '$metaData',
        			  'Attendance'
                  )";

		$sql[] = "REPLACE INTO Settings (`name`, `value`, `description`, `meta`, `category`) 
                  VALUES (
                      'Attendance: Punch In End Time',
                      '23:59',
                      'Define the latest time employees can punch in for the day.',
                      '$metaData',
        			  'Attendance'
                  )";

		$sql[] = "REPLACE INTO Settings (`name`, `value`, `description`, `meta`, `category`) 
                  VALUES (
                      'Attendance: Punch Out Start Time',
                      '00:00',
                      'Define the earliest time employees can punch out for the day.',
                      '$metaData',
        			  'Attendance'
                  )";

		$sql[] = "REPLACE INTO Settings (`name`, `value`, `description`, `meta`, `category`) 
                  VALUES (
                      'Attendance: Punch Out End Time',
                      '23:59',
                      'Define the latest time employees can punch out for the day.',
                      '$metaData',
        			  'Attendance'
                  )";

		$metaDataYesNo = '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]';
		$sql[] = "REPLACE INTO Settings (`name`, `value`, `description`, `meta`, `category`) 
                  VALUES (
                      'Attendance: Limit Punch in-Out Times',
                      '0',
                      'Limit the time period that employees can punch in and out.',
                      '$metaDataYesNo',
        			  'Attendance'
                  )";

		foreach ($sql as $query) {
			$this->executeQuery($query);
		}

        return true;
    }

    public function down() {
    }
}
