use phpactiverecord;

create table `Files` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NOT NULL,
	`filename` varchar(100) NOT NULL,
	`employee` bigint(20) NULL,
	`file_group` varchar(100) NOT NULL,
	`size` bigint(20) NULL,
	`size_text` varchar(20) NULL,
	primary key  (`id`),
	unique key `filename` (`filename`)
) engine=innodb default charset=utf8;
