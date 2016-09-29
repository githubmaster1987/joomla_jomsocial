CREATE TABLE IF NOT EXISTS `#__community_activities` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `actor` int(10) unsigned NOT NULL,
  `target` int(10) unsigned NOT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `app` varchar(200) NOT NULL,
  `verb` VARCHAR( 200 ) NOT NULL,
  `cid` int(10) NOT NULL,
  `groupid` INT( 10 ) NULL,
  `eventid` INT( 10 ) NULL,
  `group_access` TINYINT NOT NULL DEFAULT  '0',
  `event_access` TINYINT NOT NULL DEFAULT  '0',
  `created` datetime NOT NULL,
  `access` tinyint(3) unsigned NOT NULL,
  `params` text NOT NULL,
  `points` int(4) NOT NULL default '1',
  `archived` tinyint(3) NOT NULL,
  `location` TEXT NOT NULL,
  `latitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `longitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `comment_id` int(10) NOT NULL,
  `comment_type` varchar(200) NOT NULL,
  `like_id` int(10) NOT NULL,
  `like_type` varchar(200) NOT NULL,
  `actors` text Not NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `actor` (`actor`),
  KEY `target` (`target`),
  KEY `app` (`app`),
  KEY `created` (`created`),
  KEY `archived` (`archived`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_activities_hide` (
  `activity_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_apps` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` int(10) unsigned NOT NULL,
  `apps` varchar(200) NOT NULL,
  `ordering` int(10) unsigned NOT NULL,
  `position` varchar(50) NOT NULL DEFAULT 'content',
  `params` text NOT NULL,
  `privacy` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_userid` (`userid`),
  KEY `idx_user_apps` (`userid`, `apps`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_avatar` (
  `id` int(10) unsigned NOT NULL,
  `apptype` varchar(255) NOT NULL,
  `path` text NOT NULL,
  `type` tinyint(3) unsigned NOT NULL COMMENT '0 = small, 1 = medium, 2=large',
  UNIQUE KEY `id` (`id`,`apptype`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_blocklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `blocked_userid` int(11) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `blocked_userid` (`blocked_userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__community_config` (
  `name` varchar(64) NOT NULL,
  `params` longtext NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_connection` (
  `connection_id` int(11) NOT NULL auto_increment,
  `connect_from` int(11) NOT NULL,
  `connect_to` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `group` int(11) NOT NULL,
  `msg` text NOT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY  (`connection_id`),
  KEY `connect_from` (`connect_from`,`connect_to`,`status`,`group`),
  KEY `idx_connect_to` (`connect_to`),
  KEY `idx_connect_from` (`connect_from`),
  KEY `idx_connect_tofrom` ( `connect_to`, `connect_from` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_connect_users` (
  `connectid` bigint(20) NOT NULL,
  `type` varchar(255) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY (`connectid`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_events` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `parent` INT( 11 ) NOT NULL COMMENT 'parent for recurring event',
  `catid` int(11) unsigned NOT NULL,
  `contentid` int(11) unsigned NULL DEFAULT '0' COMMENT '0 - if type == profile, else it hold the group id',
  `type` varchar(255) NOT NULL DEFAULT 'profile' COMMENT 'profile, group',
  `title` varchar(255) NOT NULL,
  `location` text NOT NULL,
  `summary` text NOT NULL,
  `unlisted` tinyint(1) NOT NULL,
  `description` text NULL,
  `creator` int(11) unsigned NOT NULL,
  `startdate` datetime NOT NULL,
  `enddate` datetime NOT NULL,
  `permission` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '0 - Open (Anyone can mark attendence), 1 - Private (Only invited can mark attendence)',
  `avatar` varchar(255) NULL,
  `thumb` varchar(255) NULL,
  `cover` TEXT NOT NULL,
  `invitedcount` int(11) unsigned NULL DEFAULT '0',
  `confirmedcount` int(11) unsigned NULL DEFAULT '0' COMMENT 'treat this as member count as well',
  `declinedcount` int(11) unsigned NULL DEFAULT '0',
  `maybecount` int(11) unsigned NULL DEFAULT '0',
  `wallcount` int(11) unsigned NULL DEFAULT '0',
  `ticket` int(11) unsigned NULL DEFAULT '0' COMMENT 'Represent how many guest can be joined or invited.',
  `allowinvite` tinyint(1) unsigned NULL DEFAULT '1' COMMENT '0 - guest member cannot invite thier friends to join. 1 - yes, guest member can invite any of thier friends to join.',
  `created` datetime NULL,
  `hits` int(11) unsigned NULL DEFAULT '0',
  `published` int(11) unsigned NULL DEFAULT '1',
  `latitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `longitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `offset` varchar(5) NULL,
  `allday` TINYINT( 11 ) NOT NULL DEFAULT '0',
  `repeat` VARCHAR( 50 ) DEFAULT NULL COMMENT 'null,daily,weekly,monthly',
  `repeatend` DATE NOT NULL,
  `storage` VARCHAR( 64 ) NOT NULL DEFAULT 'file',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_creator` (`creator`),
  KEY `idx_period` (`startdate`, `enddate`),
  KEY `idx_type` (`type`),
  KEY `idx_catid` (`catid`),
  KEY `idx_published` (`published`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_events_category` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `parent` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_events_members` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `eventid` int(11) unsigned NOT NULL,
  `memberid` int(11) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '[Join / Invite]: 0 - [pending approval/pending invite], 1 - [approved/confirmed], 2 - [rejected/declined], 3 - [maybe/maybe], 4 - [blocked/blocked]',
  `permission` tinyint(1) unsigned NOT NULL DEFAULT '3' COMMENT '1 - creator, 2 - admin, 3 - member',
  `invited_by` int(11) unsigned NULL DEFAULT '0',
  `approval` tinyint(1) unsigned NULL DEFAULT '0' COMMENT '0 - no approval required, 1 - required admin approval',
  `created` datetime NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_eventid` (`eventid`),
  KEY `idx_status` (`status`),
  KEY `idx_invitedby` (`invited_by`),
  KEY `idx_permission` (`eventid`, `permission`),
  KEY `idx_member_event` (`eventid`, `memberid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_featured` (
  `id` int(11) NOT NULL auto_increment,
  `cid` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__community_fields` (
  `id` int(10) NOT NULL auto_increment,
  `type` varchar(255) NOT NULL,
  `ordering` int(11) default '0',
  `published` tinyint(1) NOT NULL default '0',
  `min` int(5) NOT NULL,
  `max` int(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `tips` text NOT NULL,
  `visible` tinyint(1) default '0',
  `required` tinyint(1) default '0',
  `searchable` tinyint(1) default '1',
  `registration` tinyint(1) default '1',
  `options` text,
  `fieldcode` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fieldcode` (`fieldcode`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_fields_values` (
  `id` int(10) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `field_id` int(10) NOT NULL,
  `value` text NOT NULL,
  `access` TINYINT( 3 ) NOT NULL DEFAULT '0',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_user_fieldid` (`user_id`, `field_id`),
  KEY ( `access` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_groups` (
  `id` int(11) NOT NULL auto_increment,
  `published` tinyint(1) NOT NULL,
  `ownerid` int(11) NOT NULL,
  `categoryid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `summary` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `approvals` tinyint(1) NOT NULL,
  `unlisted` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `avatar` text NOT NULL,
  `thumb` text NOT NULL,
  `cover` TEXT NOT NULL,
  `discusscount` int(11) NOT NULL default '0',
  `wallcount` int(11) NOT NULL default '0',
  `membercount` int(11) NOT NULL default '0',
  `params` TEXT NOT NULL,
  `hits` int(11) NOT NULL,
  `storage` VARCHAR( 64 ) NOT NULL DEFAULT 'file',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_groups_bulletins` (
  `id` int(11) NOT NULL auto_increment,
  `groupid` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date` datetime NOT NULL,
  `params` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `groupid` (`groupid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_groups_category` (
  `id` int(11) NOT NULL auto_increment,
  `parent` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_groups_discuss` (
  `id` int(11) NOT NULL auto_increment,
  `parentid` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `title` text NOT NULL,
  `message` text NOT NULL,
  `lastreplied` datetime NOT NULL,
  `lock` tinyint(1) NOT NULL DEFAULT '0',
  `params` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `groupid` (`groupid`),
  KEY `parentid` (`parentid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_groups_invite` (
`groupid` INT( 11 ) NOT NULL ,
`userid` INT( 11 ) NOT NULL ,
`creator` INT( 11 ) NOT NULL
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_groups_members` (
  `groupid` int(11) NOT NULL,
  `memberid` int(11) NOT NULL,
  `approved` int(11) NOT NULL,
  `permissions` int(1) NOT NULL,
  KEY `groupid` (`groupid`),
  KEY `idx_memberid` (`memberid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_invitations` (
  `id` int(11) NOT NULL auto_increment,
  `callback` varchar(255) NOT NULL,
  `cid` int(11) NOT NULL,
  `users` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__community_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element` varchar(200) NOT NULL,
  `uid` int(10) NOT NULL,
  `like` TEXT NOT NULL ,
  `dislike` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `element` (`element`,`uid`)
) ENGINE = MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_location_cache` (
  `address` varchar(255) NOT NULL,
  `latitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `longitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `data` text NOT NULL,
  `status` varchar(2) NOT NULL,
  `created` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_mailq` (
  `id` int(11) NOT NULL auto_increment,
  `recipient` text NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `template` varchar(64) NOT NULL,
  `email_type` TEXT,
  `params` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_memberlist` (
  `id` int(11) NOT NULL auto_increment,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `condition` varchar(255) NOT NULL,
  `avataronly` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__community_memberlist_criteria` (
  `id` int(11) NOT NULL auto_increment,
  `listid` int(11) NOT NULL,
  `field` varchar(255) NOT NULL,
  `condition` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `listid` (`listid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__community_msg` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `from` int(10) unsigned NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `deleted` tinyint(3) unsigned default '0',
  `from_name` varchar(45) NOT NULL,
  `posted_on` datetime default NULL,
  `subject` tinytext NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_msg_recepient` (
  `msg_id` int(10) unsigned NOT NULL,
  `msg_parent` int(10) unsigned NOT NULL default '0',
  `msg_from` int(10) unsigned NOT NULL,
  `to` int(10) unsigned NOT NULL,
  `bcc` tinyint(3) unsigned default '0',
  `is_read` tinyint(3) unsigned default '0',
  `deleted` tinyint(3) unsigned default '0',
  UNIQUE KEY `un` (`msg_id`,`to`),
  KEY `msg_id` (`msg_id`),
  KEY `to` (`to`),
  KEY `idx_isread_to_deleted` (`is_read`, `to`, `deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_oauth` (
  `userid` int(11) NOT NULL,
  `requesttoken` text NOT NULL,
  `accesstoken` text NOT NULL,
  `app` varchar(255) NOT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_photos` (
  `id` int(11) NOT NULL auto_increment,
  `albumid` int(11) NOT NULL,
  `caption` text NOT NULL,
  `published` tinyint(1) NOT NULL,
  `creator` int(11) NOT NULL,
  `permissions` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `thumbnail` varchar(255) NOT NULL,
  `original` varchar(255) NOT NULL,
  `filesize` int(11) NOT NULL DEFAULT '0',
  `storage` varchar(64) NOT NULL default 'file',
  `created` datetime NOT NULL,
  `ordering` INT( 11 ) NOT NULL DEFAULT '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  `status` VARCHAR( 200 ) NOT NULL,
  `params` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `albumid` (`albumid`),
  KEY `idx_storage` ( `storage` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_photos_albums` (
  `id` int(11) NOT NULL auto_increment,
  `photoid` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `permissions` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `path` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `groupid` INT( 11 ) NOT NULL DEFAULT '0',
  `eventid` INT(11) NOT NULL DEFAULT '0',
  `hits` int(11) NOT NULL DEFAULT '0',
  `location` TEXT NOT NULL DEFAULT '',
  `latitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `longitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `default` TINYINT( 1 ) NOT NULL DEFAULT '0',
  `params` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `creator` (`creator`),
  KEY `idx_type` (`type`),
  KEY `idx_albumtype` (`id`, `type`),
  KEY `idx_creatortype` (`creator`, `type`),
  KEY `idx_groupid` (`groupid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_photos_tag` (
  `id` int(11) NOT NULL auto_increment,
  `photoid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `position` varchar(50) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `idx_photoid` (`photoid`),
  KEY `idx_userid` (`userid`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_photo_user` (`photoid`, `userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__community_photos_tokens` (
  `userid` int(11) NOT NULL,
  `token` varchar(200) NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_profiles` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `approvals` tinyint(3) NOT NULL,
  `published` tinyint(3) NOT NULL,
  `avatar` text NOT NULL,
  `watermark` text NOT NULL,
  `watermark_hash` VARCHAR (255) NOT NULL,
  `watermark_location` text NOT NULL,
  `thumb` text NOT NULL,
  `created` datetime NOT NULL,
  `create_groups` tinyint(1) DEFAULT '1',
  `create_events` INT NULL DEFAULT '1',
  `profile_lock` TINYINT( 1 ) NULL DEFAULT '0',
  `ordering` INT(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `approvals` (`approvals`,`published`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__community_profiles_fields` (
  `id` int(11) NOT NULL auto_increment,
  `parent` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `multiprofile_id` (`parent`),
  KEY `field_id` (`field_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__community_register` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `token` varchar(200) NOT NULL,
  `name` varchar(255) NOT NULL,
  `firstname` varchar(180) NOT NULL,
  `lastname` varchar(180) NOT NULL,
  `username` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created` datetime NULL,
  `ip` varchar(25) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_register_auth_token` (
  `token` varchar(200) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `auth_key` varchar(200) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`token`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_reports` (
  `id` int(11) NOT NULL auto_increment,
  `uniquestring` varchar(200) NOT NULL,
  `link` varchar(255) NOT NULL,
  `status` int(1) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_reports_actions` (
  `id` int(11) NOT NULL auto_increment,
  `reportid` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `method` varchar(255) NOT NULL,
  `parameters` varchar(255) NOT NULL,
  `defaultaction` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_reports_reporter` (
  `reportid` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `ip` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_storage_s3` (
  `storageid` VARCHAR( 255 ) NOT NULL ,
  `resource_path` VARCHAR( 255 ) NOT NULL ,
  UNIQUE (`storageid`)
) ENGINE=MYISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_tags` (
	`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
	`element` VARCHAR( 200 ) NOT NULL ,
	`userid` INT( 11 ) NOT NULL ,
	`cid` INT( 11 ) NOT NULL ,
	`created` DATETIME NOT NULL ,
	`tag` VARCHAR( 200 ) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_tags_words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(200) NOT NULL,
  `count` int(11) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_userpoints` (
  `id` int(11) NOT NULL auto_increment,
  `rule_name` varchar(255) NOT NULL default '',
  `rule_description` text NOT NULL default '',
  `rule_plugin` varchar(255) NOT NULL default '',
  `action_string` varchar(255) NOT NULL default '',
  `component` varchar(255) NOT NULL default '',
  `access` tinyint(1) NOT NULL default '1',
  `points` int(11) NOT NULL default '0',
  `published` tinyint(1) NOT NULL default '0',
  `system` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_users` (
  `userid` int(11) NOT NULL,
  `status` text NOT NULL,
  `status_access` int(11) NOT NULL default '0',
  `points` int(11) NOT NULL,
  `posted_on` datetime NOT NULL,
  `avatar` text NOT NULL,
  `thumb` text NOT NULL,
  `cover` TEXT NOT NULL,
  `invite` int(11) NOT NULL default '0',
  `params` text NOT NULL,
  `view` int(11) NOT NULL default '0',
  `friends` text NOT NULL ,
  `groups` text NOT NULL,
  `events` text NOT NULL,
  `friendcount` int(11) NOT NULL default '0',
  `alias` VARCHAR( 255 ) NOT NULL,
  `latitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `longitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `profile_id` int(11) NOT NULL DEFAULT '0',
  `storage` VARCHAR( 64 ) NOT NULL DEFAULT 'file',
  `watermark_hash` VARCHAR(255) NOT NULL,
  `search_email` TINYINT( 1 ) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_user_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `status` text NOT NULL,
  `posted_on` int(11) NOT NULL,
  `location` text NOT NULL,
  `latitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `longitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_videos` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(200) NOT NULL,
  `type` varchar(200) NOT NULL DEFAULT 'file',
  `video_id` varchar(200) DEFAULT NULL,
  `description` text NOT NULL,
  `creator` int(11) unsigned NOT NULL,
  `creator_type` varchar(200) NOT NULL DEFAULT 'user',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `permissions` varchar(255) NOT NULL DEFAULT '0',
  `category_id` int(11) unsigned NOT NULL,
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `featured` tinyint(3) NOT NULL DEFAULT '0',
  `duration` float unsigned DEFAULT '0',
  `status` varchar(200) NOT NULL DEFAULT 'pending',
  `thumb` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `groupid` INT(11) unsigned NOT NULL DEFAULT '0',
  `eventid` INT(11) unsigned NOT NULL DEFAULT '0',
  `filesize` INT(11) NOT NULL DEFAULT '0',
  `storage` varchar(64) NOT NULL default 'file',
  `location` TEXT NOT NULL DEFAULT '',
  `latitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `longitude` FLOAT( 10, 6 ) NOT NULL DEFAULT '255',
  `params` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `creator` (`creator`),
  KEY `idx_groupid` (`groupid`),
  KEY `idx_storage` ( `storage` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_videos_category` (
  `id` int(11) NOT NULL auto_increment,
  `parent` INT NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` TEXT NOT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_wall` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `contentid` int(10) unsigned NOT NULL default '0',
  `post_by` int(10) unsigned NOT NULL default '0',
  `ip` varchar(45) NOT NULL,
  `comment` text NOT NULL,
  `date` varchar(45) NOT NULL,
  `published` tinyint(1) unsigned NOT NULL,
  `type` varchar(200) NOT NULL,
  `params` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `contentid` (`contentid`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#__community_notifications` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`actor` INT( 11 ) NOT NULL ,
`target` INT( 11 ) NOT NULL ,
`content` TEXT NOT NULL ,
`type` VARCHAR( 200 ) NOT NULL,
`cmd_type` TEXT NOT NULL,
`status` TINYINT NOT NULL,
`created` DATETIME NOT NULL ,
`params` TEXT NOT NULL,
 KEY `created` (`created`),
 KEY `status` (`status`),
 KEY `type` (`type`),
 KEY `target` (`target`),
 KEY `actor` (`actor`)
) ENGINE = MYISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__community_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `groupid` int(11) NOT NULL,
  `discussionid` int(11) NOT NULL,
  `bulletinid` int(11) NOT NULL,
  `eventid` int(11) NOT NULL,
  `profileid` int(11) NOT NULL,
  `messageid` int(11) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `filesize` int(11) NOT NULL,
  `hits` int(11) NOT NULL,
  `storage` varchar(64) NOT NULL,
  `creator` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discussionid` (`discussionid`),
  KEY `groupid` (`groupid`),
  KEY `creator` (`creator`),
  KEY `bulletinid` (`bulletinid`)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_videos_tag` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`videoid` INT( 11 ) NOT NULL ,
`userid` INT( 11 ) NOT NULL ,
`position` TEXT NOT NULL ,
`created_by` INT( 11 ) NOT NULL ,
`created` datetime NOT NULL
) ENGINE = MYISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__community_engagement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `week` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__community_moods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(128) NOT NULL,
  `image` varchar(256) DEFAULT NULL,
  `custom` tinyint(4) NOT NULL,
  `published` tinyint(4) NOT NULL DEFAULT '1',
  `allowcustomtext` tinyint(4) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__community_moods` (`id`, `title`, `description`, `image`, `custom`, `published`, `allowcustomtext`) VALUES
  (1, 'happy', 'COM_COMMUNITY_MOOD_HAPPY', NULL, 0, 1, 0),
  (2, 'meh', 'COM_COMMUNITY_MOOD_MEH', NULL, 0, 1, 0),
  (3, 'sad', 'COM_COMMUNITY_MOOD_SAD', NULL, 0, 1, 0),
  (4, 'loved', 'COM_COMMUNITY_MOOD_LOVED', NULL, 0, 1, 0),
  (5, 'excited', 'COM_COMMUNITY_MOOD_EXCITED', NULL, 0, 1, 0),
  (6, 'pretty', 'COM_COMMUNITY_MOOD_PRETTY', NULL, 0, 1, 0),
  (7, 'tired', 'COM_COMMUNITY_MOOD_TIRED', NULL, 0, 1, 0),
  (8, 'angry', 'COM_COMMUNITY_MOOD_ANGRY', NULL, 0, 1, 0),
  (9, 'speachless', 'COM_COMMUNITY_MOOD_SPEACHLESS', NULL, 0, 1, 0),
  (10, 'shocked', 'COM_COMMUNITY_MOOD_SHOCKED', NULL, 0, 1, 0),
  (11, 'irretated', 'COM_COMMUNITY_MOOD_IRRETATED', NULL, 0, 1, 0),
  (12, 'sick', 'COM_COMMUNITY_MOOD_SICK', NULL, 0, 1, 0),
  (13, 'annoyed', 'COM_COMMUNITY_MOOD_ANNOYED', NULL, 0, 1, 0),
  (14, 'relieved', 'COM_COMMUNITY_MOOD_RELIEVED', NULL, 0, 1, 0),
  (15, 'blessed', 'COM_COMMUNITY_MOOD_BLESSED', NULL, 0, 1, 0),
  (16, 'bored', 'COM_COMMUNITY_MOOD_BORED', NULL, 0, 1, 0),
  (19, 'COM_COMMUNITY_MOOD_SHORT_HAPPY', 'COM_COMMUNITY_MOOD_HAPPY', 'png', 1, 1, 0),
  (20, 'COM_COMMUNITY_MOOD_SHORT_SAD', 'COM_COMMUNITY_MOOD_SAD', 'png', 1, 1, 0),
  (21, 'COM_COMMUNITY_MOOD_SHORT_COOL', 'COM_COMMUNITY_MOOD_COOL', 'png', 1, 1, 0),
  (22, 'COM_COMMUNITY_MOOD_SHORT_IRRITATED', 'COM_COMMUNITY_MOOD_IRRITATED', 'png', 1, 1, 0),
  (23, 'COM_COMMUNITY_MOOD_SHORT_ANNOYED', 'COM_COMMUNITY_MOOD_ANNOYED', 'png', 1, 1, 0),
  (24, 'COM_COMMUNITY_MOOD_SHORT_SHOCKED', 'COM_COMMUNITY_MOOD_SHOCKED', 'png', 1, 1, 0),
  (25, 'COM_COMMUNITY_MOOD_SHORT_AMUSED', 'COM_COMMUNITY_MOOD_AMUSED', 'png', 1, 1, 0),
  (26, 'COM_COMMUNITY_MOOD_SHORT_SPEECHLESS', 'COM_COMMUNITY_MOOD_SPEECHLESS', 'png', 1, 1, 0),
  (27, 'COM_COMMUNITY_MOOD_SHORT_RICH', 'COM_COMMUNITY_MOOD_RICH', 'png', 1, 1, 0),
  (28, 'COM_COMMUNITY_MOOD_SHORT_CHEEKY', 'COM_COMMUNITY_MOOD_CHEEKY', 'png', 1, 1, 0),
  (29, 'COM_COMMUNITY_MOOD_SHORT_ANGRY', 'COM_COMMUNITY_MOOD_ANGRY', 'png', 1, 1, 0),
  (30, 'COM_COMMUNITY_MOOD_SHORT_HUNGRY', 'COM_COMMUNITY_MOOD_HUNGRY', 'png', 1, 1, 0),
  (31, 'COM_COMMUNITY_MOOD_SHORT_FESTIVE', 'COM_COMMUNITY_MOOD_FESTIVE', 'png', 1, 1, 0),
  (32, 'COM_COMMUNITY_MOOD_SHORT_ROYAL', 'COM_COMMUNITY_MOOD_ROYAL', 'png', 1, 1, 0),
  (33, 'COM_COMMUNITY_MOOD_SHORT_LOVE', 'COM_COMMUNITY_MOOD_LOVE', 'png', 1, 1, 0),
  (34, 'COM_COMMUNITY_MOOD_SHORT_AFRAID', 'COM_COMMUNITY_MOOD_AFRAID', 'png', 1, 1, 0),
  (35, 'COM_COMMUNITY_MOOD_SHORT_POWERFUL', 'COM_COMMUNITY_MOOD_POWERFUL', 'png', 1, 1, 0),
  (36, 'COM_COMMUNITY_MOOD_SHORT_INVISIBLE', 'COM_COMMUNITY_MOOD_INVISIBLE', 'png', 1, 1, 0),
  (37, 'COM_COMMUNITY_MOOD_SHORT_SWEET', 'COM_COMMUNITY_MOOD_SWEET', 'png', 1, 1, 0),
  (38, 'COM_COMMUNITY_MOOD_SHORT_THIRSTY', 'COM_COMMUNITY_MOOD_THIRSTY', 'png', 1, 1, 0),
  (39, 'COM_COMMUNITY_MOOD_SHORT_CLEAN', 'COM_COMMUNITY_MOOD_CLEAN', 'png', 1, 1, 0),
  (40, 'COM_COMMUNITY_MOOD_SHORT_WATCHING', 'COM_COMMUNITY_MOOD_WATCHING', 'png', 1, 1, 0),
  (41, 'COM_COMMUNITY_MOOD_SHORT_BORED', 'COM_COMMUNITY_MOOD_BORED', 'png', 1, 1, 0),
  (42, 'COM_COMMUNITY_MOOD_SHORT_BUMMED', 'COM_COMMUNITY_MOOD_BUMMED', 'png', 1, 1, 0),
  (43, 'COM_COMMUNITY_MOOD_SHORT_INNOVATIVE', 'COM_COMMUNITY_MOOD_INNOVATIVE', 'png', 1, 1, 0),
  (44, 'COM_COMMUNITY_MOOD_SHORT_LUCKY', 'COM_COMMUNITY_MOOD_LUCKY', 'png', 1, 1, 0),
  (45, 'COM_COMMUNITY_MOOD_SHORT_FOCUSED', 'COM_COMMUNITY_MOOD_FOCUSED', 'png', 1, 1, 0),
  (46, 'COM_COMMUNITY_MOOD_SHORT_SURFING', 'COM_COMMUNITY_MOOD_SURFING', 'png', 1, 1, 0);

CREATE TABLE IF NOT EXISTS `#__community_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `points` int(11) NOT NULL DEFAULT '0',
  `image` varchar(256) NOT NULL,
  `published` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `points` (`points`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__community_badges` (`id`, `title`, `points`, `image`, `published`) VALUES
(1, '0', 0, 'png', 1),
(2, '50', 50, 'png', 1),
(3, '100', 100, 'png', 1),
(4, '200', 200, 'png', 1),
(5, '350', 350, 'png', 1),
(6, '600', 600, 'png', 1);

CREATE TABLE IF NOT EXISTS `#__community_theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(32) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB;

REPLACE INTO `#__community_theme` (`key`, `value`) VALUES
  ('scss-default', '{"colors":{"scss-color-primary":"5677FC","scss-color-secondary":"259B24","scss-color-neutral":"ECF0F1","scss-color-important":"E74C3C","scss-color-info":"E67E22","scss-color-background":"ECF0F1","scss-color-toolbar":"F5F7F7","scss-color-focus-background":"FFFFFF","scss-color-postbox":"FFFFFF","scss-color-postbox-tab":"F5F5F5","scss-color-module-background":"FFFFFF","scss-color-moduletab-background":"E0E7E8","scss-color-dropdown-background":"FFFFFF","scss-color-dropdown-border":"E3E5E7"},"general":{"scss-stream-position":"right","scss-button-style":"flat","scss-avatar-shape":"circle","scss-avatar-style":"bordered"}}');

CREATE TABLE IF NOT EXISTS `#__community_hashtag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(128) CHARACTER SET utf8 NOT NULL,
  `params` text CHARACTER SET utf8 NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__community_profile_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  `date` date NOT NULL,
  `params` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__community_photo_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  `date` date NOT NULL,
  `params` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__community_video_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  `date` date NOT NULL,
  `params` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__community_group_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  `date` date NOT NULL,
  `params` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__community_event_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `eid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `count` int(11) NOT NULL,
  `date` date NOT NULL,
  `params` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__community_digest_email` (
`user_id` INT(11) NOT NULL ,
`total_sent` INT(11) NOT NULL ,
`last_sent` TIMESTAMP NOT NULL ,
PRIMARY KEY (`user_id`) ) ENGINE = MyISAM;