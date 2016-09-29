<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

class CTableProfile extends JTable {

    var $userid         = null;
    var $status         = null;
    var $status_access  = null;
    var $points          = null;
    var $posted_on      = null;
    var $avatar         = null;
    var $thumb          = null;
    var $invite         = null;
    var $params         = null;
    var $view           = null;
    var $friends        = null;
    var $groups         = null;
    var $friendcount    = null;
    var $alias          = null;
    var $latitude       = null;
    var $longtitude     = null;
    var $profile_id     = null;
    var $storage        = null;
    var $watermark_hash = null;
    var $search_email   = null;
    var $cover          = null;

    public function __construct(&$db) {
        parent::__construct('#__community_users', 'userid', $db);
    }

    public function getAvatar() {
        // Get the avatar path. Some maintance/cleaning work: We no longer store
        // the default avatar in db. If the default avatar is found, we reset it
        // to empty. In next release, we'll rewrite this portion accordingly.
        // We allow the default avatar to be template specific.

        $profileModel = CFactory::getModel('Profile');
        $gender = $profileModel->getGender($this->userid);

        if (empty($gender))
            $gender = 'Male';

        if ($this->avatar == 'components/com_community/assets/user-' . JText::_($gender) . '.png') {
            $this->avatar = '';
            $this->store();
        }


        $avatar = CUrlHelper::avatarURI($this->avatar, 'user-' . JText::_($gender) . '.png');

        return $avatar;
    }

    public function store($updateNulls = false){

        //before storing, we always get the latest user points
        if($this->userid) {
            $db = $this->getDbo();
            $db->setQuery(
                "SELECT " . $db->quoteName('points') . " FROM #__community_users "
                . " WHERE "
                . $db->quoteName('userid') ."=" .$db->quote($this->userid)
            );
            $this->points = $db->loadResult();
        }
        parent::store($updateNulls);
    }

    /**
     * Get large avatar use for cropping
     * @return string
     */
    public function getLargeAvatar() {
        $config = CFactory::getConfig();

        /* Some profile type avatar are stored directly to the avatar with this format avatar_[id]
         * So, if we have this kind of format, we will take this as priority
         * Used by JSPT
         */
        if(count(explode('_',$this->avatar)) > 1){
            $largeAvatar = $this->avatar;
        }else{
            $largeAvatar = $config->getString('imagefolder') . '/avatar/profile-' . basename($this->avatar);
        }

        $current = CStorage::getStorage($this->storage);
        if ($current->exists($largeAvatar)) {
            return $current->getURI($largeAvatar);
        } else {
            return $this->getAvatar();
        }
    }

    public function removeAvatar() {
        if (JFile::exists($this->avatar) && !JString::stristr($this->avatar, 'avatar_')) {
            JFile::delete($this->avatar);
        }

        if (JFile::exists($this->thumb) && !JString::stristr($this->thumb, 'avatar_')) {
            JFile::delete($this->thumb);
        }

        $this->avatar = '';
        $this->thumb = '';
        $this->store();
    }

    /**
     * Set user profile avatar
     */
    public function setImage($path, $type = 'thumb') {
        CError::assert($path, '', '!empty', __FILE__, __LINE__);

        $db = $this->getDBO();

        // Fix the back quotes
        $path = JString::str_ireplace('\\', '/', $path);
        $type = JString::strtolower($type);

        // Test if the record exists.
        $oldFile = $this->$type;

        if ($oldFile) {
            // File exists, try to remove old files first.
            $oldFile = JString::str_ireplace('/', '/', $oldFile);

            // If old file is default_thumb or default, we should not remove it.
            if (!JString::stristr($oldFile, 'user.png') && !JString::stristr($oldFile, 'user_thumb.png') && !JString::stristr($oldFile, 'avatar_')) {
                jimport('joomla.filesystem.file');
                JFile::delete($oldFile);
            }
        }
        $this->$type = $path;
        $this->store();

        // Trigger profile avatar update event.
        if ($type == 'avatar') {
            $appsLib = CAppPlugins::getInstance();
            $appsLib->loadApplications();
            $args = array();
            $args[] = $this->userid;   // userid
            $args[] = $oldFile; // old path
            $args[] = $path;  // new path

            $appsLib->triggerEvent('onProfileAvatarUpdate', $args);
        }
    }

    public function setCover($path) {
        $this->cover = $path;
        $this->storage = 'file';
        try {
            $this->store();
            return true;
        } catch (Exception $e){
            return false;
        }
        return false;
    }

}