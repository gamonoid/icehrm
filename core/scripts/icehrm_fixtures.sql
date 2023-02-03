INSERT INTO `CompanyStructures` (`id`, `title`, `description`, `address`, `type`, `country`, `parent`) VALUES
(4, 'Development Center', 'Development Center', 'PO Box 001002\nSample Road, Sample Town', 'Regional Office', 'SG', 1),
(5, 'Engineering Department', 'Engineering Department', 'PO Box 001002\nSample Road, Sample Town,  341234', 'Department', 'SG', 4),
(6, 'Development Team', 'Development Team', '', 'Unit', 'SG', 5),
(7, 'QA Team', 'QA Team', '', 'Unit', 'SG', 5),
(8, 'Server Administration', 'Server Administration', '', 'Unit', 'SG', 5),
(9, 'Administration & HR', 'Administration and Human Resource', '', 'Department', 'SG', 4);


REPLACE INTO Employees (id, employee_id, first_name, middle_name, last_name, nationality, birthday, gender, marital_status, ssn_num, nic_num, other_id, driving_license, driving_license_exp_date, employment_status, job_title, pay_grade, work_station_id, address1, address2, city, country, province, postal_code, home_phone, mobile_phone, work_phone, work_email, private_email, joined_date, confirmation_date, supervisor, indirect_supervisors, department, custom1, custom2, custom3, custom4, custom5, custom6, custom7, custom8, custom9, custom10, termination_date, notes, status, ethnicity, immigration_status, approver1, approver2, approver3) VALUES
(2, 'EMP002', 'Lala', 'Nadila ', 'Lamees', 175, '1984-03-12', 'Female', 'Single', '', '4594567WE3', '4595567WE3', '349-066-YUO', '2012-03-01', 1, 8, 2, 'W001', 'Green War Rd, 00123', '', 'Istanbul', 'TR', null, '909066', '+960112345', '+960112345', '+960112345', 'icehrm+manager@web-stalk.com', 'icehrm+manager@web-stalk.com', '2011-03-07', '2012-02-14', 1, '[3,4]', 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, 5, 6, null),
(3, 'EMP003', 'Sofia', '', 'O''Sullivan', 4, '1975-08-28', 'Female', 'Married', '', '768-20-4394', '768-20-4394', '', null, 3, 10, 2, '', '2792 Trails End Road', 'Fort Lauderdale', 'Fort Lauderdale', 'US', 12, '33308', '954-388-3340', '954-388-3340', '954-388-3340', 'icehrm+user1@web-stalk.com', 'icehrm+user1@web-stalk.com', '2010-02-08', null, 2, '[1,4]', 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, 5, null, null),
(4, 'EMP004', 'Taylor', '', 'Holmes', 10, '1979-07-15', 'Male', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(5, 'EMP0.9649787330673809', 'Joseph', '', 'Frances', 10, '1979-07-15', 'Male', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(6, 'EMP0.24060630727615795', 'Carol', '', 'Linda', 10, '1979-07-15', 'Male', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(7, 'EMP0.19408038742288983', 'Walter', '', 'Deborah', 10, '1979-07-15', 'Male', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(8, 'EMP0.03006229552446147', 'Anna', '', 'Daniel', 10, '1979-07-15', 'Male', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(9, 'EMP0.16481477968843167', 'Richard', '', 'Susan', 10, '1979-07-15', 'Male', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(10, 'EMP0.37350199406361395', 'Ronald', '', 'Carl', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(11, 'EMP0.43016207165099873', 'Carol', '', 'Kimberly', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(12, 'EMP0.3284134905118621', 'Christopher', '', 'Linda', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(13, 'EMP0.6587049045215406', 'Richard', '', 'Amy', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(14, 'EMP0.8292297160525152', 'Daniel', '', 'Eric', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(15, 'EMP0.5533464602691555', 'Stephanie', '', 'Jason', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(16, 'EMP0.8785160862640627', 'Ryan', '', 'Karen', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(17, 'EMP0.34093407200736375', 'Frances', '', 'Stephen', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(18, 'EMP0.5547930296089435', 'Joshua', '', 'William', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(19, 'EMP0.17822402266620102', 'Scott', '', 'Scott', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(20, 'EMP0.2785116120041456', 'Kimberly', '', 'Angela', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(21, 'EMP0.4688819464937616', 'Jeffrey', '', 'Jose', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(22, 'EMP0.9907437935385329', 'Helen', '', 'Deborah', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(23, 'EMP0.5996925259006141', 'Gary', '', 'Joshua', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(24, 'EMP0.22532124465827014', 'Harold', '', 'Andrew', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(25, 'EMP0.8340029714945731', 'Elizabeth', '', 'Joyce', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(26, 'EMP0.6365657119420969', 'Daniel', '', 'Lisa', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(27, 'EMP0.407178323163817', 'Brian', '', 'Raymond', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(28, 'EMP0.38699322881828363', 'Amanda', '', 'Harold', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(29, 'EMP0.9656451781891707', 'Dorothy', '', 'Barbara', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(30, 'EMP0.8027986751895385', 'Debra', '', 'Deborah', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(31, 'EMP0.1514758022050148', 'Karen', '', 'Carolyn', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(32, 'EMP0.4425015444331817', 'Rebecca', '', 'Michael', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(33, 'EMP0.3102784932686747', 'Donna', '', 'Debra', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null),
(34, 'EMP0.8747393599476101', 'Patricia', '', 'Shirley', 10, '1979-07-15', 'Female', 'Single', '158-06-2292', '158-06-2292', '', '', null, 1, 5, 2, '', '1164', 'Walnut Avenue', 'Rochelle Park', 'US', 35, '7662', '201-474-8048', '201-474-8048', '201-474-8048', 'icehrm+user2@web-stalk.com', 'icehrm+user2@web-stalk.com', '2006-07-12', null, 2, null, 2, null, null, null, null, null, null, null, null, null, null, null, null, 'Active', null, null, null, null, null);



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
(5, 'user3', 'icehrm+user3@web-stalk.com', '4048bb914a704a0728549a26b92d8550', NULL,NULL, 'Restricted Admin','["1"]', '2013-01-03 02:58:55', '2013-01-03 02:58:55', '2013-01-03 02:58:55'),
(6, 'user4', 'icehrm+user4@web-stalk.com', '4048bb914a704a0728549a26b92d8550', 5,NULL, 'Manager','', '2013-01-03 02:58:55', '2013-01-03 02:58:55', '2013-01-03 02:58:55'),
(7, 'user5', 'icehrm+user5@web-stalk.com', '4048bb914a704a0728549a26b92d8550', 6,NULL, 'Employee','', '2013-01-03 02:58:55', '2013-01-03 02:58:55', '2013-01-03 02:58:55'),
(8, 'user6', 'icehrm+user6@web-stalk.com', '4048bb914a704a0728549a26b92d8550', 7,NULL, 'Employee','', '2013-01-03 02:58:55', '2013-01-03 02:58:55', '2013-01-03 02:58:55'),
(9, 'user7', 'icehrm+user7@web-stalk.com', '4048bb914a704a0728549a26b92d8550', 8,NULL, 'Employee','', '2013-01-03 02:58:55', '2013-01-03 02:58:55', '2013-01-03 02:58:55'),
(10, 'user8', 'icehrm+user8@web-stalk.com', '4048bb914a704a0728549a26b92d8550', 9,NULL, 'Employee','', '2013-01-03 02:58:55', '2013-01-03 02:58:55', '2013-01-03 02:58:55'),
(11, 'user9', 'icehrm+user9@web-stalk.com', '4048bb914a704a0728549a26b92d8550', 10,NULL, 'Employee','', '2013-01-03 02:58:55', '2013-01-03 02:58:55', '2013-01-03 02:58:55');


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


INSERT INTO `Settings` (`name`, `value`, `description`, `meta`, `category`) VALUES
('Instance : ID', '0847429146712c108e23c435e8f93b4d', '','','Instance'),
('Instance: Key', 'UQHEYBx9H1eNR66nhNCNCz1WCDDhkjtx1OuJbO3ZQMt+8tfSGvuOH/YEHntRajY=', '','', 'Instance');

INSERT INTO `EmployeeTeams` (`id`,`name`, `description`, `lead`, `department`) VALUES
(1,'alpha', 'designing team', 5,2),
(2,'beta', 'marketing team', 4,3);

INSERT INTO `EmployeeTeamMembers` (`id`,`team`, `member`, `role`) VALUES
(1,1, 3, '1'),
(2,2,6, '2');



INSERT INTO `EmployeeExpenses` (`employee`, `expense_date`, `payment_method` , `payee` , `category` , `notes` , `amount` , `currency` ,`transaction_no`) VALUES
(1, '2018-02-25', 1 ,'Kusal',1, 'personal' , 500.00 , 4 ,'A12'),
(3, '2019-01-12', 2 ,'Saman',2, 'personal' , 1500.00 , 6 ,'B34');

INSERT INTO `AssetTypes` (`id`,`name`, `description`) VALUES
(1,'laptop','electronic items' ),
(2,'chair','furniture' );

INSERT INTO `CompanyAssets` (`id`,`code`, `type`,  `employee` , `department` , `description` ) VALUES
(1,'A123', 1, 1 ,1, 'furniture' ),
(2,'B445', 2, 2 ,2, 'computers');

INSERT INTO `Candidates` (`id`,`first_name`, `last_name`,  `hiringStage` , `country`,`jobId` , `gender` ,`home_phone`, `email`, `cv` ) VALUES
(1,'Sahan', 'Perera', 2 ,'LK',1 , 'Male','0116676767', 'sahan@gmail.com', './cv.pdf'),
(2,'Kamal', 'Silva', 3 ,'ES',2 ,'Male','0116780767', 'kamal@gmail.com', './resume.pdf');

UPDATE `EmployeeDocuments` SET attachment = './attchment.pdf'
WHERE id=1;

UPDATE `FieldNameMappings` SET display = 'Form'
WHERE id=1;

INSERT INTO `OvertimeCategories` (`id`,`name`, `created`, `updated`) VALUES (1,'weekday','2019-10-17 15:26:00','2019-10-22 18:26:00'),(2,'weekends', '2019-07-10 15:26:00', '2019-10-03 18:26:00');

INSERT INTO `EmployeeOvertime` (`id`,`employee`, `start_time`,  `end_time` , `category`,`project` ) VALUES
(1, 1, '2019-10-17 15:26:00','2019-10-22 18:26:00',2,3),
(2, 3, '2019-07-10 15:26:00', '2019-10-03 18:26:00',1,1);

INSERT INTO `PayrollEmployees` (`id`,`employee`, `pay_frequency`,  `currency` ) VALUES
(1,2,1, 5),
(2,3,2,7);

INSERT INTO `Forms` (`id`,`name`, `description`,  `items`  ) VALUES
(1,'feedback','give feedbacks to events','[{"name":"feedback","field_label":"feedback"}]'),
(2,'contact','give contact details','[{"name":"phone","field_label":"phone"}]');

INSERT INTO `EmployeeForms` (`id`,`employee`, `form`,  `status` ) VALUES
(1,4, 1,'Pending'),
(2,6,2,'Completed');

INSERT INTO `EmployeeTravelRecords` (`id`,`employee`, `type`,`purpose`, `travel_from`,`travel_to`,`travel_date`,`return_date`,`currency`,`funding`,`status`) VALUES
(1,3,'Plane','company meeting','Colombo','London','2018-07-12 18:09:45','2018-08-01 12:34:45',6,500.00,'Approved'),
(2,4,'Plane','marketing','Colombo','Paris','2018-07-12 18:09:45','2018-08-01 12:34:45',3,1000.00, 'Approved');

INSERT INTO `CustomFields` (`id`,`name`,`type` ,`display`,`field_label`,  `display_order` ,`field_type`) VALUES
(1,'price','CompanyAsset','Form', 'price',1, 'text'),
(2,'value','CompanyAsset','Form', 'value',2, 'select'),
(3,'province','Employee','Form', 'province',1, 'text'),
(4,'title','Employee','Form', 'title',2, 'select'),
(5,'profession','EmployeeTravelRecord','Form','profession',3,'text'),
(6,'title','EmployeeTravelRecord','Form','title',4,'text');


INSERT INTO `DataImportFiles` (`id`,`name`, `data_import_definition`, `file`,`details`) VALUES
(1,'IT department', '1','./file.csv', 'active'),
(2,'HR department', '1','./file2.csv', 'active');


INSERT INTO `CompanyDocuments` (`id`,`name`, `details`, `status`,`attachment`,`share_departments`) VALUES
(1,'salary sheet','monthly','Active','./salary.pdf','Head Office'),
(2,'Cv','','Active','./cv.pdf','Head Office');


UPDATE `LeaveTypes` SET leave_color = '#000000'
WHERE id=1;


INSERT INTO `LeaveStartingBalance` (`id`,`leave_type`, `employee`, `leave_period`,`amount`,`note`) VALUES
(1,2,5,3 ,4 , 'sick leave'),
(2,1,1,1,1, 'travel');


INSERT INTO DeductionGroup (`id`,`name`, `description`) VALUES
(1,'group1','deduction group 1'),
(2,'group2','deduction group 2');


INSERT INTO TrainingSessions (`id`,`name`, `course` ,`scheduled`,`status`) VALUES
(1,'hr', 1, '2019-11-14 21:01:00', 'Approved'),
(2,'finance', 2, '2019-11-14 21:01:00', 'Approved');
