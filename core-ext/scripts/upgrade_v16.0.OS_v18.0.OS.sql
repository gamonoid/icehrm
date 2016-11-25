create table `EmployeeAttendanceSheets` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` enum('Approved','Pending','Rejected','Submitted') default 'Pending',
  CONSTRAINT `Fk_EmployeeAttendanceSheets_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `EmployeeAttendanceSheetsKey` (`employee`,`date_start`,`date_end`),
  KEY `EmployeeAttendanceSheets_date_end` (`date_end`),
  primary key  (`id`)
) engine=innodb default charset=utf8;

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Attendance: Overtime Calculation Period', 'Daily', 'Set the period for overtime calculation. (Affects attendance sheets)','["value", {"label":"Value","type":"select","source":[["Daily","Daily"],["Weekly","Weekly"]]}]');



ALTER TABLE `CustomFields` ADD unique key `CustomFields_name` (`type`,`name`);
Alter table `Reports` ADD COLUMN `output` varchar(15) NOT NULL default 'CSV';
Update Reports set output = 'CSV';

create table `CustomFieldValues` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`type` varchar(20) not null,
	`name` varchar(60) not null,
	`object_id` varchar(60) not null,
	`value` text default NULL,
	`updated` timestamp default '0000-00-00 00:00:00',
	`created` timestamp default '0000-00-00 00:00:00',
	primary key  (`id`),
	UNIQUE key `CustomFields_type_name_object_id` (`type`,`name`,`object_id`),
	INDEX `CustomFields_type_object_id` (`type`,`object_id`)
) engine=innodb default charset=utf8;


ALTER TABLE `CustomFields` ADD COLUMN `field_type` varchar(20) NULL;
ALTER TABLE `CustomFields` ADD COLUMN `field_label` varchar(50) NULL;
ALTER TABLE `CustomFields` ADD COLUMN `field_validation` varchar(50) NULL;
ALTER TABLE `CustomFields` ADD COLUMN `field_options` varchar(500) NULL;
ALTER TABLE `CustomFields` ADD COLUMN `display_order` int(11) default 0;



create table `DataImport` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(60) not null,
	`dataType` varchar(60) not null,
	`details` text default NULL,
	`columns` text default NULL,
	`updated` timestamp default '0000-00-00 00:00:00',
	`created` timestamp default '0000-00-00 00:00:00',
	primary key  (`id`)
) engine=innodb default charset=utf8;


create table `DataImportFiles` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(60) not null,
	`data_import_definition` varchar(200) not null,
	`status` varchar(15) null,
	`file` varchar(100) null,
	`details` text default NULL,
	`updated` timestamp default '0000-00-00 00:00:00',
	`created` timestamp default '0000-00-00 00:00:00',
	primary key  (`id`)
) engine=innodb default charset=utf8;


