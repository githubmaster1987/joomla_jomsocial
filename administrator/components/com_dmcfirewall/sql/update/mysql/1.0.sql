CREATE TABLE IF NOT EXISTS `#__dmcfirewall_login` (
  `id` TINYINT NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(50) NOT NULL,
  `reason` varchar(75) NOT NULL,
  `time_date` varchar(50) NOT NULL,
  `time_stamp` int(11) NOT NULL,
  `username` VARCHAR(150) NOT NULL,
  `password` VARCHAR(200) NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARACTER SET utf8 AUTO_INCREMENT=1 ;

#ALTER TABLE `#__dmcfirewall_stats` ADD COLUMN `bad_login_attempts` smallint(6) NOT NULL DEFAULT '0' AFTER `hack_attempts_prevented`;