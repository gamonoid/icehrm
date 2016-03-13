INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('System: Default Country', '0', 'Set the default Country','[ "value", {"label":"Country","type":"select2","remote-source":["Country","code","name"]}]');

INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Attendance: Overtime Calculation Class', 'BasicOvertimeCalculator', 'Set the method used to calculate overtime','["value", {"label":"Value","type":"select","source":[["BasicOvertimeCalculator","BasicOvertimeCalculator"],["CaliforniaOvertimeCalculator","CaliforniaOvertimeCalculator"]]}]');

INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Attendance: Overtime Start Hour', '8', 'Overtime calculation will start after an employee work this number of hours per day, 0 to indicate no overtime', ''),
  ('Attendance: Double time Start Hour', '12', 'Double time calculation will start after an employee work this number of hours per day, 0 to indicate no double time', '');

INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Attendance: Work Week Start Day', '0', 'Set the starting day of the work week','["value", {"label":"Value","type":"select","source":[["0","Sunday"],["1","Monday"],["2","Tuesday"],["3","Wednesday"],["4","Thursday"],["5","Friday"],["6","Saturday"]]}]');

INSERT INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`) VALUES
('Overtime Report', 'This report list all employee attendance entries by employee with overtime calculations', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'OvertimeReport', '["employee","date_start","date_end"]', 'Class','Time Management');

INSERT INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`) VALUES
('Overtime Summary Report', 'This report list all employee attendance entries by employee with overtime calculation summary', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'OvertimeSummaryReport', '["employee","date_start","date_end"]', 'Class','Time Management');

Alter table `Employees` modify column `joined_date` date default '0000-00-00';
Alter table `Employees` modify column `confirmation_date` date default '0000-00-00';
Alter table `Employees` modify column `termination_date` date default '0000-00-00';
Alter table `Employees` modify column `birthday` date default '0000-00-00';
