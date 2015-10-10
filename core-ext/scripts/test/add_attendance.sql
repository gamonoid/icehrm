SET @employee = 1;
SET @startInTime = '2014-08-01 08:15:15';
SET @startOutTime = '2014-08-01 12:15:15';

INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');

SET @startInTime = DATE_ADD(@startInTime, INTERVAL 1 DAY);
SET @startOutTime = DATE_ADD(@startOutTime, INTERVAL 1 DAY);
INSERT INTO `Attendance` (`employee`, `in_time`, `out_time`, `note`) VALUES
(@employee, FROM_UNIXTIME(UNIX_TIMESTAMP(@startInTime) + FLOOR(0 + (RAND() * 60*60*4))), FROM_UNIXTIME(UNIX_TIMESTAMP(@startOutTime) + FLOOR(0 + (RAND() * 60*60*4))), 'Test Entry');




























