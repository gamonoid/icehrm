ALTER TABLE Employees ADD COLUMN `status` enum('Active','Terminated') default 'Active';

create table `ArchivedEmployees` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ref_id` bigint(20) NOT NULL,
  `employee_id` varchar(50) default null,
  `first_name` varchar(100) default '' not null,
  `last_name` varchar(100) default '' not null,
  `gender` enum('Male','Female') default NULL,
  `ssn_num` varchar(100) default '',
  `nic_num` varchar(100) default '',
  `other_id` varchar(100) default '',
  `work_email` varchar(100) default null,
  `joined_date` DATETIME default '0000-00-00 00:00:00',
  `confirmation_date` DATETIME default '0000-00-00 00:00:00',
  `supervisor` bigint(20) default null,
  `department` bigint(20) default null,
  `termination_date` DATETIME default '0000-00-00 00:00:00',
  `notes` text default null,
  `data` longtext default null,
  primary key  (`id`)

) engine=innodb default charset=utf8;


create table `FieldNameMappings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  `textOrig` varchar(200) default null,
  `textMapped` varchar(200) default null,
  `display` enum('Form','Table and Form','Hidden') default 'Form',
  `created` DATETIME default '0000-00-00 00:00:00',
  `updated` DATETIME default '0000-00-00 00:00:00',
  primary key  (`id`)
) engine=innodb default charset=utf8;

INSERT INTO `FieldNameMappings` (`type`, `name`, `textOrig`, `textMapped`, `display`) VALUES
  ('Employee', 'employee_id', 'Employee Number', 'Employee Number', 'Table and Form'),
  ('Employee', 'first_name', 'First Name', 'First Name', 'Table and Form'),
  ('Employee', 'middle_name', 'Middle Name', 'Middle Name', 'Form'),
  ('Employee', 'last_name', 'Last Name', 'Last Name', 'Table and Form'),
  ('Employee', 'nationality', 'Nationality', 'Nationality', 'Form'),
  ('Employee', 'birthday', 'Date of Birth', 'Date of Birth', 'Form'),
  ('Employee', 'gender', 'Gender', 'Gender', 'Form'),
  ('Employee', 'marital_status', 'Marital Status', 'Marital Status', 'Form'),
  ('Employee', 'ssn_num', 'SSN/NRIC', 'SSN/NRIC', 'Form'),
  ('Employee', 'nic_num', 'NIC', 'NIC', 'Form'),
  ('Employee', 'other_id', 'Other ID', 'Other ID', 'Form'),
  ('Employee', 'driving_license', 'Driving License No', 'Driving License No', 'Form'),
  ('Employee', 'employment_status', 'Employment Status', 'Employment Status', 'Form'),
  ('Employee', 'job_title', 'Job Title', 'Job Title', 'Form'),
  ('Employee', 'pay_grade', 'Pay Grade', 'Pay Grade', 'Form'),
  ('Employee', 'work_station_id', 'Work Station Id', 'Work Station Id', 'Form'),
  ('Employee', 'address1', 'Address Line 1', 'Address Line 1', 'Form'),
  ('Employee', 'address2', 'Address Line 2', 'Address Line 2', 'Form'),
  ('Employee', 'city', 'City', 'City', 'Form'),
  ('Employee', 'country', 'Country', 'Country', 'Form'),
  ('Employee', 'province', 'Province', 'Province', 'Form'),
  ('Employee', 'postal_code', 'Postal/Zip Code', 'Postal/Zip Code', 'Form'),
  ('Employee', 'home_phone', 'Home Phone', 'Home Phone', 'Form'),
  ('Employee', 'mobile_phone', 'Mobile Phone', 'Mobile Phone', 'Table and Form'),
  ('Employee', 'work_phone', 'Work Phone', 'Work Phone', 'Form'),
  ('Employee', 'work_email', 'Work Email', 'Work Email', 'Form'),
  ('Employee', 'private_email', 'Private Email', 'Private Email', 'Form'),
  ('Employee', 'joined_date', 'Joined Date', 'Joined Date', 'Form'),
  ('Employee', 'confirmation_date', 'Confirmation Date', 'Confirmation Date', 'Form'),
  ('Employee', 'termination_date', 'Termination Date', 'Termination Date', 'Form'),
  ('Employee', 'supervisor', 'Supervisor', 'Supervisor', 'Table and Form'),
  ('Employee', 'department', 'Department', 'Department', 'Table and Form'),
  ('Employee', 'notes', 'Notes', 'Notes', 'Form');


create table `CustomFields` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  `data` text default null,
  `display` enum('Form','Table and Form','Hidden') default 'Form',
  `created` DATETIME default '0000-00-00 00:00:00',
  `updated` DATETIME default '0000-00-00 00:00:00',
  primary key  (`id`)
) engine=innodb default charset=utf8;


INSERT INTO `CustomFields` (`type`, `name`, `data`,`display`) VALUES
  ('Employee', 'custom1', '', 'Hidden'),
  ('Employee', 'custom2', '', 'Hidden'),
  ('Employee', 'custom3', '', 'Hidden'),
  ('Employee', 'custom4', '', 'Hidden'),
  ('Employee', 'custom5', '', 'Hidden'),
  ('Employee', 'custom6', '', 'Hidden'),
  ('Employee', 'custom7', '', 'Hidden'),
  ('Employee', 'custom8', '', 'Hidden'),
  ('Employee', 'custom9', '', 'Hidden'),
  ('Employee', 'custom10', '', 'Hidden');


Alter table `Employees` MODIFY COLUMN `middle_name` varchar(100) default null;
Alter table `Employees` MODIFY COLUMN `last_name` varchar(100) default null;
Alter table `Employees` MODIFY COLUMN `ssn_num` varchar(100) default NULL;
Alter table `Employees` MODIFY COLUMN `nic_num` varchar(100) default NULL;
Alter table `Employees` MODIFY COLUMN `other_id` varchar(100) default NULL;
Alter table `Employees` MODIFY COLUMN `driving_license` varchar(100) default NULL;
Alter table `Employees` MODIFY COLUMN `work_station_id` varchar(100) default NULL;
Alter table `Employees` MODIFY COLUMN `address1` varchar(100) default NULL;
Alter table `Employees` MODIFY COLUMN `address2` varchar(100) default NULL;
Alter table `Employees` MODIFY COLUMN `city` varchar(150) default NULL;