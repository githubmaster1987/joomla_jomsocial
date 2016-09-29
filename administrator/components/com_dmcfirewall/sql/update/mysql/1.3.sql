ALTER TABLE `#__dmcfirewall_stats` ADD COLUMN `last_bad_content_email` int(11) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__dmcfirewall_stats` ADD COLUMN `last_bad_content_pages` int(11) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `#__dmcfirewall_log` CHANGE `additional_information` `additional_information` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;