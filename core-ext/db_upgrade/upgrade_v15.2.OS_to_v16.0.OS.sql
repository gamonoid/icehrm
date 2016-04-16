REPLACE INTO `Reports` (`id`, `name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`) VALUES
  (1, 'Employee Details Report', 'This report list all employee details and you can filter employees by department, employment status or job title', '[\r\n[ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}],\r\n[ "employment_status", {"label":"Employment Status","type":"select2","remote-source":["EmploymentStatus","id","name"],"allow-null":true}],\r\n[ "job_title", {"label":"Job Title","type":"select2","remote-source":["JobTitle","id","name"],"allow-null":true}]\r\n]', 'Select id, employee_id as ''Employee ID'',\r\nconcat(`first_name`,'' '',`middle_name`,'' '', `last_name`) as ''Name'',\r\n(SELECT name from Nationality where id = nationality) as ''Nationality'',\r\nbirthday as ''Birthday'',\r\ngender as ''Gender'',\r\nmarital_status as ''Marital Status'',\r\nssn_num as ''SSN Number'',\r\nnic_num as ''NIC Number'',\r\nother_id as ''Other IDs'',\r\ndriving_license as ''Driving License Number'',\r\n(SELECT name from EmploymentStatus where id = employment_status) as ''Employment Status'',\r\n(SELECT name from JobTitles where id = job_title) as ''Job Title'',\r\n(SELECT name from PayGrades where id = pay_grade) as ''Pay Grade'',\r\nwork_station_id as ''Work Station ID'',\r\naddress1 as ''Address 1'',\r\naddress2 as ''Address 2'',\r\ncity as ''City'',\r\n(SELECT name from Country where code = country) as ''Country'',\r\n(SELECT name from Province where id = province) as ''Province'',\r\npostal_code as ''Postal Code'',\r\nhome_phone as ''Home Phone'',\r\nmobile_phone as ''Mobile Phone'',\r\nwork_phone as ''Work Phone'',\r\nwork_email as ''Work Email'',\r\nprivate_email as ''Private Email'',\r\njoined_date as ''Joined Date'',\r\nconfirmation_date as ''Confirmation Date'',\r\n(SELECT title from CompanyStructures where id = department) as ''Department'',\r\n(SELECT concat(`first_name`,'' '',`middle_name`,'' '', `last_name`,'' [Employee ID:'',`employee_id`,'']'') from Employees e1 where e1.id = e.supervisor) as ''Supervisor'' \r\nFROM Employees e _where_', '["department","employment_status","job_title"]', 'Query', 'Employee Information'),
  (3, 'Employee Time Entry Report', 'This report list all employee time entries by employee, date range and project', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "project", {"label":"Project","type":"select","allow-null":true,"remote-source":["Project","id","name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'EmployeeTimesheetReport', '["employee","date_start","date_end","status"]', 'Class','Time Management'),
  (4, 'Employee Attendance Report', 'This report list all employee attendance entries by employee and date range', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'EmployeeAttendanceReport', '["employee","date_start","date_end"]', 'Class','Time Management'),
  (5, 'Employee Time Tracking Report', 'This report list employee working hours and attendance details for each day for a given period ', '[\r\n[ "employee", {"label":"Employee","type":"select2","allow-null":false,"remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'EmployeeTimeTrackReport', '["employee","date_start","date_end"]', 'Class','Time Management');

REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`) VALUES
  ('Active Employee Report', 'This report list employees who are currently active based on joined date and termination date ',
   '[\r\n[ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}]\r\n]',
   'ActiveEmployeeReport',
   '["department"]', 'Class','Employee Information');

REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`, `report_group`) VALUES
  ('New Hires Employee Report', 'This report list employees who are joined between given two dates ',
   '[[ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]',
   'NewHiresEmployeeReport',
   '["department","date_start","date_end"]', 'Class','Employee Information');

REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`, `report_group`) VALUES
  ('Terminated Employee Report', 'This report list employees who are terminated between given two dates ',
   '[[ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]',
   'TerminatedEmployeeReport',
   '["department","date_start","date_end"]', 'Class','Employee Information');




INSERT INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`) VALUES
  ('Employee Time Sheet Report', 'This report list all employee time sheets by employee and date range', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}],\r\n[ "status", {"label":"Status","allow-null":true,"null-label":"All Status","type":"select","source":[["Approved","Approved"],["Pending","Pending"],["Rejected","Rejected"]]}]\r\n]', 'EmployeeTimeSheetData', '["employee","date_start","date_end","status"]', 'Class','Time Management');


REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('LDAP: Enabled', '0',  '','["value", {"label":"Value","type":"select","source":[["0","No"],["1","Yes"]]}]'),
  ('LDAP: Server', '',  'LDAP Server IP or DNS',''),
  ('LDAP: Port', '389',  'LDAP Server Port',''),
  ('LDAP: Root DN', '',  'e.g: dc=mycompany,dc=net',''),
  ('LDAP: Manager DN', '',  'e.g: cn=admin,dc=mycompany,dc=net',''),
  ('LDAP: Manager Password', '',  'Password of the manager user',''),
  ('LDAP: Version 3', '1',  'Are you using LDAP v3','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]'),
  ('LDAP: User Filter', '',  'e.g: uid={}, we will replace {} with actual username provided by the user at the time of login','');




/* v15.0.PRO to v16.0.PRO */

ALTER TABLE `Users` ADD COLUMN `login_hash` varchar(64) default null;
ALTER TABLE `Users` ADD INDEX login_hash_index (`login_hash`);

INSERT INTO `ImmigrationStatus` VALUES
  (1,'Citizen'),
  (2,'Permanent Resident'),
  (3,'Work Permit Holder'),
  (4,'Dependant Pass Holder');

INSERT INTO `Ethnicity` VALUES
  (1,'White American'),
  (2,'Black or African American'),
  (3,'Native American'),
  (4,'Alaska Native'),
  (5,'Asian American'),
  (6,'Native Hawaiian'),
  (7,'Pacific Islander');

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Attendance: Overtime Calculation Class', 'BasicOvertimeCalculator', 'Set the method used to calculate overtime','["value", {"label":"Value","type":"select","source":[["BasicOvertimeCalculator","BasicOvertimeCalculator"],["CaliforniaOvertimeCalculator","CaliforniaOvertimeCalculator"]]}]');

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Attendance: Work Week Start Day', '0', 'Set the starting day of the work week','["value", {"label":"Value","type":"select","source":[["0","Sunday"],["1","Monday"],["2","Tuesday"],["3","Wednesday"],["4","Thursday"],["5","Friday"],["6","Saturday"]]}]');

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('System: Reset Module Names', '1', 'Select this to reset module names in Database','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
('Attendance: Overtime Start Hour', '8', 'Overtime calculation will start after an employee work this number of hours per day, 0 to indicate no overtime', ''),
('Attendance: Double time Start Hour', '12', 'Double time calculation will start after an employee work this number of hours per day, 0 to indicate no double time', ''),
('Api: REST Api Enabled', '1',  '','["value", {"label":"Value","type":"select","source":[["0","No"],["1","Yes"]]}]'),
('Api: REST Api Token', 'Click on edit icon',  '','["value", {"label":"Value","type":"placeholder"}]');

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('System: Allowed Countries', '0', 'Only these countries will be allowed in select boxes','["value", {"label":"Value","type":"select2multi","remote-source":["Country","id","name"]}]');

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('System: Allowed Currencies', '0', 'Only these currencies will be allowed in select boxes','["value", {"label":"Value","type":"select2multi","remote-source":["CurrencyType","id","code+name"]}]');

INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('System: Allowed Nationality', '', 'Only these nationalities will be allowed in select boxes','["value", {"label":"Value","type":"select2multi","remote-source":["Nationality","id","name"]}]');


REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`) VALUES
('Overtime Report', 'This report list all employee attendance entries by employee with overtime calculations', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'OvertimeReport', '["employee","date_start","date_end"]', 'Class','Time Management');

REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`) VALUES
('Overtime Summary Report', 'This report list all employee attendance entries by employee with overtime calculation summary', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'OvertimeSummaryReport', '["employee","date_start","date_end"]', 'Class','Time Management');

create table `EmployeeDataHistory` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) not null,
  `employee` bigint(20) NOT NULL,
  `field` varchar(100) not null,
  `old_value` varchar(500) default null,
  `new_value` varchar(500) default null,
  `description` varchar(800) default null,
  `user` bigint(20) NULL,
  `updated` timestamp default '0000-00-00 00:00:00',
  `created` timestamp default '0000-00-00 00:00:00',
  CONSTRAINT `Fk_EmployeeDataHistory_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeDataHistory_Users` FOREIGN KEY (`user`) REFERENCES `Users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  primary key  (`id`)
) engine=innodb default charset=utf8;

Alter table `Employees` modify column `joined_date` date default '0000-00-00';
Alter table `Employees` modify column `confirmation_date` date default '0000-00-00';
Alter table `Employees` modify column `termination_date` date default '0000-00-00';
Alter table `Employees` modify column `birthday` date default '0000-00-00';


REPLACE INTO `FieldNameMappings` (`type`, `name`, `textOrig`, `textMapped`, `display`) VALUES
  ('Employee', 'indirect_supervisors', 'Indirect Supervisors', 'Indirect Supervisors', 'Form');

Update Crons set time = (FLOOR( 1 + RAND( ) *58 )), type = 'Hourly' where name = 'Document Expire Alert';

CREATE TABLE `PayFrequency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB default charset=utf8;

