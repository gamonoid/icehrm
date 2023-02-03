/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

DELIMITER $$
CREATE FUNCTION generate_fname () RETURNS varchar(255)
  BEGIN
    RETURN ELT(FLOOR(1 + (RAND() * (100-1))), "James","Mary","John","Patricia","Robert","Linda","Michael","Barbara","William","Elizabeth","David","Jennifer","Richard","Maria","Charles","Susan","Joseph","Margaret","Thomas","Dorothy","Christopher","Lisa","Daniel","Nancy","Paul","Karen","Mark","Betty","Donald","Helen","George","Sandra","Kenneth","Donna","Steven","Carol","Edward","Ruth","Brian","Sharon","Ronald","Michelle","Anthony","Laura","Kevin","Sarah","Jason","Kimberly","Matthew","Deborah","Gary","Jessica","Timothy","Shirley","Jose","Cynthia","Larry","Angela","Jeffrey","Melissa","Frank","Brenda","Scott","Amy","Eric","Anna","Stephen","Rebecca","Andrew","Virginia","Raymond","Kathleen","Gregory","Pamela","Joshua","Martha","Jerry","Debra","Dennis","Amanda","Walter","Stephanie","Patrick","Carolyn","Peter","Christine","Harold","Marie","Douglas","Janet","Henry","Catherine","Carl","Frances","Arthur","Ann","Ryan","Joyce","Roger","Diane");
  END$$

DELIMITER ;

DELIMITER $$
CREATE FUNCTION generate_lname () RETURNS varchar(255)
  BEGIN
    RETURN ELT(FLOOR(1 + (RAND() * (100-1))), "Smith","Johnson","Williams","Jones","Brown","Davis","Miller","Wilson","Moore","Taylor","Anderson","Thomas","Jackson","White","Harris","Martin","Thompson","Garcia","Martinez","Robinson","Clark","Rodriguez","Lewis","Lee","Walker","Hall","Allen","Young","Hernandez","King","Wright","Lopez","Hill","Scott","Green","Adams","Baker","Gonzalez","Nelson","Carter","Mitchell","Perez","Roberts","Turner","Phillips","Campbell","Parker","Evans","Edwards","Collins","Stewart","Sanchez","Morris","Rogers","Reed","Cook","Morgan","Bell","Murphy","Bailey","Rivera","Cooper","Richardson","Cox","Howard","Ward","Torres","Peterson","Gray","Ramirez","James","Watson","Brooks","Kelly","Sanders","Price","Bennett","Wood","Barnes","Ross","Henderson","Coleman","Jenkins","Perry","Powell","Long","Patterson","Hughes","Flores","Washington","Butler","Simmons","Foster","Gonzales","Bryant","Alexander","Russell","Griffin","Diaz","Hayes");
  END$$
DELIMITER ;

INSERT INTO `CompanyStructures` (`id`, `title`, `description`, `address`, `type`, `country`, `parent`) VALUES
(4, 'Development Center', 'Development Center', 'PO Box 001002\nSample Road, Sample Town', 'Regional Office', 'SG', 1),
(5, 'Engineering Department', 'Engineering Department', 'PO Box 001002\nSample Road, Sample Town,  341234', 'Department', 'SG', 4),
(6, 'Development Team', 'Development Team', '', 'Unit', 'SG', 5),
(7, 'QA Team', 'QA Team', '', 'Unit', 'SG', 5),
(8, 'Server Administration', 'Server Administration', '', 'Unit', 'SG', 5),
(9, 'Administration & HR', 'Administration and Human Resource', '', 'Department', 'SG', 4);


