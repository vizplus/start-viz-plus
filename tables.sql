CREATE TABLE `bruteforce_check` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`time` int(11) NOT NULL,
	`ip` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `codes` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`code` varchar(128) NOT NULL,
	`amount` decimal(8,2) NOT NULL,
	`created` int(11) NOT NULL,
	`viz_price` decimal(8,3) NOT NULL DEFAULT 0.000,
	`viz_claimed` decimal(8,3) NOT NULL DEFAULT 0.000,
	`claimed` int(11) DEFAULT NULL,
	`user` varchar(25) DEFAULT NULL,
	`tx` blob DEFAULT NULL,
	`in_tokens` tinyint(1) NOT NULL DEFAULT 0,
	`status` tinyint(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;