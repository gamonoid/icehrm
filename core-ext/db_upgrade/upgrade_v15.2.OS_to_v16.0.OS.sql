REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`) VALUES
  ('Employee Details Report', 'This report list all employee details and you can filter employees by department, employment status or job title', '[\r\n[ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}],\r\n[ "employment_status", {"label":"Employment Status","type":"select2","remote-source":["EmploymentStatus","id","name"],"allow-null":true}],\r\n[ "job_title", {"label":"Job Title","type":"select2","remote-source":["JobTitle","id","name"],"allow-null":true}]\r\n]', 'Select id, employee_id as ''Employee ID'',\r\nconcat(`first_name`,'' '',`middle_name`,'' '', `last_name`) as ''Name'',\r\n(SELECT name from Nationality where id = nationality) as ''Nationality'',\r\nbirthday as ''Birthday'',\r\ngender as ''Gender'',\r\nmarital_status as ''Marital Status'',\r\nssn_num as ''SSN Number'',\r\nnic_num as ''NIC Number'',\r\nother_id as ''Other IDs'',\r\ndriving_license as ''Driving License Number'',\r\n(SELECT name from EmploymentStatus where id = employment_status) as ''Employment Status'',\r\n(SELECT name from JobTitles where id = job_title) as ''Job Title'',\r\n(SELECT name from PayGrades where id = pay_grade) as ''Pay Grade'',\r\nwork_station_id as ''Work Station ID'',\r\naddress1 as ''Address 1'',\r\naddress2 as ''Address 2'',\r\ncity as ''City'',\r\n(SELECT name from Country where code = country) as ''Country'',\r\n(SELECT name from Province where id = province) as ''Province'',\r\npostal_code as ''Postal Code'',\r\nhome_phone as ''Home Phone'',\r\nmobile_phone as ''Mobile Phone'',\r\nwork_phone as ''Work Phone'',\r\nwork_email as ''Work Email'',\r\nprivate_email as ''Private Email'',\r\njoined_date as ''Joined Date'',\r\nconfirmation_date as ''Confirmation Date'',\r\n(SELECT title from CompanyStructures where id = department) as ''Department'',\r\n(SELECT concat(`first_name`,'' '',`middle_name`,'' '', `last_name`,'' [Employee ID:'',`employee_id`,'']'') from Employees e1 where e1.id = e.supervisor) as ''Supervisor'' \r\nFROM Employees e _where_', '["department","employment_status","job_title"]', 'Query', 'Employee Information'),
  ('Employee Time Entry Report', 'This report list all employee time entries by employee, date range and project', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "project", {"label":"Project","type":"select","allow-null":true,"remote-source":["Project","id","name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'EmployeeTimesheetReport', '["employee","date_start","date_end","status"]', 'Class','Time Management'),
  ('Employee Attendance Report', 'This report list all employee attendance entries by employee and date range', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'EmployeeAttendanceReport', '["employee","date_start","date_end"]', 'Class','Time Management'),
  ('Employee Time Tracking Report', 'This report list employee working hours and attendance details for each day for a given period ', '[\r\n[ "employee", {"label":"Employee","type":"select2","allow-null":false,"remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'EmployeeTimeTrackReport', '["employee","date_start","date_end"]', 'Class','Time Management');

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




REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`) VALUES
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

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
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