INSERT INTO `Employees` (`id`, `employee_id`, `first_name`, `middle_name`, `last_name`, `nationality`, `birthday`, `gender`, `marital_status`, `ssn_num`, `nic_num`, `other_id`, `driving_license`, `driving_license_exp_date`, `employment_status`, `job_title`, `pay_grade`, `work_station_id`, `address1`, `address2`, `city`, `country`, `province`, `postal_code`, `home_phone`, `mobile_phone`, `work_phone`, `work_email`, `private_email`, `joined_date`, `confirmation_date`, `supervisor`, `department`, `custom1`, `custom2`, `custom3`, `custom4`, `custom5`, `custom6`, `custom7`, `custom8`, `custom9`, `custom10`,`indirect_supervisors`) VALUES
(2, 'EMP002', 'Lala', 'Nadila ', 'Lamees', 175, '1984-03-12 18:30:00', 'Female', 'Single', '', '4594567WE3', '4595567WE3', '349-066-YUO', '2012-03-01', 1, 8, 2, 'W001', 'Green War Rd, 00123', '', 'Istanbul', 'TR', NULL, '909066', '+960112345', '+960112345', '+960112345', 'icehrm+manager@web-stalk.com', 'icehrm+manager@web-stalk.com', '2011-03-07 18:30:00', '2012-02-14 18:30:00', 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,NULL),
(3, 'EMP003', 'Sofia', '', 'O''Sullivan', 4, '1975-08-28 18:30:00', 'Female', 'Married', '', '768-20-4394', '768-20-4394', '', NULL, 3, 10, 2, '', '2792 Trails End Road', 'Fort Lauderdale', 'Fort Lauderdale', 'US', 12, '33308', '954-388-3340', '954-388-3340', '954-388-3340', 'icehrm+user1@web-stalk.com', 'icehrm+user1@web-stalk.com', '2010-02-08 18:30:00', NULL, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,'[\"1\"]'),
(4, 'EMP004', 'Taylor', '', 'Holmes', 10, '1979-07-15 18:30:00', 'Male', 'Single', '158-06-2292', '158-06-2292', '', '', NULL, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12 18:30:00', NULL, 2, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,NULL);



UPDATE `Employees` set supervisor = 2, indirect_supervisors = '[3,4]', approver1 = 5, approver2 = 6, approver3 = 7 where id = 1;
UPDATE `Employees` set supervisor = 1, indirect_supervisors = '[3,4]', approver1 = 5, approver2 = 6 where id = 2;
UPDATE `Employees` set supervisor = 2, indirect_supervisors = '[1,4]', approver1 = 5 where id = 3;

INSERT INTO `EmergencyContacts` (`id`, `employee`, `name`, `relationship`, `home_phone`, `work_phone`, `mobile_phone`) VALUES
(1, 1, 'Emma Owns', 'Mother', '+874463422', '+874463422', '+874463422'),
(2, 2, 'Casey Watson', 'Sister', '231-453-876', '231-453-876', '231-453-876');


INSERT INTO `EmployeeCertifications` (`id`, `certification_id`, `employee`, `institute`, `date_start`, `date_end`) VALUES
(1, 21, 1, 'PHR', '2012-06-04', '2016-06-13'),
(2, 19, 1, 'CPA', '2010-02-16', '2019-02-28'),
(3, 17, 2, 'PMP', '2011-06-14', '2019-10-20'),
(4, 3, 2, 'PMI', '2004-06-08', '2017-09-14');

INSERT INTO `EmployeeCompanyLoans` (`id`, `employee`, `loan`, `start_date`, `last_installment_date`, `period_months`, `amount`, `monthly_installment`, `status`, `details`) VALUES
(1, 2, 2, '2013-02-05', '2016-02-05', 12, '12000.00', '1059.45', 'Approved', '');

INSERT INTO `EmployeeDependents` (`id`, `employee`, `name`, `relationship`, `dob`, `id_number`) VALUES
(1, 1, 'Emma Owns', 'Parent', '1940-06-11', '475209UHB'),
(2, 1, 'Mica Singroo', 'Other', '2000-06-13', '');


INSERT INTO `EmployeeEducations` (`id`, `education_id`, `employee`, `institute`, `date_start`, `date_end`) VALUES
(1, 1, 1, 'National University of Turky', '2004-02-03', '2006-06-13'),
(2, 1, 2, 'MIT', '1995-02-21', '1999-10-12');


INSERT INTO `EmployeeLanguages` (`id`, `language_id`, `employee`, `reading`, `speaking`, `writing`, `understanding`) VALUES
(1, 1, 1, 'Full Professional Proficiency', 'Full Professional Proficiency', 'Full Professional Proficiency', 'Native or Bilingual Proficiency'),
(2, 1, 2, 'Native or Bilingual Proficiency', 'Native or Bilingual Proficiency', 'Native or Bilingual Proficiency', 'Native or Bilingual Proficiency'),
(3, 2, 2, 'Limited Working Proficiency', 'Professional Working Proficiency', 'Limited Working Proficiency', 'Professional Working Proficiency');


INSERT INTO `EmployeeProjects` (`id`, `employee`, `project`, `date_start`, `date_end`, `status`, `details`) VALUES
(1, 2, 1, '2010-03-18', '2014-03-06', 'Inactive', ''),
(3, 2, 2, '2013-02-05', '2013-02-11', 'Current', ''),
(5, 2, 3, '2013-02-24', NULL, 'Current', '');


INSERT INTO `EmployeeSalary` (`employee`, `component`,`amount`, `details`) VALUES
  (1, 1,'50000.00', ''),
  (1, 2,'20000.00', ''),
  (1, 3,'30000.00', ''),
  (1, 4,'2000.00', ''),

  (2, 1,'90500.00', ''),
  (2, 2,'40000.00', ''),
  (2, 3,'50000.00', ''),

  (3, 1,'131409.00', ''),
  (3, 2,'143471.00', ''),
  (3, 3,'50000.00', ''),
  (3, 4,'30000.00', ''),

  (4, 5,'1432.00', ''),
  (4, 6,'2100.00', ''),

  (5, 5,'1200.00', ''),
  (5, 6,'1500.00', ''),
  (5, 7,'2000.00', ''),

  (5, 1,round(rand() * 100000 + rand() * 20000 - rand() * 20000, 2), ''),
  (6, 1,round(rand() * 100000 + rand() * 20000 - rand() * 20000, 2), ''),
  (7, 1,round(rand() * 100000 + rand() * 20000 - rand() * 20000, 2), ''),
  (8, 1,round(rand() * 100000 + rand() * 20000 - rand() * 20000, 2), ''),
  (9, 1,round(rand() * 100000 + rand() * 20000 - rand() * 20000, 2), ''),
  (10, 1,round(rand() * 100000 + rand() * 20000 - rand() * 20000, 2), ''),
  (11, 1,round(rand() * 100000 + rand() * 20000 - rand() * 20000, 2), ''),
  (12, 1,round(rand() * 100000 + rand() * 20000 - rand() * 20000, 2), ''),
  (13, 1,round(rand() * 100000 + rand() * 20000 - rand() * 20000, 2), ''),
  (14, 1,round(rand() * 100000 + rand() * 20000 - rand() * 20000, 2), '');

INSERT INTO `EmployeeSkills` (`id`, `skill_id`, `employee`, `details`) VALUES
(1, 9, 1, 'Creating web sites'),
(2, 6, 2, 'Certified Business Intelligence Professional');




INSERT INTO `LeaveRules` (`id`, `leave_type`, `job_title`, `employment_status`, `employee`, `supervisor_leave_assign`, `employee_can_apply`, `apply_beyond_current`, `leave_accrue`, `carried_forward`, `default_per_year`) VALUES
(1, 1, 11, NULL, NULL, 'No', 'Yes', 'Yes', 'No', 'No', 25),
(2, 2, NULL, NULL, 2, 'No', 'Yes', 'Yes', 'No', 'No', 10);



INSERT INTO `Users` (`id`, `username`, `email`, `password`, `employee`,`default_module`, `user_level`,`user_roles`, `last_login`, `last_update`, `created`) VALUES
(2, 'manager', 'icehrm+manager@web-stalk.com', '4048bb914a704a0728549a26b92d8550', 2,NULL, 'Manager','', '2013-01-03 02:47:37', '2013-01-03 02:47:37', '2013-01-03 02:47:37'),
(3, 'user1', 'icehrm+user1@web-stalk.com', '4048bb914a704a0728549a26b92d8550', 3,NULL, 'Employee','', '2013-01-03 02:48:32', '2013-01-03 02:48:32', '2013-01-03 02:48:32'),
(4, 'user2', 'icehrm+user2@web-stalk.com', '4048bb914a704a0728549a26b92d8550', 4,NULL, 'Employee','', '2013-01-03 02:58:55', '2013-01-03 02:58:55', '2013-01-03 02:58:55'),
(5, 'user3', 'icehrm+user3@web-stalk.com', '4048bb914a704a0728549a26b92d8550', NULL,NULL, 'Other','["1"]', '2013-01-03 02:58:55', '2013-01-03 02:58:55', '2013-01-03 02:58:55');

INSERT INTO `Files` VALUES
  (6,'attachment_BI5XQCYFxZO12W1447383181684','attachment_BI5XQCYFxZO12W1447383181684.png',1,'Job',2000,'2MB');


INSERT INTO `EmployeeDocuments` (`id`,`employee`, `document`, `date_added`, `valid_until`, `status`, `details`, `attachment`, `expire_notification_last`) VALUES
  (1, 1, 1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'Active', '', NULL, -1),
  (2, 1, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Active', '', NULL, -1),
  (3, 1, 3, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Active', '', NULL, -1);



INSERT INTO `Attendance` (`employee`,`in_time`,`out_time`,`note`) VALUES
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 30 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 30 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 29 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 29 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 28 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 28 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 27 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 27 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 26 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 26 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 25 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 25 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 24 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 24 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 23 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 23 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 22 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 22 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 21 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 21 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 20 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 20 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 19 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 19 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 18 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 18 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 17 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 17 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 16 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 16 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(1, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 15 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 15 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),



(2, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 30 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 30 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(2, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 29 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 29 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(2, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 28 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 28 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(2, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 27 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 27 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(2, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 26 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 26 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(2, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 25 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 25 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(2, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 24 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 24 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(2, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 23 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 23 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(2, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 22 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 22 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),


(3, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 30 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 30 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(3, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 29 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 29 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(3, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 28 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 28 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(3, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 27 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 27 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(3, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 26 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 26 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(3, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 25 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 25 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(3, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 24 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 24 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(3, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 23 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 23 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), ''),
(3, FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 22 DAY))) + FLOOR(28800 + (RAND() * 3600)),'%Y-%m-%d %T'), FROM_UNIXTIME((UNIX_TIMESTAMP(DATE_SUB(CURDATE(), INTERVAL 22 DAY))) + FLOOR(57600 + (RAND() * 21600)),'%Y-%m-%d %T'), '');




INSERT INTO `PayrollEmployees` VALUES
  (1,1,4,151,'[]','[]',1),
  (2,2,4,151,'[]','[]',1),
  (3,3,4,151,'[]','[]',1),
  (4,4,2,151,'[]','[]',1),
  (5,5,2,151,'[]','[]',1),
  (6,6,4,151,'[]','[]',1),
  (7,7,4,151,'[]','[]',1),
  (8,8,4,151,'[]','[]',1),
  (9,9,4,151,'[]','[]',1),
  (10,10,4,151,'[]','[]',1);

INSERT INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
  ('Instance : ID', '0847429146712c108e23c435e8f93b4d', '',''),
  ('Instance: Key', 'UQHEYBx9H1eNR66nhNCNCz1WCDDhkjtx1OuJbO3ZQMt+8tfSGvuOH/YEHntRajY=', '','');


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;






