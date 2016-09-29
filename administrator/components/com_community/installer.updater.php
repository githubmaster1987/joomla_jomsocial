<?php
    /**
     * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
// Disallow direct access to this file
    defined('_JEXEC') or die('Restricted access');

    require_once JPATH_ROOT . '/administrator/components/com_community/models/configuration.php';
    require_once JPATH_ROOT . '/components/com_community/libraries/parameter.php';

    abstract class communityInstallerUpdate
    {
        /**
         * Method to check if column exitst
         * @param $tablename
         * @param $columname
         * @return true/false
         */

        private static function _isExistTableColumn($tablename, $columname)
        {
            $db = JFactory::getDBO();

            $query = 'SHOW FIELDS FROM ' . $db->quoteName($tablename);
            $db->setQuery($query);

            $resutl = $db->loadObjectList();

            foreach ($resutl as $_result) {
                if ($_result->Field == $columname) {
                    return false;
                }
            }

            return true;
        }

        /**
         * Method to check if Menu is exist
         * @return true/false
         */

        private static function _isExistMenu()
        {
            $db = JFactory::getDBO();

            $query = 'SELECT COUNT(*) FROM ' . $db->quoteName('#__menu_types') . ' WHERE ';
            $query .= $db->quoteName('menutype') . '=' . $db->Quote('jomsocial');

            $db->setQuery($query);

            return $db->loadResult() > 0;
        }

        /**
         * Get Current Used template
         * @return [string] [return template name]
         */
        private static function _getCurrentTemplate()
        {
            $configuration = new CommunityModelConfiguration();
            $config = $configuration->getParams();

            return $config->get('template');
        }

        /**
         * Method Update DbVersion
         * @param $dbversion
         */

        public static function updateDBVersion($dbversion)
        {
            $db = JFactory::getDBO();

            $query = 'UPDATE ' . $db->quoteName('#__community_config') . 'SET ';
            $query .= $db->quoteName('params') . ' = ' . $db->quote($dbversion) . ' WHERE ';
            $query .= $db->quoteName('name') . ' = ' . $db->quote('dbversion');

            $db->setQuery($query);
            $db->execute();
        }

        /**
         * Method to insert Db Version
         * @param $dbversion
         */

        public static function insertDBVersion($dbversion)
        {
            $db =& JFactory::getDBO();

            $query = 'INSERT INTO ' . $db->quoteName('#__community_config')
                . '(' . $db->quoteName('name') . ', ' . $db->quoteName('params') . ')'
                . 'VALUES(' . $db->quote('dbversion') . ', ' . $db->quote($dbversion) . ')';

            $db->setQuery($query);
            $db->execute();
        }

        /**
         * Method to insert Basic Config
         *
         * */

        public static function insertBasicConfig()
        {
            $db =& JFactory::getDBO();

            $query = 'INSERT INTO ' . $db->quoteName('#__community_config')
                . '(' . $db->quoteName('name') . ', ' . $db->quoteName('params') . ')'
                . 'VALUES(' . $db->quote('config') . ', "")';

            $db->setQuery($query);
            $db->execute();
        }

        public static function update_11()
        {
            $db = JFactory::getDBO();

            // Update Event Table
            if (self::_isExistTableColumn('#__community_events', 'allday')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_events') . ' ADD ' . $db->quoteName('allday') . ' TINYINT( 11 ) NOT NULL DEFAULT ' . $db->quote(0);
                $query .= ' , ADD ' . $db->quoteName('repeat') . ' VARCHAR( 50 ) DEFAULT NULL COMMENT ' . $db->Quote('null,daily,weekly,monthly');
                $query .= ' , ADD ' . $db->quoteName('repeatend') . ' DATE NOT NULL';
                $query .= ' , ADD ' . $db->quoteName('parent') . ' INT( 11 ) NOT NULL COMMENT ' . $db->Quote('parent for recurring event') . ' AFTER ' . $db->quoteName('id');
                $query .= ' , ADD KEY `idx_catid` (`catid`)';
                $query .= ' , ADD KEY `idx_published` (`published`)';

                $db->setQuery($query);
                $db->execute();
            }

            //Update Community Profile table
            if (self::_isExistTableColumn('#__community_profiles', 'profile_lock')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_profiles')
                    . ' ADD ' . $db->quoteName('profile_lock')
                    . ' TINYINT (1) NULL DEFAULT ' . $db->quote(0)
                    . ' AFTER ' . $db->quoteName('create_events');

                $db->setQuery($query);
                $db->execute();

            }

            //Update Community Groups Discussion table
            if (self::_isExistTableColumn('#__community_groups_discuss', 'params')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_groups_discuss') . ' ADD ' . $db->quoteName('params') . ' TEXT NOT NULL ';

                $db->setQuery($query);
                $db->execute();
            }

            //Update Community Groups Bulletins Table
            if (self::_isExistTableColumn('#__community_groups_bulletins', 'params')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_groups_bulletins') . ' ADD ' . $db->quoteName('params') . ' TEXT NOT NULL ';

                $db->setQuery($query);
                $db->execute();
            }

            //Update Community Register table
            if (self::_isExistTableColumn('#__community_register', 'ip')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_register') . ' CHANGE ' . $db->quoteName('ip') . ' ' . $db->quoteName('ip') . ' VARCHAR( 39 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; ';

                $db->setQuery($query);
                $db->execute();
            }

            //Update Community Register Auth token table
            if (self::_isExistTableColumn('#__community_register_auth_token', 'ip')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_register_auth_token') . ' CHANGE ' . $db->quoteName('ip') . ' ' . $db->quoteName('ip') . ' VARCHAR( 39 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; ';

                $db->setQuery($query);
                $db->execute();
            }

            // Update Menu link to mygroup
            if (self::_isExistMenu()) {
                $query = 'UPDATE ' . $db->quoteName('#__menu') . ' SET ' . $db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_community&view=groups&task=mygroupupdate');
                $query .= ' WHERE ' . $db->quoteName('menutype') . ' = ' . $db->quote('jomsocial');
                $query .= ' AND  ' . $db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_community&view=groups&task=mygroups');
                $query .= ' AND  ' . $db->quoteName('alias') . ' = ' . $db->quote('groups');

                $db->setQuery($query);
                $db->execute();
            }
        }

        public static function update_12()
        {
            $db = JFactory::getDBO();

            if (self::_isExistTableColumn('#__community_photos', 'params')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_photos') . ' ADD ' . $db->quoteName('params') . ' TEXT NOT NULL ';

                $db->setQuery($query);
                $db->execute();
            }

            if (self::_isExistTableColumn('#__community_photos_albums', 'params')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_photos_albums') . ' ADD ' . $db->quoteName('params') . ' TEXT NOT NULL ';

                $db->setQuery($query);
                $db->execute();
            }

            if (self::_isExistTableColumn('#__community_videos', 'params')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_videos') . ' ADD ' . $db->quoteName('params') . ' TEXT NOT NULL ';

                $db->setQuery($query);
                $db->execute();
            }

            $query = 'ALTER TABLE ' . $db->quoteName('#__community_register_auth_token') . ' CHANGE ' . $db->quoteName('ip') . ' ' . $db->quoteName('ip') . ' VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; ';

            $db->setQuery($query);
            $db->execute();

            $query = 'DELETE FROM  ' . $db->quoteName('#__community_activities') . ' WHERE ' . $db->quoteName('title') . ' LIKE ' . $db->quote('%{multiple}%');

            $db->setQuery($query);
            $db->execute();

            if (self::_getCurrentTemplate() == 'blackout') {
                $configuration = new CommunityModelConfiguration();
                $configuration->updateTemplate('default');
            }

            $query = 'UPDATE ' . $db->quoteName('#__community_activities') . 'SET ' . $db->quoteName('app') . ' = ' . $db->quote('albums.featured')
                . ' WHERE ' . $db->quoteName('app') . ' = ' . $db->quote('photos.featured');

            $db->setQuery($query);
            $db->execute();

            if (self::_isExistTableColumn('#__community_activities', 'actors')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_activities') . ' ADD ' . $db->quoteName('actors') . ' TEXT NOT NULL ';

                $db->setQuery($query);
                $db->execute();
            }

            // Delete old app activity string
            $query = 'DELETE FROM ' . $db->quoteName('#__community_activities') . ' WHERE ' . $db->quoteName('app') . '=' . $db->quote('feed') . ' OR ' . $db->quoteName('app') . '=' . $db->quote('friendslocation') . ' OR ' . $db->quoteName('app') . '=' . $db->quote('kunena') . '
				 OR ' . $db->quoteName('app') . '=' . $db->quote('latestphoto') . ' OR ' . $db->quoteName('app') . '=' . $db->quote('myarticles') . ' OR ' . $db->quoteName('app') . '=' . $db->quote('mycontacts') . ' OR ' . $db->quoteName('app') . '=' . $db->quote('mygoogleads') . '
				 OR ' . $db->quoteName('app') . '=' . $db->quote('mytaggedvideos') . ' OR ' . $db->quoteName('app') . '=' . $db->quote('twitter') . ' OR ' . $db->quoteName('app') . '=' . $db->quote('wall');

            $db->setQuery($query);
            $db->execute();

            //Delete old profile avatar string
            $query = 'DELETE FROM  ' . $db->quoteName('#__community_activities') . ' WHERE ' . $db->quoteName('app') . ' LIKE ' . $db->quote('profile') . ' AND ' . $db->quoteName('comment_type') . ' LIKE ' . $db->quote('profile.avatar.upload');

            $db->setQuery($query);
            $db->execute();

            //Delete old groups updated stream
            $query = 'DELETE FROM  ' . $db->quoteName('#__community_activities') . ' WHERE ' . $db->quoteName('app') . ' LIKE ' . $db->quote('groups') . ' AND ' . $db->quoteName('app') . ' LIKE ' . $db->quote('%updated group%');

            $db->setQuery($query);
            $db->execute();

        }

        public static function update_13()
        {
            $db = JFactory::getDBO();

            if (self::_isExistTableColumn('#__community_users', 'cover')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_users') . ' ADD ' . $db->quoteName('cover') . ' TEXT NOT NULL AFTER ' . $db->quoteName('thumb');

                $db->setQuery($query);
                $db->execute();
            }

            if (self::_isExistTableColumn('#__community_groups', 'cover')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_groups') . ' ADD ' . $db->quoteName('cover') . ' TEXT NOT NULL AFTER ' . $db->quoteName('thumb');

                $db->setQuery($query);
                $db->execute();
            }

            if (self::_isExistTableColumn('#__community_events', 'cover')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_events') . ' ADD ' . $db->quoteName('cover') . ' TEXT NOT NULL AFTER ' . $db->quoteName('thumb');

                $db->setQuery($query);
                $db->execute();
            }

            if (self::_isExistTableColumn('#__community_groups', 'hits')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_groups') . ' ADD ' . $db->quoteName('hits') . ' INT NOT NULL';

                $db->setQuery($query);
                $db->execute();
            }

            if (self::_isExistTableColumn('#__community_photos_albums', 'eventid')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_photos_albums') . ' ADD ' . $db->quoteName('eventid') . ' INT NOT NULL AFTER ' . $db->quoteName('groupid');

                $db->setQuery($query);
                $db->execute();
            }

            if (self::_isExistMenu()) {
                $query = 'UPDATE ' . $db->quoteName('#__menu') . ' SET ' . $db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_community&view=groups&task=mygroups')
                    . ' WHERE ' . $db->quoteName('menutype') . ' = ' . $db->quote('jomsocial')
                    . ' AND  ' . $db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_community&view=groups&task=mygroupupdate')
                    . ' AND  ' . $db->quoteName('alias') . ' = ' . $db->quote('groups');
                $db->setQuery($query);
                $db->execute();
            }

            //remove backend double toolbar
            $file = JPATH_ROOT . '/administrator/components/com_community/toolbar.community.php';

            if (JFile::exists($file)) {
                JFile::delete($file);
            }

            //Use default template
            $configuration = new CommunityModelConfiguration();
            $configuration->updateTemplate('default');
        }

        public static function update_14()
        {
            $db = JFactory::getDBO();

            if (self::_isExistTableColumn('#__community_events', 'params')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_events') . ' ADD ' . $db->quoteName('params') . ' TEXT NOT NULL';

                $db->setQuery($query);
                $db->execute();
            }
        }

        public static function update_15()
        {
            $db = JFactory::getDBO();

            if (self::_isExistTableColumn('#__community_wall', 'params')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_wall') . ' ADD ' . $db->quoteName('params') . ' TEXT NOT NULL';

                $db->setQuery($query);
                $db->execute();
            }

            if (self::_isExistTableColumn('#__community_blocklist', 'type')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_blocklist') . ' ADD ' . $db->quoteName('type') . ' VARCHAR(50) NOT NULL';

                $db->setQuery($query);
                $db->execute();
            }

            $query = 'SHOW INDEX FROM ' . $db->quoteName('#__community_config');
            $db->setQuery($query);
            $result = $db->loadAssoc();
            if (!isset($result['Column_name']) || empty($result['Column_name'])) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_config') . ' ADD PRIMARY KEY ( ' . $db->quoteName('name') . ' )';
                $db->setQuery($query);
                $db->execute();
            }
        }

        public static function update_16()
        {
            $db = JFactory::getDBO();

            // add alias to community_user table
            $query = 'ALTER TABLE ' . $db->quoteName('#__community_users') . ' ADD INDEX ( ' . $db->quoteName('alias') . ')';
            $db->setQuery($query);
            $db->execute();

            // add moods table
            $query = 'CREATE TABLE IF NOT EXISTS' . $db->quoteName('#__community_moods') . ' (
          ' . $db->quoteName('id') . ' int(11) NOT NULL AUTO_INCREMENT,
          ' . $db->quoteName('title') . ' varchar(128) NOT NULL,
          ' . $db->quoteName('description') . ' varchar(128) NOT NULL,
          ' . $db->quoteName('image') . 'varchar(256) DEFAULT NULL,
          ' . $db->quoteName('custom') . ' tinyint(4) NOT NULL,
          ' . $db->quoteName('published') . ' tinyint(4) NOT NULL DEFAULT ' . $db->quote(1) . ',
          ' . $db->quoteName('allowcustomtext') . ' tinyint(4) NOT NULL DEFAULT ' . $db->quote(0) . ',
          ' . $db->quoteName('ordering') . ' int(11) NOT NULL DEFAULT ' . $db->quote(0) . ',
          PRIMARY KEY (' . $db->quoteName('id') . ')
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8';

            $db->setQuery($query);
            $db->execute();

            // preset mood ordering by id
            $query = 'UPDATE ' . $db->quoteName('#__community_moods') . ' SET ' .
                $db->quoteName('ordering') . ' = ' . $db->quoteName('id');

            $db->setQuery($query);
            $db->execute();

            // populate preset moods
            $query = 'INSERT IGNORE INTO ' . $db->quoteName('#__community_moods') .
                ' (' . $db->quoteName('id') .
                ', ' . $db->quoteName('title') .
                ', ' . $db->quoteName('description') .
                ', ' . $db->quoteName('image') .
                ', ' . $db->quoteName('custom') .
                ', ' . $db->quoteName('published') .
                ', ' . $db->quoteName('allowcustomtext') . ') VALUES
            (1, ' . $db->quoteName('happy') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_HAPPY') . ', NULL, 0, 1, 0),
              (2, ' . $db->quoteName('meh') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_MEH') . ', NULL, 0, 1, 0),
              (3, ' . $db->quoteName('sad') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_SAD') . ', NULL, 0, 1, 0),
              (4, ' . $db->quoteName('loved') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_LOVED') . ', NULL, 0, 1, 0),
              (5, ' . $db->quoteName('excited') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_EXCITED') . ', NULL, 0, 1, 0),
              (6, ' . $db->quoteName('pretty') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_PRETTY') . ', NULL, 0, 1, 0),
              (7, ' . $db->quoteName('tired') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_TIRED') . ', NULL, 0, 1, 0),
              (8, ' . $db->quoteName('angry') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_ANGRY') . ', NULL, 0, 1, 0),
              (9, ' . $db->quoteName('speachless') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_SPEACHLESS') . ', NULL, 0, 1, 0),
              (10, ' . $db->quoteName('shocked') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_SHOCKED') . ', NULL, 0, 1, 0),
              (11, ' . $db->quoteName('irretated') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_IRRETATED') . ', NULL, 0, 1, 0),
              (12, ' . $db->quoteName('sick') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_SICK') . ', NULL, 0, 1, 0),
              (13, ' . $db->quoteName('annoyed') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_ANNOYED') . ', NULL, 0, 1, 0),
              (14, ' . $db->quoteName('relieved') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_RELIEVED') . ', NULL, 0, 1, 0),
              (15, ' . $db->quoteName('blessed') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_BLESSED') . ', NULL, 0, 1, 0),
              (16, ' . $db->quoteName('bored') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_BORED') . ', NULL, 0, 1, 0),
              (19, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_HAPPY') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_HAPPY') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (20, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_SAD') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_SAD') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (21, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_COOL') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_COOL') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (22, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_IRRITATED') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_IRRITATED') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (23, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_ANNOYED') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_ANNOYED') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (24, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_SHOCKED') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_SHOCKED') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (25, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_AMUSED') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_AMUSED') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (26, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_SPEECHLESS') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_SPEECHLESS') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (27, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_RICH') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_RICH') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (28, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_CHEEKY') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_CHEEKY') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (29, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_ANGRY') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_ANGRY') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (30, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_HUNGRY') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_HUNGRY') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (31, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_FESTIVE') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_FESTIVE') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (32, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_ROYAL') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_ROYAL') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (33, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_LOVE') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_LOVE') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (34, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_AFRAID') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_AFRAID') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (35, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_POWERFUL') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_POWERFUL') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (36, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_INVISIBLE') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_INVISIBLE') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (37, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_SWEET') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_SWEET') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (38, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_THIRSTY') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_THIRSTY') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (39, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_CLEAN') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_CLEAN') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (40, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_WATCHING') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_WATCHING') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (41, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_BORED') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_BORED') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (42, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_BUMMED') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_BUMMED') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (43, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_INNOVATIVE') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_INNOVATIVE') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (44, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_LUCKY') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_LUCKY') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (45, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_FOCUSED') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_FOCUSED') . ', ' . $db->quoteName('png') . ', 1, 1, 0),
              (46, ' . $db->quoteName('COM_COMMUNITY_MOOD_SHORT_SURFING') . ', ' . $db->quoteName('COM_COMMUNITY_MOOD_SURFING') . ', ' . $db->quoteName('png') . ', 1, 1, 0)';


            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
            }


            // create badges table
            $query = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__community_badges') . ' (
              ' . $db->quoteName('id') . ' varchar(64) NOT NULL,
              ' . $db->quoteName('points') . ' int(11) NOT NULL DEFAULT ' . $db->quote(0) . ',
              ' . $db->quoteName('image') . ' varchar(256) NOT NULL,
              ' . $db->quoteName('published') . ' tinyint(4) NOT NULL DEFAULT ' . $db->quote(0) . ',
              PRIMARY KEY (' . $db->quoteName('id') . '),
              UNIQUE KEY ' . $db->quoteName('points') . ' (' . $db->quoteName('points') . ')
            )ENGINE=InnoDB DEFAULT CHARSET=utf8 ';

            $db->setQuery($query);
            $db->execute();

            // If badges table is empty, inject defaults based on old karma config
            $query = 'SELECT * FROM '. $db->quoteName('#__community_badges');
            $db->setQuery( $query );
            $result = $db->loadObject();

            if(empty($result)) {
                $config = CFactory::getConfig();
                $usedPoints=array();

                // grab config for 0,1,2,3,4,5
                for($i=0;$i<6;$i++) {

                    $configKey = 'point'.$i;
                    $configPoints = $config->get($configKey);

                    // Add 1 if there is a duplicate
                    while(in_array($configPoints, $usedPoints)) $configPoints++;

                    // default the first item to zero
                    if($i==0) $configPoints = 0;

                    $query = 'INSERT INTO '. $db->quoteName('#__community_badges')
                        . ' ( '
                        .  $db->quoteName('title')
                        . ', '.$db->quoteName('points')
                        . ', '.$db->quoteName('image')
                        . ', '.$db->quoteName('published')
                        . ') VALUES ('
                        . $db->quote($configPoints)
                        . ', '.$db->quote($configPoints)
                        . ', '.$db->quote('png')
                        . ', '.$db->quote(1)
                        . ')';

                    $db->setQuery($query);
                    $db->execute();

                    //grab ID and copy the old karma file to a new badge file
                    $oldFile=JPATH_ROOT."/components/com_community/assets/karma{$i}.png";
                    $newFile=JPATH_ROOT."/components/com_community/assets/badge_".$db->insertid().".png";

                    JFile::copy($oldFile,$newFile);
                }

            }

            // create theme table
            $query =    'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__community_theme') . ' (
                ' . $db->quoteName('id') . ' int(11) NOT NULL AUTO_INCREMENT,
                ' . $db->quoteName('key') . ' varchar(32) NOT NULL,
                ' . $db->quoteName('value') . ' text NOT NULL,
                PRIMARY KEY (' . $db->quoteName('id') . '),
                UNIQUE KEY ' . $db->quoteName('key') . ' (' . $db->quoteName('key') . ')
            )';

            $db->setQuery($query);
            $db->execute();

            //for default theme scss
            $query = 'REPLACE INTO ' . $db->quoteName('#__community_theme')
                . '(' . $db->quoteName('key') . ', ' . $db->quoteName('value') . ')'
                . 'VALUES(' . $db->quote('scss-default') . ', ' . $db->quote('{"colors":{"scss-color-primary":"5677FC","scss-color-secondary":"259B24","scss-color-neutral":"ECF0F1","scss-color-important":"E74C3C","scss-color-info":"E67E22","scss-color-background":"ECF0F1","scss-color-toolbar":"F5F7F7","scss-color-focus-background":"FFFFFF","scss-color-postbox":"FFFFFF","scss-color-postbox-tab":"F5F5F5","scss-color-module-background":"FFFFFF","scss-color-moduletab-background":"E0E7E8","scss-color-dropdown-background":"FFFFFF","scss-color-dropdown-border":"E3E5E7"},"general":{"scss-stream-position":"right","scss-button-style":"flat","scss-avatar-shape":"circle","scss-avatar-style":"bordered"}}') . ')';
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
            }

            //for s3 storage
            $query = 'ALTER TABLE ' . $db->quoteName('#__community_events') . ' ADD  ' . $db->quoteName('storage') . ' VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ' . $db->quote('file');
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
            }

            //Add primary key to Fb table
            $query = 'ALTER TABLE ' . $db->quoteName('#__community_connect_users') . ' ADD PRIMARY KEY( ' . $db->quote('connectid') . ')';
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
            }

            $query = 'ALTER TABLE ' . $db->quoteName('#__community_events') . ' ADD ' . $db->quoteName('unlisted') . ' TINYINT NOT NULL';
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
            }

            //Update default template to jomsocial
            $query = 'SELECT ' . $db->quoteName('params') . ' FROM ' . $db->quoteName('#__community_config') . ' WHERE ' . $db->quoteName('name') . ' = ' . $db->quote('config');
            $db->setQuery($query);
            $params = $db->loadResult();

            $params = new JRegistry($params);
            $params->set('template', 'jomsocial');
            $params = $params->toString();

            $query = 'UPDATE ' . $db->quoteName('#__community_config') . ' SET ' . $db->quoteName('params') . ' = ' . $db->quote($params)
                . ' WHERE ' . $db->quoteName('name') . ' = ' . $db->quote('config');
            $db->setQuery($query);
            $db->execute();

            //hashtag table
            $query = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__community_hashtag') . ' (
                  ' . $db->quoteName('id') . ' int(11) NOT NULL AUTO_INCREMENT,
                  ' . $db->quoteName('tag') . ' varchar(128) CHARACTER SET utf8 NOT NULL,
                  ' . $db->quoteName('params') . ' text CHARACTER SET utf8 NOT NULL,
                  ' . $db->quoteName('created_at') . ' timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (' . $db->quoteName('id') . '),
                  UNIQUE KEY ' . $db->quoteName('tag') . ' (' . $db->quoteName('tag') . ')
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
            $db->setQuery($query);
            $db->execute();

            //migrate previous hashtag in activity to hashtag table
            $query = 'SELECT ' . $db->quoteName('id') . ',' . $db->quoteName('title') . ',' . $db->quoteName('content') . ' FROM ' . $db->quoteName('#__community_activities') . ' order by id desc LIMIT 1000';

            $db->setQuery($query);
            $results = $db->loadObjectList();

            $matches = null;
            $hashtaglist = array();
            foreach ($results as $result) {
                preg_match_all('(#\w+)', $result->content . ' ' . $result->title, $matches);
                if (!empty($matches[0])) {
                    foreach ($matches[0] as $match) {
                        if ($match != '#eee') { // videos content image src might have #eee as inline css, escape this
                            $hashtaglist[$match][] = $result->id;
                        }
                    }
                }
            }

            if (count($hashtaglist) > 0) {
                foreach ($hashtaglist as $hash => $activityIds) {
                    $param = new JRegistry();
                    $param->set('activity_id', $activityIds);

                    $query = 'REPLACE INTO ' . $db->quoteName('#__community_hashtag')
                        . '(' . $db->quoteName('tag') . ', ' . $db->quoteName('params') . ')'
                        . 'VALUES(' . $db->quote($hash) . ', ' . $db->quote($param->toString()) . ')';

                    $db->setQuery($query);
                    $db->execute();
                }
            }

            // update activity table
            $query = 'ALTER TABLE  ' . $db->quoteName('#__community_activities') . ' ADD  ' . $db->quoteName('updated_at') . ' TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP';
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
            }

            //update group table
            $query = 'ALTER TABLE ' . $db->quoteName('#__community_groups') . ' ADD ' . $db->quoteName('summary') . ' TEXT NOT NULL';
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
            }

            $query = 'ALTER TABLE ' . $db->quoteName('#__community_groups') . ' ADD ' . $db->quoteName('unlisted') . ' TINYINT NOT NULL';
            $db->setQuery($query);
            try {
                $db->execute();
            } catch (Exception $e) {
            }

            $query = 'DELETE FROM '.$db->quoteName('#__community_userpoints').' WHERE '.$db->quoteName('action_string').' = '.$db->quote('profile.avatar.uploads');
            $db->setQuery($query);
            $db->execute();
        }

        public static function update_17(){
            $db = JFactory::getDBO();

            if (self::_isExistTableColumn('#__community_videos', 'eventid')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_videos') . ' ADD ' . $db->quoteName('eventid') . ' INT( 11 ) NOT NULL AFTER '
                    . $db->quoteName('groupid');

                $db->setQuery($query);
                $db->execute();
            }

            if (self::_isExistTableColumn('#__community_featured', 'target_id')) {

                $query = 'ALTER TABLE '. $db->quoteName('#__community_featured') .' ADD '.$db->quoteName('target_id').' INT(11) NOT NULL AFTER '.$db->quoteName('created_by');
                $db->setQuery($query);
                $db->execute();
            }

            if (self::_isExistTableColumn('#__community_files', 'messageid')) {

                $query = 'ALTER TABLE '. $db->quoteName('#__community_files') .' ADD '.$db->quoteName('messageid').' INT(11) NOT NULL AFTER '.$db->quoteName('profileid');
                $db->setQuery($query);
                $db->execute();
            }

            $query = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__community_profile_stats') . ' (
                      ' . $db->quoteName('id') . ' int(11) NOT NULL AUTO_INCREMENT,
                      ' . $db->quoteName('uid') . ' int(11) NOT NULL,
                      ' . $db->quoteName('type') . ' varchar(255) NOT NULL,
                      ' . $db->quoteName('count') . ' int(11) NOT NULL,
                      ' . $db->quoteName('date') . ' date NOT NULL,
                      ' . $db->quoteName('params') . ' text NOT NULL,
                      ' . $db->quoteName('created_at') . ' timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (' . $db->quoteName('id') . ')
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
            $db->setQuery($query);
            $db->execute();
        }

        public static function update_18(){
            $db = JFactory::getDbo();

            $query = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__community_photo_stats') . ' (
                      ' . $db->quoteName('id') . ' int(11) NOT NULL AUTO_INCREMENT,
                      ' . $db->quoteName('pid') . ' int(11) NOT NULL,
                      ' . $db->quoteName('type') . ' varchar(255) NOT NULL,
                      ' . $db->quoteName('count') . ' int(11) NOT NULL,
                      ' . $db->quoteName('date') . ' date NOT NULL,
                      ' . $db->quoteName('params') . ' text NOT NULL,
                      ' . $db->quoteName('created_at') . ' timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (' . $db->quoteName('id') . ')
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
            $db->setQuery($query);
            $db->execute();

            $query = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__community_video_stats') . ' (
                      ' . $db->quoteName('id') . ' int(11) NOT NULL AUTO_INCREMENT,
                      ' . $db->quoteName('vid') . ' int(11) NOT NULL,
                      ' . $db->quoteName('type') . ' varchar(255) NOT NULL,
                      ' . $db->quoteName('count') . ' int(11) NOT NULL,
                      ' . $db->quoteName('date') . ' date NOT NULL,
                      ' . $db->quoteName('params') . ' text NOT NULL,
                      ' . $db->quoteName('created_at') . ' timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (' . $db->quoteName('id') . ')
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
            $db->setQuery($query);
            $db->execute();

            $query = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__community_group_stats') . ' (
                      ' . $db->quoteName('id') . ' int(11) NOT NULL AUTO_INCREMENT,
                      ' . $db->quoteName('gid') . ' int(11) NOT NULL,
                      ' . $db->quoteName('type') . ' varchar(255) NOT NULL,
                      ' . $db->quoteName('count') . ' int(11) NOT NULL,
                      ' . $db->quoteName('date') . ' date NOT NULL,
                      ' . $db->quoteName('params') . ' text NOT NULL,
                      ' . $db->quoteName('created_at') . ' timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (' . $db->quoteName('id') . ')
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
            $db->setQuery($query);
            $db->execute();

            $query = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__community_event_stats') . ' (
                      ' . $db->quoteName('id') . ' int(11) NOT NULL AUTO_INCREMENT,
                      ' . $db->quoteName('eid') . ' int(11) NOT NULL,
                      ' . $db->quoteName('type') . ' varchar(255) NOT NULL,
                      ' . $db->quoteName('count') . ' int(11) NOT NULL,
                      ' . $db->quoteName('date') . ' date NOT NULL,
                      ' . $db->quoteName('params') . ' text NOT NULL,
                      ' . $db->quoteName('created_at') . ' timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (' . $db->quoteName('id') . ')
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
            $db->setQuery($query);
            $db->execute();

        }

        public static function update_19(){
            $db = JFactory::getDbo();

            $query = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__community_digest_email') . ' (
                      ' . $db->quoteName('user_id') . ' int(11) NOT NULL,
                      ' . $db->quoteName('last_sent') . ' TIMESTAMP NOT NULL,
                      ' . $db->quoteName('total_sent') . ' int(11) NOT NULL,
                      PRIMARY KEY (' . $db->quoteName('user_id') . ')
                    ) ENGINE=MyISAM';
            $db->setQuery($query);
            $db->execute();

            if (self::_isExistTableColumn('#__community_fields_values', 'params')) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__community_fields_values')
                    . ' ADD ' . $db->quoteName('params') . ' text NOT NULL';
                $db->setQuery($query);
                $db->execute();
            }
        }
    }