create table `LeaveTypes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `supervisor_leave_assign` enum('Yes','No') default 'Yes',
  `employee_can_apply` enum('Yes','No') default 'Yes',
  `apply_beyond_current` enum('Yes','No') default 'Yes',
  `leave_accrue` enum('No','Yes') default 'No',
  `carried_forward` enum('No','Yes') default 'No',
  `default_per_year` decimal(10,3) NOT NULL,
  `carried_forward_percentage` int(11) NULL default 0,
  `carried_forward_leave_availability` int(11) NULL default 365,
  `propotionate_on_joined_date` enum('No','Yes') default 'No',
  `send_notification_emails` enum('Yes','No') default 'Yes',
  `leave_group` bigint(20) NULL,
  `leave_color` varchar(10) NULL,
  `max_carried_forward_amount` int(11) NULL default 0,
  primary key  (`id`),
  unique key (`name`)
) engine=innodb default charset=utf8;

create table `LeaveRules` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `leave_type` bigint(20) NOT NULL,
  `job_title` bigint(20) NULL,
  `employment_status` bigint(20) NULL,
  `employee` bigint(20) NULL,
  `supervisor_leave_assign` enum('Yes','No') default 'Yes',
  `employee_can_apply` enum('Yes','No') default 'Yes',
  `apply_beyond_current` enum('Yes','No') default 'Yes',
  `leave_accrue` enum('No','Yes') default 'No',
  `carried_forward` enum('No','Yes') default 'No',
  `default_per_year` decimal(10,3) NOT NULL,
  `carried_forward_percentage` int(11) NULL default 0,
  `carried_forward_leave_availability` int(11) NULL default 365,
  `propotionate_on_joined_date` enum('No','Yes') default 'No',
  `leave_group` bigint(20) NULL,
  `max_carried_forward_amount` int(11) NULL default 0,
  primary key  (`id`)
) engine=innodb default charset=utf8;


create table `LeaveGroups` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text default null,
  `created` timestamp NULL default '0000-00-00 00:00:00',
  `updated` timestamp NULL default '0000-00-00 00:00:00',
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `LeaveGroupEmployees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `leave_group` bigint(20) NOT NULL,
  `created` timestamp NULL default '0000-00-00 00:00:00',
  `updated` timestamp NULL default '0000-00-00 00:00:00',
  CONSTRAINT `Fk_LeaveGroupEmployees_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_LeaveGroupEmployees_LeaveGroups` FOREIGN KEY (`leave_group`) REFERENCES `LeaveGroups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  primary key  (`id`),
  unique key `LeaveGroupEmployees_employee` (`employee`)
) engine=innodb default charset=utf8;

create table `LeavePeriods` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `date_start` date default '0000-00-00',
  `date_end` date default '0000-00-00',
  `status` enum('Active','Inactive') default 'Inactive',
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `WorkDays` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `status` enum('Full Day','Half Day','Non-working Day') default 'Full Day',
  `country` bigint(20) DEFAULT NULL,
  primary key  (`id`),
  unique key `workdays_name_country` (`name`,`country`)
) engine=innodb default charset=utf8;

create table `HoliDays` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `dateh` date default '0000-00-00',
  `status` enum('Full Day','Half Day') default 'Full Day',
  `country` bigint(20) DEFAULT NULL,
  primary key  (`id`),
  unique key `holidays_dateh_country` (`dateh`,`country`)
) engine=innodb default charset=utf8;

create table `EmployeeLeaves` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `leave_type` bigint(20) NOT NULL,
  `leave_period` bigint(20) NOT NULL,
  `date_start` date default '0000-00-00',
  `date_end` date default '0000-00-00',
  `details` text default null,
  `status` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled') default 'Pending',
  `attachment` varchar(100) NULL,
  CONSTRAINT `Fk_EmployeeLeaves_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeLeaves_LeaveTypes` FOREIGN KEY (`leave_type`) REFERENCES `LeaveTypes` (`id`),
  CONSTRAINT `Fk_EmployeeLeaves_LeavePeriods` FOREIGN KEY (`leave_period`) REFERENCES `LeavePeriods` (`id`),
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `EmployeeLeaveLog` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee_leave` bigint(20) NOT NULL,
  `user_id` bigint(20) NULL,
  `data` varchar(500) NOT NULL,
  `status_from` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled') default 'Pending',
  `status_to` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled') default 'Pending',
  `created` timestamp default '0000-00-00 00:00:00',
  CONSTRAINT `Fk_EmployeeLeaveLog_EmployeeLeaves` FOREIGN KEY (`employee_leave`) REFERENCES `EmployeeLeaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeLeaveLog_Users` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `EmployeeLeaveDays` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee_leave` bigint(20) NOT NULL,
  `leave_date` date default '0000-00-00',
  `leave_type` enum('Full Day','Half Day - Morning','Half Day - Afternoon','1 Hour - Morning','2 Hours - Morning','3 Hours - Morning','1 Hour - Afternoon','2 Hours - Afternoon','3 Hours - Afternoon') NOT NULL,
  CONSTRAINT `Fk_EmployeeLeaveDays_EmployeeLeaves` FOREIGN KEY (`employee_leave`) REFERENCES `EmployeeLeaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  primary key  (`id`)
) engine=innodb default charset=utf8;


create table `Documents` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text default null,
  `expire_notification` enum('Yes','No') default 'Yes',
  `expire_notification_month` enum('Yes','No') default 'Yes',
  `expire_notification_week` enum('Yes','No') default 'Yes',
  `expire_notification_day` enum('Yes','No') default 'Yes',
  `sign` enum('Yes','No') default 'Yes',
  `sign_label` VARCHAR(500) default null,
  `created` DATETIME default '0000-00-00 00:00:00',
  `updated` DATETIME default '0000-00-00 00:00:00',
  primary key  (`id`)
) engine=innodb default charset=utf8;