INSERT INTO `DataImport` (`id`, `name`, `dataType`, `details`, `columns`, `updated`, `created`) VALUES
(1, 'Sage50 Import - Employee Data', 'Sage50Employees', '', '[{"name":"employee_id","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"Yes","id":"columns_7"},{"name":"title","title":"title","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_1"},{"name":"initial","title":"Initial","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_2"},{"name":"first_name","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_3"},{"name":"middle_name","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_4"},{"name":"middle_name","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_5"},{"name":"last_name","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_6"},{"name":"address1","title":"Address1","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_8"},{"name":"address3","title":"Address3","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_10"},{"name":"address2","title":"Address2","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_9"},{"name":"address4","title":"Address4","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_11"},{"name":"address5","title":"Address5","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_12"},{"name":"postal_code","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_13"},{"name":"home_phone","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_14"},{"name":"mobile_phone","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_15"},{"name":"private_email","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_16"},{"name":"gender","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_17"},{"name":"marital_status","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_18"},{"name":"previous_surname","title":"Previous Surname","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_19"},{"name":"birthday","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_20"},{"name":"disabled","title":"Disabled","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_21"},{"name":"nationality","title":"Nationality","type":"Reference","dependOn":"Nationality","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_22"},{"name":"ethnicity","title":"Ethnicity","type":"Normal","dependOn":"Ethnicity","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_23"},{"name":"EmergencyContact/name","title":"","type":"Attached","dependOn":"EmergencyContact","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_24"},{"name":"EmergencyContact/relationship","title":"","type":"Attached","dependOn":"EmergencyContact","dependOnField":"relationship","isKeyField":"No","idField":"No","id":"columns_25"},{"name":"EmergencyContact/home_phone","title":"","type":"Attached","dependOn":"EmergencyContact","dependOnField":"home_phone","isKeyField":"No","idField":"No","id":"columns_26"},{"name":"tax_code","title":"Tax Code","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_27"},{"name":"wk1_mth1_basis","title":"Wk1Mth1 Basis","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_28"},{"name":"NI_category","title":"NI Category","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_29"},{"name":"nic_num","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_30"},{"name":"ssn_num","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_31"},{"name":"job_title","title":"","type":"Reference","dependOn":"JobTitle","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_32"},{"name":"employment_status","title":"","type":"Reference","dependOn":"EmploymentStatus","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_33"},{"name":"pay_frequency","title":"Pay Frequency","type":"Reference","dependOn":"PayFrequency","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_34"},{"name":"contracted_hours","title":"Contracted Hours","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_35"},{"name":"joined_date","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_36"},{"name":"termination_date","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_37"},{"name":"CompanyStructure/title","title":"","type":"Reference","dependOn":"CompanyStructure","dependOnField":"title","isKeyField":"Yes","idField":"No","id":"columns_38"},{"name":"cost_centre_reference","title":"Cost Centre","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_39"},{"name":"employee_notes","title":"Employee Notes","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_40"}]', '2016-06-03 00:26:32', '2016-06-03 00:26:32');


create table `UserReports` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `details` text default null,
  `parameters` text default null,
  `query` text default null,
  `paramOrder` varchar(500) NOT NULL,
  `type` enum('Query','Class') default 'Query',
  `report_group` varchar(500) NULL,
  `output` varchar(15) NOT NULL default 'CSV',
  primary key  (`id`),
  UNIQUE KEY `UserReports_Name` (`name`)
) engine=innodb default charset=utf8;



