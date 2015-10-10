create table `Country` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`code` char(2) not null default '',
	`namecap` varchar(80) null default '',
	`name` varchar(80) not null default '',
	`iso3` char(3) default null,
	`numcode` smallint(6) default null,
	UNIQUE KEY `code` (`code`),
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`)
) engine=innodb default charset=utf8;

create table `Province` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(40) not null default '',
	`code` char(2) not null default '',
	`country` char(2) not null default 'US',
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	CONSTRAINT `Fk_Province_Country` FOREIGN KEY (`country`) REFERENCES `Country` (`code`),
	primary key  (`id`)
) engine=innodb default charset=utf8;

create table `CurrencyTypes` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`code` varchar(3) not null default '',
	`name` varchar(70) not null default '',
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`),
	UNIQUE KEY `CurrencyTypes_code` (`code`)
) engine=innodb default charset=utf8;

create table `Nationality` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(100) default null,
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`)
) engine=innodb default charset=utf8;

create table `Profiles` (
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
	 `email` varchar(50) default null,
	 `home_phone` varchar(50) default null,
	 `mobile_phone` varchar(50) default null,
	 `supervisor` bigint(20) default null,
	 `data` longtext default null,
	 `created` DATETIME default '0000-00-00 00:00:00',
	 `updated` DATETIME default '0000-00-00 00:00:00',
	 CONSTRAINT `Fk_Profile_Nationality` FOREIGN KEY (`nationality`) REFERENCES `Nationality` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	 CONSTRAINT `Fk_Profile_Country` FOREIGN KEY (`country`) REFERENCES `Country` (`code`) ON DELETE SET NULL ON UPDATE CASCADE,
	 CONSTRAINT `Fk_Profile_Province` FOREIGN KEY (`province`) REFERENCES `Province` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	 CONSTRAINT `Fk_Profile_Supervisor` FOREIGN KEY (`supervisor`) REFERENCES `Profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	 primary key  (`id`)
	 
) engine=innodb default charset=utf8;

create table `Users` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`username` varchar(100) default null,
	`email` varchar(100) default null,
	`password` varchar(100) default null,
	`profile` bigint(20) null,
	`user_level` enum('Admin','Profile','Manager') default NULL,
	`last_login` DATETIME default '0000-00-00 00:00:00',
	`last_update` DATETIME default '0000-00-00 00:00:00',
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	CONSTRAINT `Fk_User_Profile` FOREIGN KEY (`profile`) REFERENCES `Profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
	primary key  (`id`),
	unique key `username` (`username`)
) engine=innodb default charset=utf8;


create table `RestAccessTokens` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`userId` bigint(20) NOT NULL,
	`hash` varchar(32) default null,
	`token` varchar(500) default null,
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`),
	unique key `userId` (`userId`)
) engine=innodb default charset=utf8;

create table `Permissions` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`user_level` enum('Admin','Profile','Manager') default NULL,
	`module_id` bigint(20) NOT NULL,
	`permission` varchar(200) default null,
	`meta` varchar(500) default null,
	`value` varchar(200) default null,
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	UNIQUE KEY `Module_Permission` (`user_level`,`module_id`,`permission`),
	primary key  (`id`)
) engine=innodb default charset=utf8;

create table `DataEntryBackups` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`tableType` varchar(200) default null,
	`data` longtext default null,
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`)
) engine=innodb default charset=utf8;

create table `AuditLog` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`time` datetime default '0000-00-00 00:00:00',
	`user` bigint(20) NOT NULL,
	`ip` varchar(100) NULL,
	`type` varchar(100) NOT NULL,
	`profile` varchar(300) NULL,
	`details` text default null,
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	CONSTRAINT `Fk_AuditLog_Users` FOREIGN KEY (`user`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	primary key  (`id`)
) engine=innodb default charset=utf8;


create table `Notifications` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`time` datetime default '0000-00-00 00:00:00',
	`fromUser` bigint(20) NULL,
	`fromProfile` bigint(20) NULL,
	`toUser` bigint(20) NOT NULL,
	`image` varchar(500) default null,
	`message` text default null,
	`action` text default null,
	`type` varchar(100) NULL,
	`status` enum('Unread','Read') default 'Unread',
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	CONSTRAINT `Fk_Notifications_Users` FOREIGN KEY (`touser`) REFERENCES `Users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	primary key  (`id`),
	KEY `toUser_time` (`toUser`,`time`),
	KEY `toUser_status_time` (`toUser`,`status`,`time`)
) engine=innodb default charset=utf8;


create table `Settings` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NOT NULL,
	`value` text default null,
	`description` text default null,
	`meta` text default null,
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`),
	UNIQUE KEY (`name`)
) engine=innodb default charset=utf8;


create table `Modules` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`menu` varchar(30) NOT NULL,
	`name` varchar(100) NOT NULL,
	`icon` VARCHAR( 50 ) NULL,
	`mod_group` varchar(30) NOT NULL,
	`mod_order` INT(11) NULL,
	`status` enum('Enabled','Disabled') default 'Enabled',
	`version` varchar(10) default '',
	`update_path` varchar(500) default '',
	`user_levels` varchar(500) NOT NULL,
	primary key  (`id`),
	UNIQUE KEY `Modules_name_modgroup` (`name`,`mod_group`)
) engine=innodb default charset=utf8;

create table `Reports` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NOT NULL,
	`details` text default null,
	`parameters` text default null,
	`query` text default null,
	`paramOrder` varchar(500) NOT NULL,
	`type` enum('Query','Class') default 'Query',
	`created` DATETIME default '0000-00-00 00:00:00',
	`updated` DATETIME default '0000-00-00 00:00:00',
	primary key  (`id`),
	UNIQUE KEY `Reports_Name` (`name`)
) engine=innodb default charset=utf8;

create table `Files` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`name` varchar(100) NOT NULL,
	`filename` varchar(100) NOT NULL,
	`profile` bigint(20) NULL,
	`file_group` varchar(100) NOT NULL,
	primary key  (`id`),
	unique key `filename` (`filename`)
) engine=innodb default charset=utf8;