create table `EmployeeDocuments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `document` bigint(20) NULL,
  `date_added` date NOT NULL,
  `valid_until` date NOT NULL,
  `status` enum('Active','Inactive','Draft') default 'Active',
  `details` text default null,
  `attachment` varchar(100) NULL,
  `signature` text default null,
  `expire_notification_last` int(4) NULL,
  CONSTRAINT `Fk_EmployeeDocuments_Documents` FOREIGN KEY (`document`) REFERENCES `Documents` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeDocuments_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  primary key  (`id`),
  KEY `KEY_EmployeeDocuments_valid_until` (`valid_until`),
  KEY `KEY_EmployeeDocuments_valid_until_status` (`valid_until`,`status`,`expire_notification_last`)
) engine=innodb default charset=utf8;


create table `CompanyDocuments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text default null,
  `valid_until` date NOT NULL,
  `status` enum('Active','Inactive','Draft') default 'Active',
  `notify_employees` enum('Yes','No') default 'Yes',
  `attachment` varchar(100) NULL,
  primary key  (`id`)
) engine=innodb default charset=utf8;


create table `Courses` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `code` varchar(300) NOT NULL,
  `name` varchar(300) NOT NULL,
  `description` text default null,
  `coordinator` bigint(20) NULL,
  `trainer` varchar(300) NULL,
  `trainer_info` text default null,
  `paymentType` enum('Company Sponsored','Paid by Employee') default 'Company Sponsored',
  `currency` varchar(3) null,
  `cost` decimal(12,2) DEFAULT 0.00,
  `status` enum('Active','Inactive') default 'Active',
  `created` datetime default '0000-00-00 00:00:00',
  `updated` datetime default '0000-00-00 00:00:00',
  CONSTRAINT `Fk_Courses_Employees` FOREIGN KEY (`coordinator`) REFERENCES `Employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `TrainingSessions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `course` bigint(20) NOT NULL,
  `description` text default null,
  `scheduled` datetime default '0000-00-00 00:00:00',
  `dueDate` datetime default '0000-00-00 00:00:00',
  `deliveryMethod` enum('Classroom','Self Study','Online') default 'Classroom',
  `deliveryLocation` varchar(500) NULL,
  `status` enum('Pending','Approved','Completed','Cancelled') default 'Pending',
  `attendanceType` enum('Sign Up','Assign') default 'Sign Up',
  `attachment` varchar(300) NULL,
  `created` datetime default '0000-00-00 00:00:00',
  `updated` datetime default '0000-00-00 00:00:00',
  `requireProof` enum('Yes','No') default 'Yes',
  CONSTRAINT `Fk_TrainingSessions_Courses` FOREIGN KEY (`course`) REFERENCES `Courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  primary key  (`id`)
) engine=innodb default charset=utf8;


create table `EmployeeTrainingSessions` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `trainingSession` bigint(20) NULL,
  `feedBack` varchar(1500) NULL,
  `status` enum('Scheduled','Attended','Not-Attended','Completed') default 'Scheduled',
  `proof` varchar(300) NULL,
  CONSTRAINT `Fk_EmployeeTrainingSessions_TrainingSessions` FOREIGN KEY (`trainingSession`) REFERENCES `TrainingSessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeTrainingSessions_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  primary key  (`id`)
) engine=innodb default charset=utf8;



create table `LeaveStartingBalance` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `leave_type` bigint(20) NOT NULL,
  `employee` bigint(20) NULL,
  `leave_period` bigint(20) NOT NULL,
  `amount` decimal(10,3) NOT NULL,
  `note` text DEFAULT NULL,
  `created` datetime default '0000-00-00 00:00:00',
  `updated` datetime default '0000-00-00 00:00:00',
  primary key  (`id`)
) engine=innodb default charset=utf8;



/* Sync with Default Schema */