REPLACE INTO `UserReports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
  ('Time Entry Report', 'View your time entries by date range and project',
   '[\r\n[ "client", {"label":"Select Client","type":"select","allow-null":true,"null-label":"Not Selected","remote-source":["Client","id","name"]}],\r\n[ "project", {"label":"Or Project","type":"select","allow-null":true,"null-label":"All Projects","remote-source":["Project","id","name","getAllProjects"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]',
   'EmployeeTimesheetReport', '["client","project","date_start","date_end","status"]', 'Class','Time Management','CSV');

REPLACE INTO `UserReports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
  ('Attendance Report', 'View your attendance entries by date range', '[\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'EmployeeAttendanceReport', '["date_start","date_end"]', 'Class','Time Management','CSV');

REPLACE INTO `UserReports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
  ('Time Tracking Report', 'View your working hours and attendance details for each day for a given period ', '[\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'EmployeeTimeTrackReport', '["date_start","date_end"]', 'Class','Time Management','CSV');


REPLACE INTO `UserReports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
  ('Travel Request Report', 'View travel requests for a specified period',
   '[\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}],\r\n[ "status", {"label":"Status","type":"select","source":[["NULL","All Statuses"],["Approved","Approved"],["Pending","Pending"],["Rejected","Rejected"],["Cancellation Requested","Cancellation Requested"],["Cancelled","Cancelled"]]}]\r\n]',
   'TravelRequestReport',
   '["date_start","date_end","status"]', 'Class', 'Travel and Expense Management','CSV');


REPLACE INTO `UserReports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
  ('Time Sheet Report', 'This report list all employee time sheets by employee and date range',
   '[\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}],\r\n[ "status", {"label":"Status","allow-null":true,"null-label":"All Status","type":"select","source":[["Approved","Approved"],["Pending","Pending"],["Rejected","Rejected"]]}]\r\n]',
   'EmployeeTimeSheetData',
   '["date_start","date_end","status"]', 'Class','Time Management','CSV');


REPLACE INTO `UserReports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
  ('Client Project Time Report', 'View your time entries for projects under a given client',
   '[\r\n[ "client", {"label":"Select Client","type":"select","allow-null":false,"remote-source":["Client","id","name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]',
   'ClientProjectTimeReport', '["client","date_start","date_end","status"]', 'Class','Time Management','PDF');






REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
  ('Employee Time Entry Report', 'View employee time entries by date range and project',
   '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "client", {"label":"Select Client","type":"select","allow-null":true,"null-label":"Not Selected","remote-source":["Client","id","name"]}],\r\n[ "project", {"label":"Or Project","type":"select","allow-null":true,"null-label":"All Projects","remote-source":["Project","id","name","getAllProjects"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]',
   'EmployeeTimesheetReport', '["employee","client","project","date_start","date_end","status"]', 'Class','Time Management','CSV');


Alter table `Projects` MODIFY COLUMN `status` enum('Active','On Hold','Completed', 'Dropped') default 'Active';

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
('System: Reset Module Names', '0', 'Select this to reset module names in Database','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');






create table `ReportFiles` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`employee` bigint(20) NULL,
	`name` varchar(100) NOT NULL,
	`attachment` varchar(100) NOT NULL,
	`created` timestamp default '0000-00-00 00:00:00',
	unique key `ReportFiles_attachment` (`attachment`),
	primary key  (`id`)
) engine=innodb default charset=utf8;


INSERT IGNORE into ReportFiles (`name`,`attachment`,`created`) SELECT filename,name,SUBSTRING_INDEX(SUBSTRING_INDEX(name,'-',-5),'_',1) from Files where file_group ='Report';

Update Settings set value = '1' where name = 'System: Reset Module Names';

create table `PayslipTemplates` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NOT NULL,
	`data` longtext NULL,
	`status` enum('Show','Hide') default 'Show',
	`created` timestamp default '0000-00-00 00:00:00',
	`updated` timestamp default '0000-00-00 00:00:00',
	primary key  (`id`)
) engine=innodb default charset=utf8;


Alter table `Payroll` add column `payslipTemplate` bigint(20) NULL;

Alter table `Payroll` modify column `column_template` bigint(20) NULL;


Alter table `Employees` add column `approver1` bigint(20) default null;
Alter table `Employees` add column `approver2` bigint(20) default null;
Alter table `Employees` add column `approver3` bigint(20) default null;

create table `EmployeeApprovals` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`type` varchar(100) NOT NULL,
	`element` bigint(20) NOT NULL,
	`approver` bigint(20) NULL,
	`level` int(11) default 0,
	`status` int(11) default 0,
	`active` int(11) default 0,
	`created` timestamp default '0000-00-00 00:00:00',
	`updated` timestamp default '0000-00-00 00:00:00',
	UNIQUE key `EmployeeApprovals_type_element_level` (`type`,`element`,`level`),
	INDEX `EmployeeApprovals_type_element_status_level` (`type`,`element`,`status`,`level`),
	INDEX `EmployeeApprovals_type_element` (`type`,`element`),
	INDEX `EmployeeApprovals_type` (`type`),
	primary key  (`id`)
) engine=innodb default charset=utf8;

Alter table `EmployeeExpenses` modify column `status` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') default 'Pending';
Alter table `EmployeeTravelRecords` modify column `status` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') default 'Pending';


Alter table `EmployeeLeaves` modify column `status` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') default 'Pending';

