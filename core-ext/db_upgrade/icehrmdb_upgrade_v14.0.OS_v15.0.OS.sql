ALTER table `Employees` ADD COLUMN `indirect_supervisors` VARCHAR(250) default null after `supervisor`;
ALTER table `Reports` ADD COLUMN `report_group` varchar(500) NULL;

UPDATE Reports set report_group = 'Employee Information' where name = 'Employee Details Report';
UPDATE Reports set report_group = 'Time Management' where name = 'Employee Time Entry Report';
UPDATE Reports set report_group = 'Time Management' where name = 'Employee Attendance Report';
UPDATE Reports set report_group = 'Time Management' where name = 'Employee Time Tracking Report';
UPDATE Reports set report_group = 'Employee Information' where name = 'Active Employee Report';
UPDATE Reports set report_group = 'Employee Information' where name = 'New Hires Employee Report';
UPDATE Reports set report_group = 'Employee Information' where name = 'Terminated Employee Report';
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