create table `EmployementType` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) not null default '',
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `Industry` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) not null default '',
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `ExperienceLevel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) not null default '',
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `JobFunction` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) not null default '',
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `EducationLevel` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) not null default '',
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `Benifits` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) not null default '',
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `Tags` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) not null default '',
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `Job` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `shortDescription` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `country` bigint(20) DEFAULT NULL,
  `company` bigint(20) DEFAULT NULL,
  `department` VARCHAR(100) NULL,
  `code` VARCHAR(20) NULL,
  `employementType` bigint(20) DEFAULT NULL,
  `industry` bigint(20) DEFAULT NULL,
  `experienceLevel` bigint(20) DEFAULT NULL,
  `jobFunction` bigint(20) DEFAULT NULL,
  `educationLevel` bigint(20) DEFAULT NULL,
  `currency` bigint(20) DEFAULT NULL,
  `showSalary` enum('Yes','No') default NULL,
  `salaryMin` bigint(20) DEFAULT NULL,
  `salaryMax` bigint(20) DEFAULT NULL,
  `keywords` text DEFAULT NULL,
  `status` enum('Active','On hold','Closed') default NULL,
  `closingDate` DATETIME default '0000-00-00 00:00:00',
  `attachment` varchar(100) NULL,
  `display` varchar(200) NOT NULL,
  `postedBy` bigint(20) DEFAULT NULL,
  INDEX `Job_status` (`status`),
  primary key  (`id`)
) engine=innodb default charset=utf8;


create table `Candidates` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) default '' not null,
  `last_name` varchar(100) default '' not null,
  `nationality` bigint(20) default null,
  `birthday` DATETIME default '0000-00-00 00:00:00',
  `gender` enum('Male','Female') default NULL,
  `marital_status` enum('Married','Single','Divorced','Widowed','Other') default NULL,
  `address1` varchar(100) default '',
  `address2` varchar(100) default '',
  `city` varchar(150) default '',
  `country` char(2) default null,
  `province` bigint(20) default null,
  `postal_code` varchar(20) default null,
  `email` varchar(200) default null,
  `home_phone` varchar(50) default null,
  `mobile_phone` varchar(50) default null,
  `cv_title` varchar(200) default '' not null,
  `cv` varchar(150) NULL,
  `cvtext` text NULL,
  `industry` text DEFAULT NULL,
  `profileImage` varchar(150) NULL,
  `head_line` text DEFAULT NULL,
  `objective` text DEFAULT NULL,
  `work_history` text DEFAULT NULL,
  `education` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `referees` text DEFAULT NULL,
  `linkedInUrl` varchar(500) DEFAULT NULL,
  `linkedInData` text DEFAULT NULL,
  `totalYearsOfExperience` int(11) default null,
  `totalMonthsOfExperience` int(11) default null,
  `htmlCVData` longtext DEFAULT NULL,
  `generatedCVFile` varchar(150) DEFAULT NULL,
  `created` DATETIME default '0000-00-00 00:00:00',
  `updated` DATETIME default '0000-00-00 00:00:00',
  `expectedSalary` int(11) default null,
  `preferedPositions` text default null,
  `preferedJobtype` varchar(60) default null,
  `preferedCountries` text default null,
  `tags` text default null,
  `notes` text default null,
  `calls` text default null,
  `age` int(11) default null,
  `hash` varchar(100) DEFAULT NULL,
  `linkedInProfileLink` varchar(250) DEFAULT NULL,
  `linkedInProfileId` varchar(50) DEFAULT NULL,
  `facebookProfileLink` varchar(250) DEFAULT NULL,
  `facebookProfileId` varchar(50) DEFAULT NULL,
  `twitterProfileLink` varchar(250) DEFAULT NULL,
  `twitterProfileId` varchar(50) DEFAULT NULL,
  `googleProfileLink` varchar(250) DEFAULT NULL,
  `googleProfileId` varchar(50) DEFAULT NULL,
  primary key  (`id`)

) engine=innodb default charset=utf8;


create table `Applications` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `job` bigint(20) NOT NULL,
  `candidate` bigint(20) DEFAULT NULL,
  `created` DATETIME default '0000-00-00 00:00:00',
  `referredByEmail` varchar(200) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  primary key  (`id`),
  unique key  (`job`,`candidate`),
  CONSTRAINT `Fk_Applications_Job` FOREIGN KEY (`job`) REFERENCES `Job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_Applications_Candidates` FOREIGN KEY (`candidate`) REFERENCES `Candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) engine=innodb default charset=utf8;