INSERT INTO `PayFrequency` VALUES
  (1,'Bi Weekly'),
  (2,'Weekly'),
  (3,'Semi Monthly'),
  (4,'Monthly'),
  (5,'Yearly');


CREATE TABLE `PayrollColumnTemplates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `columns` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB default charset=utf8;

create table `Payroll` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NULL,
  `pay_period` bigint(20) NOT NULL,
  `department` bigint(20) NOT NULL,
  `column_template` bigint(20) NOT NULL,
  `columns` varchar(500) DEFAULT NULL,
  `date_start` DATE NULL default '0000-00-00',
  `date_end` DATE NULL default '0000-00-00',
  `status` enum('Draft','Completed','Processing') default 'Draft',
  primary key  (`id`)
) engine=innodb default charset=utf8;


CREATE TABLE `PayrollData` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payroll` bigint(20) NOT NULL,
  `employee` bigint(20) NOT NULL,
  `payroll_item` int(11) NOT NULL,
  `amount` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `PayrollDataUniqueKey` (`payroll`,`employee`,`payroll_item`),
  CONSTRAINT `Fk_PayrollData_Payroll` FOREIGN KEY (`payroll`) REFERENCES `Payroll` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB default charset=utf8;


CREATE TABLE `PayrollColumns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `calculation_hook` varchar(200) DEFAULT NULL,
  `salary_components` varchar(500) DEFAULT NULL,
  `deductions` varchar(500) DEFAULT NULL,
  `add_columns` varchar(500) DEFAULT NULL,
  `sub_columns` varchar(500) DEFAULT NULL,
  `colorder` int(11) DEFAULT NULL,
  `editable` enum('Yes','No') default 'Yes',
  `enabled` enum('Yes','No') default 'Yes',
  `default_value` varchar(25) DEFAULT NULL,
  `calculation_columns` varchar(500) DEFAULT NULL,
  `calculation_function` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB default charset=utf8;




INSERT INTO `PayrollColumns` (`id`,`name`,`calculation_hook`,`salary_components`,`deductions`,`add_columns`,`sub_columns`,`editable`) VALUES
  (1,'Total Hours','AttendanceUtil_getTimeWorkedHours','','','','','No'),
  (2,'Regular Hours','AttendanceUtil_getRegularWorkedHours','','','','','No'),
  (3,'Overtime Hours','AttendanceUtil_getOverTimeWorkedHours','','','','','No'),
  (4,'Leave Hours','LeaveUtil_getLeaveHours','','','','','No');

create table `PayrollEmployees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `pay_frequency` int(11) default null,
  `currency` bigint(20) NULL,
  `deduction_exemptions` varchar(250) default null,
  `deduction_allowed` varchar(250) default null,
  CONSTRAINT `Fk_PayrollEmployee_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  primary key  (`id`),
  unique key `PayrollEmployees_employee` (`employee`)
) engine=innodb default charset=utf8;

create table `DeductionGroup` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  primary key  (`id`)
) engine=innodb default charset=utf8;

drop table `DeductionRules`;
drop table `Deductions`;

create table `Deductions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `componentType` varchar(250) NULL,
  `component` varchar(250) NULL,
  `payrollColumn` int(11) DEFAULT NULL,
  `rangeAmounts` text default null,
  `deduction_group` bigint(20) NULL,
  CONSTRAINT `Fk_Deductions_DeductionGroup` FOREIGN KEY (`deduction_group`) REFERENCES `DeductionGroup` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  primary key  (`id`)
) engine=innodb default charset=utf8;





Update Reports set parameters = '[\r\n[ "department", {"label":"Department (Company)","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}],\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}],\r\n[ "status", {"label":"Leave Status","type":"select","source":[["NULL","All Statuses"],["Approved","Approved"],["Pending","Pending"],["Rejected","Rejected"],["Cancellation Requested","Cancellation Requested"],["Cancelled","Cancelled"]]}]\r\n]' where name = "Employee Leaves Report";


Delete from `Settings` where name = 'System: Default Country';