Alter table `EmployeeLeaveLog` modify column `status_from` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') default 'Pending';
Alter table `EmployeeLeaveLog` modify column `status_to` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') default 'Pending';


create table `StatusChangeLogs` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`type` varchar(100) NOT NULL,
	`element` bigint(20) NOT NULL,
	`user_id` bigint(20) NULL,
	`data` varchar(500) NOT NULL,
	`status_from` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') default 'Pending',
	`status_to` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') default 'Pending',
	`created` timestamp default '0000-00-00 00:00:00',
	INDEX `EmployeeApprovals_type_element` (`type`,`element`),
	primary key  (`id`)
) engine=innodb default charset=utf8;



create table `OvertimeCategories` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(500) NOT NULL,
	`created` timestamp NULL default '0000-00-00 00:00:00',
	`updated` timestamp NULL default '0000-00-00 00:00:00',
	primary key  (`id`)
) engine=innodb default charset=utf8;


create table `EmployeeOvertime` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`employee` bigint(20) NOT NULL,
	`start_time` timestamp NULL default '0000-00-00 00:00:00',
	`end_time` timestamp NULL default '0000-00-00 00:00:00',
	`category` bigint(20) NOT NULL,
	`project` bigint(20) NULL,
	`notes` text NULL,
	`created` timestamp NULL default '0000-00-00 00:00:00',
	`updated` timestamp NULL default '0000-00-00 00:00:00',
	`status` enum('Approved','Pending','Rejected','Cancellation Requested','Cancelled','Processing') default 'Pending',
	CONSTRAINT `Fk_EmployeeOvertime_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `Fk_EmployeeOvertime_Category` FOREIGN KEY (`category`) REFERENCES `OvertimeCategories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	primary key  (`id`)
) engine=innodb default charset=utf8;






ALTER table `Payroll` modify column `column_template` bigint(20) NULL;


ALTER table `CompanyStructures` add column `heads` varchar(255) NULL default NULL;

ALTER table `CustomFields` add column `display_section` varchar(50) NULL;



INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Expense: Allow Indirect Admins to Approve', '0', 'Allow indirect admins to approve expense requests','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');

INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Travel: Allow Indirect Admins to Approve', '0', 'Allow indirect admins to approve travel requests','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');

INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Overtime: Allow Indirect Admins to Approve', '0', 'Allow indirect admins to approve overtime requests','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');



Update `Languages` set name = 'en' where name = 'English';
Update `Languages` set name = 'fr' where name = 'French';
Update `Languages` set name = 'de' where name = 'German';
Update `Languages` set name = 'zh' where name = 'Chinese';

