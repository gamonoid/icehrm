REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
    ('Overtime Report', 'This report list all employee attendance entries by employee with overtime calculations', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'OvertimeReport', '["employee","date_start","date_end"]', 'Class','Time Management','CSV');

REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
    ('Overtime Summary Report', 'This report list all employee attendance entries by employee with overtime calculation summary', '[\r\n[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'OvertimeSummaryReport', '["employee","date_start","date_end"]', 'Class','Time Management','CSV');

REPLACE INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
    ('Overtime Request Report',
     'This report list employee overtime requests by employee, date range, overtime category and project',
     '[[ "employee", {"label":"Employee","type":"select2multi","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],[ "date_start", {"label":"Start Date","type":"date"}],[ "date_end", {"label":"End Date","type":"date"}],[ "category", {"label":"Category","type":"select2","allow-null":true,"remote-source":["OvertimeCategory","id","name"]}],[ "project", {"label":"Project","type":"select2","allow-null":true,"remote-source":["Project","id","name"]}],[ "status", {"label":"Status","type":"select","source":[["NULL","All Statuses"],["Approved","Approved"],["Pending","Pending"],["Rejected","Rejected"],["Cancellation Requested","Cancellation Requested"],["Cancelled","Cancelled"],["Processing","Processing"]]}]]', 'OvertimeRequestReport', '["employee","date_start","date_end","category","project"]', 'Class','Time Management','CSV');



REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('Overtime: Allow Indirect Admins to Approve', '0', 'Allow indirect admins to approve overtime requests','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('Travel: Enable Multi Level Approvals', '0', 'Allow multi level approvals in travel module','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('Overtime: Enable Multi Level Approvals', '0', 'Allow multi level approvals in overtime module','["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('System: Company Structure Managers Enabled', '0', 'Allow Managers to View Employees in Their Company Structure, if They are Assigned as a Head of the Company Structure',
     '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');


REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
    ('System: Child Company Structure Managers Enabled', '0', 'Allow Managers to View Employees in Their Company Structure and all Child Company Structures, if They are Assigned as a Head of the Company Structure',
     '["value", {"label":"Value","type":"select","source":[["1","Yes"],["0","No"]]}]');



delete from PayrollColumns;


INSERT INTO `PayrollColumns` (`id`,`name`,`calculation_hook`,`salary_components`,`deductions`,`add_columns`,`sub_columns`,`editable`,`enabled`,`default_value`,`calculation_columns`,`calculation_function`) VALUES
    (1,'Total Hours','AttendanceUtil_getTimeWorkedHours','','','','','No','Yes','0.00',NULL,NULL),
    (2,'Regular Hours','AttendanceUtil_getRegularWorkedHours','','','','','No','Yes','0.00',NULL,NULL),
    (3,'Overtime Hours','AttendanceUtil_getOverTimeWorkedHours','','','','','No','Yes','0.00',NULL,NULL),
    (4,'Leave Hours','LeaveUtil_getLeaveHours','','','','','No','Yes','0.00',NULL,NULL);


delete from DataImport where name = 'Sage50 Import - Employee Data';

INSERT INTO `DataImport` (`name`, `dataType`, `details`, `columns`, `updated`, `created`) VALUES
    ('Employee Data Import', 'EmployeeDataImporter', '', '[{"name":"employee_id","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"Yes","id":"columns_7"},{"name":"first_name","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_3"},{"name":"middle_name","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_5"},{"name":"last_name","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_6"},{"name":"address1","title":"Address1","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_8"},{"name":"address2","title":"Address2","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_9"},{"name":"home_phone","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_14"},{"name":"mobile_phone","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_15"},{"name":"work_email","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_16"},{"name":"gender","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_17"},{"name":"marital_status","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_18"},{"name":"birthday","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_20"},{"name":"nationality","title":"Nationality","type":"Reference","dependOn":"Nationality","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_22"},{"name":"ethnicity","title":"Ethnicity","type":"Normal","dependOn":"Ethnicity","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_23"},{"name":"EmergencyContact/name","title":"","type":"Attached","dependOn":"EmergencyContact","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_24"},{"name":"EmergencyContact/relationship","title":"","type":"Attached","dependOn":"EmergencyContact","dependOnField":"relationship","isKeyField":"No","idField":"No","id":"columns_25"},{"name":"EmergencyContact/home_phone","title":"","type":"Attached","dependOn":"EmergencyContact","dependOnField":"home_phone","isKeyField":"No","idField":"No","id":"columns_26"},{"name":"ssn_num","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_31"},{"name":"job_title","title":"","type":"Reference","dependOn":"JobTitle","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_32"},{"name":"employment_status","title":"","type":"Reference","dependOn":"EmploymentStatus","dependOnField":"name","isKeyField":"Yes","idField":"No","id":"columns_33"},{"name":"joined_date","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_36"},{"name":"department","title":"","type":"Reference","dependOn":"CompanyStructure","dependOnField":"title","isKeyField":"Yes","idField":"No","id":"columns_38"}]', '2016-06-02 18:56:32', '2016-06-02 18:56:32'),
    ('Attendance Data Import', 'AttendanceDataImporter', '', '[{"name":"employee","title":"","type":"Reference","dependOn":"Employee","dependOnField":"employee_id","isKeyField":"Yes","idField":"No","id":"columns_1"},{"name":"in_time","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_2"},{"name":"out_time","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_3"},{"name":"note","title":"","type":"Normal","dependOn":"NULL","dependOnField":"","isKeyField":"No","idField":"No","id":"columns_4"}]', '2016-08-14 02:51:56', '2016-08-14 02:51:56');



REPLACE INTO `UserReports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
    ('Overtime Report', 'This report list all employee attendance entries by employee with overtime calculations', '[\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'OvertimeReport', '["employee","date_start","date_end"]', 'Class','Time Management','CSV');

REPLACE INTO `UserReports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
    ('Overtime Summary Report', 'This report list all employee attendance entries by employee with overtime calculation summary', '[\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]', 'OvertimeSummaryReport', '["date_start","date_end"]', 'Class','Time Management','CSV');

REPLACE INTO `UserReports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
    ('Client Project Time Report', 'View your time entries for projects under a given client',
     '[\r\n[ "client", {"label":"Select Client","type":"select","allow-null":false,"remote-source":["Client","id","name"]}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]',
     'ClientProjectTimeReport', '["client","date_start","date_end","status"]', 'Class','Time Management','PDF');

REPLACE INTO `UserReports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`,`report_group`,`output`) VALUES
    ('Download Payslips', 'Download your payslips',
     '[\r\n[ "payroll", {"label":"Select Payroll","type":"select","allow-null":false,"remote-source":["Payroll","id","name","getEmployeePayrolls"]}]]',
     'PayslipReport', '["payroll"]', 'Class','Finance','PDF');





INSERT INTO `DeductionGroup` VALUES
    (1,'Sri Lanka Payroll Calculation','');





INSERT INTO `PayslipTemplates` (`id`, `name`, `data`, `status`, `created`, `updated`) VALUES
    (1, 'Sri Lanka - Default Payslip', '[{"type":"Company Logo","payrollColumn":"NULL","label":"","text":"","fontSize":"Normal","fontStyle":"Normal","fontColor":"#000000","status":"Show","id":"data_1"},{"type":"Company Name","payrollColumn":"NULL","label":"","text":"","fontSize":"Normal","fontStyle":"Normal","fontColor":"","status":"Show","id":"data_2"},{"type":"Separators","payrollColumn":"NULL","label":"","text":"","fontSize":"Normal","fontStyle":"Normal","fontColor":"","status":"Show","id":"data_8"},{"type":"Payroll Column","payrollColumn":"5","label":"Basic Salary","text":"","status":"Show","id":"data_3"},{"type":"Payroll Column","payrollColumn":"6","label":"Fixed Allowance","text":"","status":"Show","id":"data_4"},{"type":"Payroll Column","payrollColumn":"7","label":"Gross Pay","text":"","status":"Show","id":"data_11"},{"type":"Separators","payrollColumn":"NULL","label":"","text":"","fontSize":"Normal","fontStyle":"Normal","fontColor":"","status":"Show","id":"data_9"},{"type":"Text","payrollColumn":"NULL","label":"","text":"Deductions","status":"Show","id":"data_13"},{"type":"Payroll Column","payrollColumn":"8","label":"EPF Employee Contribution","text":"","status":"Show","id":"data_6"},{"type":"Payroll Column","payrollColumn":"13","label":"PAYE Tax","text":"","status":"Show","id":"data_14"},{"type":"Payroll Column","payrollColumn":"14","label":"Stamp Duty","text":"","status":"Show","id":"data_15"},{"type":"Payroll Column","payrollColumn":"15","label":"Total Deductions","text":"","status":"Show","id":"data_16"},{"type":"Separators","payrollColumn":"NULL","label":"","text":"","status":"Show","id":"data_17"},{"type":"Text","payrollColumn":"NULL","label":"","text":"Employer Contributions","status":"Show","id":"data_18"},{"type":"Payroll Column","payrollColumn":"9","label":"EPF Employer Contribution","text":"","status":"Show","id":"data_19"},{"type":"Payroll Column","payrollColumn":"7","label":"ETF Employer Contribution","text":"","status":"Show","id":"data_20"},{"type":"Separators","payrollColumn":"7","label":"","text":"","status":"Show","id":"data_21"},{"type":"Text","payrollColumn":"7","label":"","text":"Totals","status":"Show","id":"data_22"},{"type":"Payroll Column","payrollColumn":"11","label":"Total EPF 20%","text":"","status":"Show","id":"data_23"},{"type":"Payroll Column","payrollColumn":"12","label":"Total for PAYE","text":"","status":"Show","id":"data_24"},{"type":"Payroll Column","payrollColumn":"16","label":"Net Salary","text":"","status":"Show","id":"data_25"}]', NULL, '2016-06-29 22:07:12', '2016-06-29 22:07:12');

INSERT INTO `Payroll` VALUES
    (1,'Sri Lanka Payroll Sample',4,1,NULL,'[\"5\",\"8\",\"9\",\"10\",\"6\",\"7\",\"11\",\"12\",\"13\",\"14\",\"15\",\"16\"]','2016-03-01','2016-03-31','Draft', 1);

INSERT INTO `PayrollColumns` VALUES
    (5,'LK - Basic Salary',NULL,'[\"1\"]','[]','[]','[]',5,'No','Yes','0.00',NULL,NULL),
    (6,'LK - Fixed Allowance',NULL,'[\"2\"]','[]','[]','[]',6,'No','Yes','0.00',NULL,NULL),
    (7,'LK - Gross Pay',NULL,'[]','[]','[\"5\",\"6\"]','[]',7,'No','Yes','0.00',NULL,NULL),
    (8,'LK - EPF Employee Contribution',NULL,'[]','[\"1\"]','[]','[]',8,'No','Yes','0.00',NULL,NULL),
    (9,'LK - EPF Employer Contribution',NULL,'[]','[\"2\"]','[]','[]',9,'No','Yes','0.00',NULL,NULL),
    (10,'LK - ETF Employer Contribution',NULL,'[]','[\"3\"]','[]','[]',10,'No','Yes','0.00',NULL,NULL),
    (11,'LK - Total EPF 20%',NULL,'[]','[]','[\"8\",\"9\"]','[]',11,'No','Yes','0.00',NULL,NULL),
    (12,'LK - Total for PAYE',NULL,'[]','[]','[\"7\"]','[]',12,'No','Yes','0.00',NULL,NULL),
    (13,'LK - PAYE Tax',NULL,'[]','[\"4\"]','[]','[]',13,'No','Yes','0.00',NULL,NULL),
    (14,'LK - Stamp Duty',NULL,'[]','[\"5\"]','[]','[]',14,'No','Yes','0.00',NULL,NULL),
    (15,'LK - Total Deductions',NULL,'[]','[]','[\"8\",\"13\",\"14\"]','[]',15,'No','Yes','0.00',NULL,NULL),
    (16,'LK - Salary to Bank',NULL,'[]','[]','[\"7\"]','[\"15\"]',16,'No','Yes','0.00',NULL,NULL);

INSERT INTO `Deductions` VALUES
    (1,'EPF Employee Contribution','[]','[]',7,'[{\"lowerCondition\":\"No Lower Limit\",\"lowerLimit\":\"0\",\"upperCondition\":\"No Upper Limit\",\"upperLimit\":\"0\",\"amount\":\"X*0.08\",\"id\":\"rangeAmounts_1\"}]',1),
    (2,'EPF Employer Contribution','[]','[]',7,'[{\"lowerCondition\":\"No Lower Limit\",\"lowerLimit\":\"0\",\"upperCondition\":\"No Upper Limit\",\"upperLimit\":\"0\",\"amount\":\"X*0.12\",\"id\":\"rangeAmounts_1\"}]',1),
    (3,'ETF Employer Contribution','[]','[]',7,'[{\"lowerCondition\":\"No Lower Limit\",\"lowerLimit\":\"0\",\"upperCondition\":\"No Upper Limit\",\"upperLimit\":\"0\",\"amount\":\"X*0.03\",\"id\":\"rangeAmounts_1\"}]',1),
    (4,'PAYE Tax','[]','[]',12,'[{\"lowerCondition\":\"No Lower Limit\",\"lowerLimit\":\"0\",\"upperCondition\":\"lte\",\"upperLimit\":\"62500\",\"amount\":\"0\",\"id\":\"rangeAmounts_1\"},{\"lowerCondition\":\"gt\",\"lowerLimit\":\"62500\",\"upperCondition\":\"lte\",\"upperLimit\":\"104167\",\"amount\":\"X*0.04 - 2500\",\"id\":\"rangeAmounts_2\"},{\"lowerCondition\":\"gt\",\"lowerLimit\":\"104167\",\"upperCondition\":\"lte\",\"upperLimit\":\"145833\",\"amount\":\"X*0.08 - 6667\",\"id\":\"rangeAmounts_3\"},{\"lowerCondition\":\"gt\",\"lowerLimit\":\"145833\",\"upperCondition\":\"lte\",\"upperLimit\":\"187500\",\"amount\":\"X*0.12-12500\",\"id\":\"rangeAmounts_4\"},{\"lowerCondition\":\"gt\",\"lowerLimit\":\"187500\",\"upperCondition\":\"No Upper Limit\",\"upperLimit\":\"0\",\"amount\":\"X*0.16 - 20000\",\"id\":\"rangeAmounts_5\"}]',1),
    (5,'Stamp Duty','[]','[]',12,'[{\"lowerCondition\":\"No Lower Limit\",\"lowerLimit\":\"0\",\"upperCondition\":\"lte\",\"upperLimit\":\"25000\",\"amount\":\"0\",\"id\":\"rangeAmounts_1\"},{\"lowerCondition\":\"gt\",\"lowerLimit\":\"25000\",\"upperCondition\":\"No Upper Limit\",\"upperLimit\":\"0\",\"amount\":\"25\",\"id\":\"rangeAmounts_2\"}]',1);


INSERT INTO `DeductionGroup` VALUES
    (2,'Ghana Payroll Calculation','');

INSERT INTO `PayslipTemplates` VALUES
    (2,'Ghana Default','[{\"type\":\"Company Name\",\"payrollColumn\":\"NULL\",\"label\":\"\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_1\"},{\"type\":\"Separators\",\"payrollColumn\":\"NULL\",\"label\":\"\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_3\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"105\",\"label\":\"\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_4\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"106\",\"label\":\"\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_5\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"107\",\"label\":\"\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_6\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"112\",\"label\":\"Total\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_7\"},{\"type\":\"Separators\",\"payrollColumn\":\"NULL\",\"label\":\"\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_8\"},{\"type\":\"Text\",\"payrollColumn\":\"NULL\",\"label\":\"\",\"text\":\"Deductions \",\"status\":\"Show\",\"id\":\"data_9\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"109\",\"label\":\"SSNIT (5.5%)\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_10\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"113\",\"label\":\"Tax (1st Level 5%)\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_11\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"114\",\"label\":\"Tax (2nd Level 10%)\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_12\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"115\",\"label\":\"Tax (3rd Level 17.5%)\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_13\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"120\",\"label\":\"Tax (4th Level 25%)\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_14\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"121\",\"label\":\"Overtime Allow. Tax (5%)\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_15\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"122\",\"label\":\"Total (PAYE Tax)\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_16\"},{\"type\":\"Separators\",\"payrollColumn\":\"NULL\",\"label\":\"\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_17\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"123\",\"label\":\"Sub Total\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_18\"},{\"type\":\"Separators\",\"payrollColumn\":\"NULL\",\"label\":\"\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_19\"},{\"type\":\"Payroll Column\",\"payrollColumn\":\"125\",\"label\":\"Netto Monthly Salary\",\"text\":\"\",\"status\":\"Show\",\"id\":\"data_20\"}]',NULL,'2016-08-30 21:53:13','2016-08-30 21:53:13');


INSERT INTO `Payroll` VALUES
    (6,'Ghana Payroll Sample',4,1,NULL,'[\"123\",\"125\",\"106\",\"124\",\"113\",\"114\",\"115\",\"121\",\"107\",\"120\",\"109\",\"105\",\"112\",\"108\",\"122\"]','2016-08-01','2016-08-31','Draft',2);


INSERT INTO `PayrollColumns` VALUES
    (105,'GH - Salary (Basic)',NULL,'[\"1\"]','[]','[]','[]',1,'No','Yes','0.00','',''),
    (106,'GH - Housing Allowance',NULL,'[]','[\"101\"]','[]','[]',2,'No','Yes','0.00','',''),
    (107,'GH - Overtime Allowance',NULL,'[]','[\"102\"]','[]','[]',3,'No','Yes','0.00','',''),
    (108,'GH - Total',NULL,'[]','[]','[\"106\",\"107\",\"105\"]','[]',4,'No','Yes','0.00','',''),
    (109,'GH - SSNIT',NULL,'[]','[\"103\"]','[]','[]',5,'No','Yes','0.00','',''),
    (112,'GH - Taxable Income',NULL,'[]','[]','[\"106\",\"105\"]','[\"109\"]',7,'No','Yes','0.00','',''),
    (113,'GH - Next 108 GHC',NULL,'[]','[\"106\"]','[]','[]',9,'No','Yes','0.00','',''),
    (114,'GH - Next 151 GHC',NULL,'[]','[\"107\"]','[]','[]',10,'No','Yes','0.00','',''),
    (115,'GH - Next 2765 GHC',NULL,'[]','[\"108\"]','[]','[]',11,'No','Yes','0.00','',''),
    (120,'GH - Remaining after 3240 GHC',NULL,'[]','[\"111\"]','[]','[]',12,'No','Yes','0.00','',''),
    (121,'GH - Overtime Allow. Tax',NULL,'[]','[]','[]','[]',13,'No','Yes','0.00','[{\"name\":\"O\",\"column\":\"107\",\"id\":\"calculation_columns_1\"}]','O*0.05'),
    (122,'GH - Total (PAYE Tax)',NULL,'[]','[]','[\"113\",\"114\",\"115\",\"120\",\"121\"]','[]',14,'No','Yes','0.00','',''),
    (123,'GH - Deductions - Sub Total',NULL,'[]','[]','[\"109\",\"122\"]','[]',15,'No','Yes','0.00','',''),
    (125,'GH - Final Total',NULL,'[]','[]','[\"108\"]','[\"123\"]',16,'No','Yes','0.00','','');


INSERT INTO `Deductions` VALUES
    (101,'Housing Allowance (10%)','[]','[\"1\"]',NULL,'[{\"lowerCondition\":\"No Lower Limit\",\"lowerLimit\":0,\"upperCondition\":\"No Upper Limit\",\"upperLimit\":0,\"amount\":\"X*0.1\",\"id\":\"rangeAmounts_1\"}]',2),
    (102,'Overtime Allowance (10%)','[]','[\"1\"]',NULL,'[{\"lowerCondition\":\"No Lower Limit\",\"lowerLimit\":0,\"upperCondition\":\"No Upper Limit\",\"upperLimit\":0,\"amount\":\"X*0.1\",\"id\":\"rangeAmounts_1\"}]',2),
    (103,'SSNIT','[]','[\"1\"]',NULL,'[{\"lowerCondition\":\"No Lower Limit\",\"lowerLimit\":0,\"upperCondition\":\"No Upper Limit\",\"upperLimit\":0,\"amount\":\"X*0.055\",\"id\":\"rangeAmounts_1\"}]',2),
    (104,'Tax - Ghana','[]','[]',NULL,'',2),
    (106,'Next 108 GHC','[]','[]',112,'[{\"lowerCondition\":\"gte\",\"lowerLimit\":\"216\",\"upperCondition\":\"lt\",\"upperLimit\":\"324\",\"amount\":\"X*0.05\",\"id\":\"rangeAmounts_1\"},{\"lowerCondition\":\"gte\",\"lowerLimit\":\"324\",\"upperCondition\":\"No Upper Limit\",\"upperLimit\":0,\"amount\":\"5.4\",\"id\":\"rangeAmounts_2\"}]',2),
    (107,'Next 151 GHC','[]','[]',112,'[{\"lowerCondition\":\"gte\",\"lowerLimit\":\"324\",\"upperCondition\":\"lt\",\"upperLimit\":\"475\",\"amount\":\"(X-324) * 0.1\",\"id\":\"rangeAmounts_1\"},{\"lowerCondition\":\"gte\",\"lowerLimit\":\"259\",\"upperCondition\":\"No Upper Limit\",\"upperLimit\":0,\"amount\":\"15.10\",\"id\":\"rangeAmounts_2\"}]',2),
    (108,'Next 2765 GHC','[]','[]',112,'[{\"lowerCondition\":\"gte\",\"lowerLimit\":\"475\",\"upperCondition\":\"lt\",\"upperLimit\":\"3240\",\"amount\":\"(X - 475) * 0.175\",\"id\":\"rangeAmounts_1\"},{\"lowerCondition\":\"gte\",\"lowerLimit\":\"3240\",\"upperCondition\":\"No Upper Limit\",\"upperLimit\":0,\"amount\":\"483.88\",\"id\":\"rangeAmounts_2\"}]',2),
    (111,'Remaining after 3240 GHC','[]','[]',112,'[{\"lowerCondition\":\"gte\",\"lowerLimit\":\"3240\",\"upperCondition\":\"No Upper Limit\",\"upperLimit\":0,\"amount\":\"(X-3240)*0.25\",\"id\":\"rangeAmounts_1\"}]',2);




ALTER table `Files` add column `size` bigint(20) NULL;

ALTER table `Files` add column `size_text` varchar(20) NULL;

ALTER table `CompanyDocuments` add column `share_departments` varchar(100) NULL;
ALTER table `CompanyDocuments` add column `share_employees` varchar(100) NULL;
ALTER table `CompanyDocuments` add column `share_userlevel` varchar(100) NULL;
ALTER table `CompanyDocuments` modify column `share_userlevel` varchar(100) NULL;

ALTER table `Notifications` add column `employee` bigint(20) NULL;

create table `Forms` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(500) NULL,
  `items` text NULL,
  `created` timestamp NULL default '0000-00-00 00:00:00',
  `updated` timestamp NULL default '0000-00-00 00:00:00',
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `EmployeeForms` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee` bigint(20) NOT NULL,
  `form` bigint(20) NOT NULL,
  `status` enum('Pending','Completed') default 'Pending',
  `created` timestamp NULL default '0000-00-00 00:00:00',
  `updated` timestamp NULL default '0000-00-00 00:00:00',
  CONSTRAINT `Fk_EmployeeForms_Employee` FOREIGN KEY (`employee`) REFERENCES `Employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Fk_EmployeeForms_Forms` FOREIGN KEY (`form`) REFERENCES `Forms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  primary key  (`id`)
) engine=innodb default charset=utf8;

create table `Migrations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `file` varchar(50) NOT NULL,
  `version` int(11) NOT NULL,
  `created` DATETIME default '0000-00-00 00:00:00',
  `updated` DATETIME default '0000-00-00 00:00:00',
  `status` enum('Pending','Up','Down','UpError','DownError') default 'Pending',
  `last_error` varchar(500) NULL,
primary key  (`id`),
unique key `KEY_Migrations_file` (`file`),
index `KEY_Migrations_status` (`status`),
index `KEY_Migrations_version` (`version`)
) engine=innodb default charset=utf8;