create table `Interviews` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `job` bigint(20) NOT NULL,
  `candidate` bigint(20) DEFAULT NULL,
  `level` varchar(100) DEFAULT NULL,
  `created` DATETIME default '0000-00-00 00:00:00',
  `updated` DATETIME default '0000-00-00 00:00:00',
  `scheduled` DATETIME default '0000-00-00 00:00:00',
  `location` varchar(500) DEFAULT NULL,
  `mapId` bigint(20) NULL,
  `status` varchar(100) default null,
  `notes` text DEFAULT NULL,
  primary key  (`id`),
  CONSTRAINT `Fk_Interviews_Job` FOREIGN KEY (`job`) REFERENCES `Job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_Interviews_Candidates` FOREIGN KEY (`candidate`) REFERENCES `Candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) engine=innodb default charset=utf8;


create table `Calls` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `job` bigint(20) NOT NULL,
  `candidate` bigint(20) DEFAULT NULL,
  `phone` varchar(20) default null,
  `created` DATETIME default '0000-00-00 00:00:00',
  `updated` DATETIME default '0000-00-00 00:00:00',
  `status` varchar(100) default null,
  `notes` text DEFAULT NULL,
  primary key  (`id`),
  CONSTRAINT `Fk_Calls_Job` FOREIGN KEY (`job`) REFERENCES `Job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_Calls_Candidates` FOREIGN KEY (`candidate`) REFERENCES `Candidates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) engine=innodb default charset=utf8;






/* Add Missing Master Data */

INSERT INTO `WorkDays` (`id`, `name`, `status`, `country`) VALUES
  (1, 'Monday', 'Full Day',NULL),
  (2, 'Tuesday', 'Full Day',NULL),
  (3, 'Wednesday', 'Full Day',NULL),
  (4, 'Thursday', 'Full Day',NULL),
  (5, 'Friday', 'Full Day',NULL),
  (6, 'Saturday', 'Non-working Day',NULL),
  (7, 'Sunday', 'Non-working Day',NULL);




REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
('System: Reset Module Names', '1', 'Select this to reset module names in Database','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]'),
('Leave: Share Calendar to Whole Company', '1', '','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]'),
('Leave: CC Emails', '',  'Every email sent though leave module will be CC to these comma seperated list of emails addresses',''),
('Leave: BCC Emails', '',  'Every email sent though leave module will be BCC to these comma seperated list of emails addresses',''),
('Api: REST Api Enabled', '1',  '','["value", {"label":"Value","type":"select","source":[["0","No"],["1","Yes"]]}]'),
('Api: REST Api Token', 'Click on edit icon',  '','["value", {"label":"Value","type":"placeholder"}]');


REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('LDAP: Enabled', '0',  '','["value", {"label":"Value","type":"select","source":[["0","No"],["1","Yes"]]}]'),
  ('LDAP: Server', '',  'LDAP Server IP or DNS',''),
  ('LDAP: Port', '389',  'LDAP Server Port',''),
  ('LDAP: Root DN', '',  'e.g: dc=mycompany,dc=net',''),
  ('LDAP: Manager DN', '',  'e.g: cn=admin,dc=mycompany,dc=net',''),
  ('LDAP: Manager Password', '',  'Password of the manager user',''),
  ('LDAP: Version 3', '1',  'Are you using LDAP v3','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]'),
  ('LDAP: User Filter', '',  'e.g: uid={}, we will replace {} with actual username provided by the user at the time of login','');

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Leave: Allow Indirect Admins to Approve', '0', 'Allow indirect admins to approve leave requests','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');


REPLACE INTO `Documents` (`id`, `name`, `details`, `expire_notification`, `expire_notification_month`, `expire_notification_week`, `expire_notification_day`,`sign`,`created`, `updated`) VALUES
  (1, 'ID Copy', 'Your ID copy','Yes','Yes','Yes','Yes','No',NOW(), NOW()),
  (2, 'Degree Certificate', 'Degree Certificate','Yes','Yes','Yes','Yes','Yes',NOW(), NOW()),
  (3, 'Driving License', 'Driving License','Yes','Yes','Yes','Yes','Yes',NOW(), NOW());

REPLACE INTO `HoliDays` (`id`, `name`, `dateh`, `status`) VALUES
  (1, 'New Year''s Day', '2015-01-01', 'Full Day'),
  (2, 'Christmas Day', '2015-12-25', 'Full Day');

REPLACE INTO `LeavePeriods` (`id`, `name`, `date_start`, `date_end`, `status`) VALUES
  (3, 'Year 2015', '2015-01-01', '2015-12-31', 'Active'),
  (4, 'Year 2016', '2016-01-01', '2016-12-31', 'Active'),
  (5, 'Year 2017', '2017-01-01', '2017-12-31', 'Active');

