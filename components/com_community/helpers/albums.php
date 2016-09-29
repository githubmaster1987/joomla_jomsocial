<?php
/**
* @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

abstract class CAlbumsHelperHandler
{
	protected $type = '';
	protected $model = '';
	protected $album = '';
	protected $my = '';

    const PRIVACY_PUBLIC_LEGACY = '0';
    const PRIVACY_PUBLIC = '10';
	const PRIVACY_MEMBERS = '20';
	const PRIVACY_FRIENDS = '30';
	const PRIVACY_PRIVATE = '40';

	public function __construct(CTableAlbum $album)
	{
		$this->my = CFactory::getUser();
		$this->model = CFactory::getModel('photos');
		$this->album = $album;
	}

	abstract public function isPublic();

	abstract public function showActivity();
}

class CAlbumsGroupHelperHandler extends CAlbumsHelperHandler
{
	public function __construct(CTableAlbum $album)
	{
		parent::__construct($album);
	}

	/**
	 * Determines whether the current album is public or not
	 *
	 * @params
	 * @return Boolean    True upon success
	 **/
	public function isPublic()
	{
		if (COwnerHelper::isCommunityAdmin()) {
			return true;
		}

		$my = CFactory::getUser();
		$group = JTable::getInstance('Group', 'CTable');
		$group->load($this->album->groupid);

		if ($group->approvals == COMMUNITY_PRIVATE_GROUP && !$group->isMember($my->id)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines whether the activity stream content for the photo related items should be shown or
	 * hidden depending on the user's privacy settings
	 * @params
	 * @return Boolean    True upon success
	 **/
	public function showActivity()
	{
		$group = JTable::getInstance('Group', 'CTable');
		$group->load($this->album->groupid);

		return $group->approvals == COMMUNITY_PUBLIC_GROUP;
	}
}

class CAlbumsUserHelperHandler extends CAlbumsHelperHandler
{
	public function __construct(CTableAlbum $album)
	{
		parent::__construct($album);
	}

	/**
	 * Determines whether the current album is public or not
	 *
	 * @params
	 * @return Boolean    True upon success
	 **/
	public function isPublic()
	{
		$my = CFactory::getUser();

		if (COwnerHelper::isCommunityAdmin()) {
           return true;
        }

		switch ($this->album->permissions) {
			case self::PRIVACY_PRIVATE:
				return $my->id == $this->album->creator;
				break;
			case self::PRIVACY_FRIENDS:
				return CFriendsHelper::isConnected($my->id, $this->album->creator);
				break;
			case self::PRIVACY_MEMBERS:
				if ($my->id != 0) {
					return true;
				}
				break;
            case self::PRIVACY_PUBLIC:
            case self::PRIVACY_PUBLIC_LEGACY:
				return true;
				break;
		}
		return false;
	}

	/**
	 * Determines whether the activity stream content for the photo related items should be shown or
	 * hidden depending on the user's privacy settings
	 * @params
	 * @return Boolean    True upon success
	 **/
	public function showActivity()
	{
		$permission = $this->album->permissions;
		$my = CFactory::getUser();

		switch ($permission) {
			case PRIVACY_MEMBERS:
				$show = $my->id != 0;
				break;
			case PRIVACY_FRIENDS:

				$show = CFriendsHelper::isConnected($my->id, $this->album->creator);
				break;
			case PRIVACY_PRIVATE:
				$show = $my->id == $this->album->creator;
				break;
            case PRIVACY_PUBLIC:
            case PRIVACY_PUBLIC_LEGACY:
			default:
				$show = true;
				break;
		}
		return $show;
	}
}

class CAlbumsHelper
{
	var $handler = '';
	var $id = '';

	/**
	 *
	 * @param mixed $id either album id OR CTableAlbum object
	 *
	 */
	public function __construct($id)
	{
		$this->id = $id;
		$this->handler = $this->_getHandler();
	}

	public function isPublic()
	{
		return $this->handler->isPublic();
	}

	public function showActivity()
	{
		return $this->handler->showActivity();
	}

	private function _getHandler()
	{
		// The $this->id could be a CTableAlbum object, in which case,
		// No need to load, just link it back
		if (is_object($this->id)) {
			$album = $this->id;
		} else {
			$album = JTable::getInstance('Album', 'CTable');
			$album->load($this->id);
		}

		if ($album->type == PHOTOS_USER_TYPE) {
			$handler = new CAlbumsUserHelperHandler($album);
		} else {
			$handler = new CAlbumsGroupHelperHandler($album);
		}

		return $handler;
	}


    /**
     * Fixed album is consider as album that cannot be uploaded directly and deleted.
     * @param $album
     * @return bool
     */
    public static function isFixedAlbum($album){
        $type = array('group.avatar', 'profile.avatar', 'event.avatar', 'group.cover', 'profile.cover', 'event.cover', 'profile.gif');
        $isDefaultAlbum = $album->default;
        if(in_array(strtolower($album->type),$type) || $isDefaultAlbum){
            return true;
        }
        return false;
    }

}