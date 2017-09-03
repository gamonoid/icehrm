INSERT INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`) VALUES
('Active Employee Report', 'This report list employees who are currently active based on joined date and termination date ', 
'[\r\n[ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}]\r\n]',
 'ActiveEmployeeReport', 
 '["department"]', 'Class');

 
INSERT INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`) VALUES
('New Hires Employee Report', 'This report list employees who are joined between given two dates ', 
'[[ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]',
 'NewHiresEmployeeReport', 
 '["department","date_start","date_end"]', 'Class');
 
INSERT INTO `Reports` (`name`, `details`, `parameters`, `query`, `paramOrder`, `type`) VALUES
('Terminated Employee Report', 'This report list employees who are terminated between given two dates ', 
'[[ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"],"allow-null":true}],\r\n[ "date_start", {"label":"Start Date","type":"date"}],\r\n[ "date_end", {"label":"End Date","type":"date"}]\r\n]',
 'TerminatedEmployeeReport', 
 '["department","date_start","date_end"]', 'Class');