INSERT INTO `Languages` (`id`, `name`, `description`) VALUES
(5, 'aa', 'Afar'),
(6, 'ab', 'Abkhaz'),
(7, 'ae', 'Avestan'),
(8, 'af', 'Afrikaans'),
(9, 'ak', 'Akan'),
(10, 'am', 'Amharic'),
(11, 'an', 'Aragonese'),
(12, 'ar', 'Arabic'),
(13, 'as', 'Assamese'),
(14, 'av', 'Avaric'),
(15, 'ay', 'Aymara'),
(16, 'az', 'Azerbaijani'),
(17, 'ba', 'Bashkir'),
(18, 'be', 'Belarusian'),
(19, 'bg', 'Bulgarian'),
(20, 'bh', 'Bihari'),
(21, 'bi', 'Bislama'),
(22, 'bm', 'Bambara'),
(23, 'bn', 'Bengali'),
(24, 'bo', 'Tibetan Standard, Tibetan, Central'),
(25, 'br', 'Breton'),
(26, 'bs', 'Bosnian'),
(27, 'ca', 'Catalan; Valencian'),
(28, 'ce', 'Chechen'),
(29, 'ch', 'Chamorro'),
(30, 'co', 'Corsican'),
(31, 'cr', 'Cree'),
(32, 'cs', 'Czech'),
(33, 'cu', 'Church Slavic'),
(34, 'cv', 'Chuvash'),
(35, 'cy', 'Welsh'),
(36, 'da', 'Danish'),
(37, 'dv', 'Divehi; Dhivehi; Maldivian;'),
(38, 'dz', 'Dzongkha'),
(39, 'ee', 'Ewe'),
(40, 'el', 'Greek, Modern'),
(41, 'eo', 'Esperanto'),
(42, 'es', 'Spanish; Castilian'),
(43, 'et', 'Estonian'),
(44, 'eu', 'Basque'),
(45, 'fa', 'Persian'),
(46, 'ff', 'Fula; Fulah; Pulaar; Pular'),
(47, 'fi', 'Finnish'),
(48, 'fj', 'Fijian'),
(49, 'fo', 'Faroese'),
(50, 'fy', 'Western Frisian'),
(51, 'ga', 'Irish'),
(52, 'gd', 'Scottish Gaelic; Gaelic'),
(53, 'gl', 'Galician'),
(55, 'gu', 'Gujarati'),
(56, 'gv', 'Manx'),
(57, 'ha', 'Hausa'),
(58, 'he', 'Hebrew (modern)'),
(59, 'hi', 'Hindi'),
(60, 'ho', 'Hiri Motu'),
(61, 'hr', 'Croatian'),
(62, 'ht', 'Haitian; Haitian Creole'),
(63, 'hu', 'Hungarian'),
(64, 'hy', 'Armenian'),
(65, 'hz', 'Herero'),
(66, 'ia', 'Interlingua'),
(67, 'id', 'Indonesian'),
(68, 'ie', 'Interlingue'),
(69, 'ig', 'Igbo'),
(70, 'ii', 'Nuosu'),
(71, 'ik', 'Inupiaq'),
(72, 'io', 'Ido'),
(73, 'is', 'Icelandic'),
(74, 'it', 'Italian'),
(75, 'iu', 'Inuktitut'),
(76, 'ja', 'Japanese (ja)'),
(77, 'jv', 'Javanese (jv)'),
(78, 'ka', 'Georgian'),
(79, 'kg', 'Kongo'),
(80, 'ki', 'Kikuyu, Gikuyu'),
(81, 'kj', 'Kwanyama, Kuanyama'),
(82, 'kk', 'Kazakh'),
(83, 'kl', 'Kalaallisut, Greenlandic'),
(84, 'km', 'Khmer'),
(85, 'kn', 'Kannada'),
(86, 'ko', 'Korean'),
(87, 'kr', 'Kanuri'),
(88, 'ks', 'Kashmiri'),
(89, 'ku', 'Kurdish'),
(90, 'kv', 'Komi'),
(91, 'kw', 'Cornish'),
(92, 'ky', 'Kirghiz, Kyrgyz'),
(93, 'la', 'Latin'),
(94, 'lb', 'Luxembourgish, Letzeburgesch'),
(95, 'lg', 'Luganda'),
(96, 'li', 'Limburgish, Limburgan, Limburger'),
(97, 'ln', 'Lingala'),
(98, 'lo', 'Lao'),
(99, 'lt', 'Lithuanian'),
(100, 'lu', 'Luba-Katanga'),
(101, 'lv', 'Latvian'),
(102, 'mg', 'Malagasy'),
(103, 'mh', 'Marshallese'),
(104, 'mi', 'Maori'),
(105, 'mk', 'Macedonian'),
(106, 'ml', 'Malayalam'),
(107, 'mn', 'Mongolian'),
(108, 'mr', 'Marathi (Mara?hi)'),
(109, 'ms', 'Malay'),
(110, 'mt', 'Maltese'),
(111, 'my', 'Burmese'),
(112, 'na', 'Nauru'),
(113, 'nb', 'Norwegian BokmÃ¥l'),
(114, 'nd', 'North Ndebele'),
(115, 'ne', 'Nepali'),
(116, 'ng', 'Ndonga'),
(117, 'nl', 'Dutch'),
(118, 'nn', 'Norwegian Nynorsk'),
(119, 'no', 'Norwegian'),
(120, 'nr', 'South Ndebele'),
(121, 'nv', 'Navajo, Navaho'),
(122, 'ny', 'Chichewa; Chewa; Nyanja'),
(123, 'oc', 'Occitan'),
(124, 'oj', 'Ojibwe, Ojibwa'),
(125, 'om', 'Oromo'),
(126, 'or', 'Oriya'),
(127, 'os', 'Ossetian, Ossetic'),
(128, 'pa', 'Panjabi, Punjabi'),
(129, 'pi', 'Pali'),
(130, 'pl', 'Polish'),
(131, 'ps', 'Pashto, Pushto'),
(132, 'pt', 'Portuguese'),
(133, 'qu', 'Quechua'),
(134, 'rm', 'Romansh'),
(135, 'rn', 'Kirundi'),
(136, 'ro', 'Romanian, Moldavian, Moldovan'),
(137, 'ru', 'Russian'),
(138, 'rw', 'Kinyarwanda'),
(139, 'sa', 'Sanskrit (Sa?sk?ta)'),
(140, 'sc', 'Sardinian'),
(141, 'sd', 'Sindhi'),
(142, 'se', 'Northern Sami'),
(143, 'sg', 'Sango'),
(144, 'si', 'Sinhala, Sinhalese'),
(145, 'sk', 'Slovak'),
(146, 'sl', 'Slovene'),
(147, 'sm', 'Samoan'),
(148, 'sn', 'Shona'),
(149, 'so', 'Somali'),
(150, 'sq', 'Albanian'),
(151, 'sr', 'Serbian'),
(152, 'ss', 'Swati'),
(153, 'st', 'Southern Sotho'),
(154, 'su', 'Sundanese'),
(155, 'sv', 'Swedish'),
(156, 'sw', 'Swahili'),
(157, 'ta', 'Tamil'),
(158, 'te', 'Telugu'),
(159, 'tg', 'Tajik'),
(160, 'th', 'Thai'),
(161, 'ti', 'Tigrinya'),
(162, 'tk', 'Turkmen'),
(163, 'tl', 'Tagalog'),
(164, 'tn', 'Tswana'),
(165, 'to', 'Tonga (Tonga Islands)'),
(166, 'tr', 'Turkish'),
(167, 'ts', 'Tsonga'),
(168, 'tt', 'Tatar'),
(169, 'tw', 'Twi'),
(170, 'ty', 'Tahitian'),
(171, 'ug', 'Uighur, Uyghur'),
(172, 'uk', 'Ukrainian'),
(173, 'ur', 'Urdu'),
(174, 'uz', 'Uzbek'),
(175, 've', 'Venda'),
(176, 'vi', 'Vietnamese'),
(178, 'wa', 'Walloon'),
(179, 'wo', 'Wolof'),
(180, 'xh', 'Xhosa'),
(181, 'yi', 'Yiddish'),
(182, 'yo', 'Yoruba'),
(183, 'za', 'Zhuang, Chuang'),
(184, 'zu', 'Zulu');


create table `SupportedLanguages` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(100) default null,
	`description` varchar(100) default null,
	primary key  (`id`)
) engine=innodb default charset=utf8;


INSERT INTO `SupportedLanguages` (`name`, `description`) VALUES
('en', 'English'),
('de', 'German'),
('fr', 'French'),
('pl', 'Polish'),
('it', 'Italian'),
('si', 'Sinhala'),
('zh', 'Chinese'),
('ja', 'Japanese'),
('hi', 'Hindi'),
('es', 'Spanish');


Alter table `Users` add column `lang` bigint(20) default null;

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('System: Language', 'en', 'Current Language','["value", {"label":"Value","type":"select2","allow-null":false,"remote-source":["SupportedLanguage","name","description"]}]');




