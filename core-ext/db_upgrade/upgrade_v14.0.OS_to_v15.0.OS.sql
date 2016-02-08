ALTER table `Reports` ADD COLUMN `report_group` varchar(500) NULL;

ALTER table `Employees` ADD COLUMN `indirect_supervisors` VARCHAR(250) default null after `supervisor`;

INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Leave: Allow Indirect Admins to Approve', '0', 'Allow indirect admins to approve leave requests','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');

INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('System: Default Country', '0', 'Set the default Country','[ "value", {"label":"Country","type":"select2","remote-source":["Country","code","name"]}]');

UPDATE Reports set report_group = 'Employee Information' where name = 'Employee Details Report';
UPDATE Reports set report_group = 'Leave Management' where name = 'Employee Leaves Report';
UPDATE Reports set report_group = 'Time Management' where name = 'Employee Time Entry Report';
UPDATE Reports set report_group = 'Time Management' where name = 'Employee Attendance Report';
UPDATE Reports set report_group = 'Time Management' where name = 'Employee Time Tracking Report';
UPDATE Reports set report_group = 'Employee Information' where name = 'Active Employee Report';
UPDATE Reports set report_group = 'Employee Information' where name = 'New Hires Employee Report';
UPDATE Reports set report_group = 'Employee Information' where name = 'Terminated Employee Report';
UPDATE Reports set report_group = 'Leave Management' where name = 'Employee Leave Entitlement';
UPDATE Reports set report_group = 'Travel and Expense Management' where name = 'Travel Request Report';
UPDATE Reports set report_group = 'Travel and Expense Management' where name = 'Expense Report';

INSERT INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`) VALUES
  ('Employee Time Sheet Report', 'This report list all employee time sheets by employee and date range', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}],\r\n[ "status", {"label":"Status","allow-null":true,"null-label":"All Status","type":"select","source":[["Approved","Approved"],["Pending","Pending"],["Rejected","Rejected"]]}]\r\n]', 'EmployeeTimeSheetData', '["employee","date_start","date_end","status"]', 'Class','Time Management');



INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
('Files: Upload Files to S3', '0', '','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]'),
('Files: Amazon S3 Key for File Upload', '', 'Please provide S3 Key for uploading files',''),
('Files: Amazone S3 Secret for File Upload', '',  'Please provide S3 Secret for uploading files',''),
('Files: S3 Bucket', '',  'Please provide S3 Bucket name for uploading files',''),
('Files: S3 Web Url', '',  'Please provide Url to the s3 bucket','');


create table `Crons` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `class` varchar(100) NOT NULL,
  `lastrun` DATETIME default '0000-00-00 00:00:00',
  `frequency` int(4) NOT NULL,
  `time` varchar(50) NOT NULL,
  `type` enum('Minutely','Hourly','Daily','Weekly','Monthly','Yearly') default 'Hourly',
  `status` enum('Enabled','Disabled') default 'Enabled',
  primary key  (`id`),
  key `KEY_Crons_frequency` (`frequency`)
) engine=innodb default charset=utf8;


INSERT INTO `Crons` (`name`,`class`, `lastrun`, `frequency`, `time`, `type`, `status`) VALUES
  ('Email Sender Task', 'EmailSenderTask', NULL, 1, 1, 'Minutely', 'Enabled'),
  ('Document Expire Alert', 'DocumentExpiryNotificationTask', NULL, 1, 1, 'Minutely', 'Enabled');




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

INSERT INTO `WorkDays` (`id`, `name`, `status`, `country`) VALUES
  (1, 'Monday', 'Full Day',NULL),
  (2, 'Tuesday', 'Full Day',NULL),
  (3, 'Wednesday', 'Full Day',NULL),
  (4, 'Thursday', 'Full Day',NULL),
  (5, 'Friday', 'Full Day',NULL),
  (6, 'Saturday', 'Non-working Day',NULL),
  (7, 'Sunday', 'Non-working Day',NULL);


INSERT INTO `Documents` (`id`, `name`, `details`, `expire_notification`, `expire_notification_month`, `expire_notification_week`, `expire_notification_day`,`sign`,`created`, `updated`) VALUES
  (1, 'ID Copy', 'Your ID copy','Yes','Yes','Yes','Yes','No',NOW(), NOW()),
  (2, 'Degree Certificate', 'Degree Certificate','Yes','Yes','Yes','Yes','Yes',NOW(), NOW()),
  (3, 'Driving License', 'Driving License','Yes','Yes','Yes','Yes','Yes',NOW(), NOW());

INSERT INTO `HoliDays` (`id`, `name`, `dateh`, `status`) VALUES
  (1, 'New Year''s Day', '2015-01-01', 'Full Day'),
  (2, 'Christmas Day', '2015-12-25', 'Full Day');

INSERT INTO `LeavePeriods` (`id`, `name`, `date_start`, `date_end`, `status`) VALUES
  (3, 'Year 2015', '2015-01-01', '2015-12-31', 'Active'),
  (4, 'Year 2016', '2016-01-01', '2016-12-31', 'Active'),
  (5, 'Year 2017', '2017-01-01', '2017-12-31', 'Active');

INSERT INTO `LeaveTypes` (`id`, `name`, `supervisor_leave_assign`, `employee_can_apply`, `apply_beyond_current`, `leave_accrue`, `carried_forward`, `default_per_year`) VALUES
  (1, 'Annual leave', 'No', 'Yes', 'No', 'No', 'No', 14),
  (2, 'Casual leave', 'Yes', 'Yes', 'No', 'No', 'No', 7),
  (3, 'Medical leave', 'Yes', 'Yes', 'Yes', 'No', 'No', 7);


INSERT INTO `Courses` (`id`,`code`, `name`, `description`, `coordinator`, `trainer`, `trainer_info`, `paymentType`, `currency`, `cost`, `status`, `created`, `updated`) VALUES
  (1,'C0001', 'Info Marketing', 'Learn how to Create and Outsource Info Marketing Products', 1, 'Tim Jhon', 'Tim Jhon has a background in business management and has been working with small business to establish their online presence','Company Sponsored','USD','55','Active',now(), now()),
  (2,'C0002', 'People Management', 'Learn how to Manage People', 1, 'Tim Jhon', 'Tim Jhon has a background in business management and has been working with small business to establish their online presence','Company Sponsored','USD','59','Active',now(), now());

INSERT INTO `EmployementType` (`name`) VALUES
  ('Full-time'),
  ('Part-time'),
  ('Contract'),
  ('Temporary'),
  ('Other');

INSERT INTO `Benifits` (`name`) VALUES
  ('Retirement plan'),
  ('Health plan'),
  ('Life insurance'),
  ('Paid vacations');



INSERT INTO `ExperienceLevel` (`name`) VALUES
  ('Not Applicable'),
  ('Internship'),
  ('Entry level'),
  ('Associate'),
  ('Mid-Senior level'),
  ('Director'),
  ('Executive');

INSERT INTO `JobFunction` (`name`) VALUES
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


INSERT INTO `EducationLevel` (`name`) VALUES
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
