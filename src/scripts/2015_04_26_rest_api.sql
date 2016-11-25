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

REPLACE INTO `Settings` (`name`, `value`, `description`, `meta`) VALUES
('Api: REST Api Enabled', '0',  '','["value", {"label":"Value","type":"select","source":[["0","No"],["1","Yes"]]}]');