REPLACE INTO `LeaveTypes` (`id`, `name`, `supervisor_leave_assign`, `employee_can_apply`, `apply_beyond_current`, `leave_accrue`, `carried_forward`, `default_per_year`) VALUES
  (1, 'Annual leave', 'No', 'Yes', 'No', 'No', 'No', 14),
  (2, 'Casual leave', 'Yes', 'Yes', 'No', 'No', 'No', 7),
  (3, 'Medical leave', 'Yes', 'Yes', 'Yes', 'No', 'No', 7);

REPLACE INTO `Courses` (`id`,`code`, `name`, `description`, `coordinator`, `trainer`, `trainer_info`, `paymentType`, `currency`, `cost`, `status`, `created`, `updated`) VALUES
  (1,'C0001', 'Info Marketing', 'Learn how to Create and Outsource Info Marketing Products', 1, 'Tim Jhon', 'Tim Jhon has a background in business management and has been working with small business to establish their online presence','Company Sponsored','USD','55','Active',now(), now()),
  (2,'C0002', 'People Management', 'Learn how to Manage People', 1, 'Tim Jhon', 'Tim Jhon has a background in business management and has been working with small business to establish their online presence','Company Sponsored','USD','59','Active',now(), now());

REPLACE INTO `EmployementType` (`name`) VALUES
  ('Full-time'),
  ('Part-time'),
  ('Contract'),
  ('Temporary'),
  ('Other');

REPLACE INTO `Benifits` (`name`) VALUES
  ('Retirement plan'),
  ('Health plan'),
  ('Life insurance'),
  ('Paid vacations');



REPLACE INTO `ExperienceLevel` (`name`) VALUES
  ('Not Applicable'),
  ('Internship'),
  ('Entry level'),
  ('Associate'),
  ('Mid-Senior level'),
  ('Director'),
  ('Executive');

REPLACE INTO `JobFunction` (`name`) VALUES
  ('Accounting/Auditing'),
  ('Administrative'),
  ('Advertising'),
  ('Business Analyst'),
  ('Financial Analyst'),
  ('Data Analyst'),
  ('Art/Creative'),
  ('Business Development'),
  ('Consulting'),
  ('Customer Service'),
  ('Distribution'),
  ('Design'),
  ('Education'),
  ('Engineering'),
  ('Finance'),
  ('General Business'),
  ('Health Care Provider'),
  ('Human Resources'),
  ('Information Technology'),
  ('Legal'),
  ('Management'),
  ('Manufacturing'),
  ('Marketing'),
  ('Other'),
  ('Public Relations'),
  ('Purchasing'),
  ('Product Management'),
  ('Project Management'),
  ('Production'),
  ('Quality Assurance'),
  ('Research'),
  ('Sales'),
  ('Science'),
  ('Strategy/Planning'),
  ('Supply Chain'),
  ('Training'),
  ('Writing/Editing');


REPLACE INTO `EducationLevel` (`name`) VALUES
  ('Unspecified'),
  ('High School or equivalent'),
  ('Certification'),
  ('Vocational'),
  ('Associate Degree'),
  ('Bachelor\'s Degree'),
  ('Master\'s Degree'),
  ('Doctorate'),
  ('Professional'),
  ('Some College Coursework Completed'),
  ('Vocational - HS Diploma'),
  ('Vocational - Degree'),
  ('Some High School Coursework');


REPLACE INTO `Crons` (`name`,`class`, `lastrun`, `frequency`, `time`, `type`, `status`) VALUES
  ('Email Sender Task', 'EmailSenderTask', NULL, 1, 1, 'Minutely', 'Enabled'),
  ('Document Expire Alert', 'DocumentExpiryNotificationTask', NULL, 1, (FLOOR( 1 + RAND( ) *58 )), 'Hourly', 'Enabled');


REPLACE INTO `ExpensesPaymentMethods` (`name`) VALUES
  ('Cash'),
  ('Check'),
  ('Credit Card'),
  ('Debit Card');


REPLACE INTO `ExpensesCategories` (`name`) VALUES
  ('Auto - Gas'),
  ('Auto - Insurance'),
  ('Auto - Maintenance'),
  ('Auto - Payment'),
  ('Transportation'),
  ('Bank Fees'),
  ('Dining Out'),
  ('Entertainment'),
  ('Hotel / Motel'),
  ('Insurance'),
  ('Interest Charges'),
  ('Loan Payment'),
  ('Medical'),
  ('Mileage'),
  ('Rent'),
  ('Rental Car'),
  ('Utility');
