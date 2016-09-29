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

class CTableMultiProfile extends JTable
{
	var $id			= null;
	var $name		= null;
	var $description= null;
	var $approvals	= null;
	var $published	= null;
	var $avatar		= null;
	var $thumb		= null;
	var $created	= null;
  	var $watermark	= null;
  	var $watermark_hash		= null;
  	var $watermark_location	= null;
	var $create_groups	= null;
	var $create_events	= null;
	var $profile_lock = null;
  	var $ordering		= null;

	public function __construct( &$db )
	{
		parent::__construct( '#__community_profiles', 'id', $db );
	}

	public function getWatermark()
	{
		return JURI::root() . $this->watermark;
	}

	/**
	 * Retrieve the profile type avatar
	 **/
	public function getThumbAvatar()
	{
		if( empty($this->thumb) )
		{
			return JURI::root() . DEFAULT_USER_THUMB;
		}
		return JURI::root() . $this->thumb;
	}

	/**
	 * Retrieve the profile type avatar
	 **/
	public function getAvatar()
	{
		return JURI::root() . $this->avatar;
	}

	public function getName()
	{
	    if( empty( $this->name ) )
	    {
	        return JText::_('COM_COMMUNITY_DEFAULT_PROFILE_TYPE');
		}

		return $this->name;
	}
	/**
	 * Retrieve a multiprofile mapping for a given multi profile.
	 *
	 * @return	Object	An object of #__community_multiprofiles_fields
	 **/
	public function getChild( $fieldId , $multiprofileId )
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT * FROM ' . $db->quoteName('#__community_profiles_fields')
				. ' WHERE ' . $db->quoteName( 'field_id' ) . '=' . $db->Quote( $fieldId )
				. ' AND ' . $db->quoteName( 'parent' ) . '=' . $db->Quote( $multiprofileId );
		$db->setQuery( $query );

		$result	= $db->loadObject();

		return $result;
	}

	/**
	 * Override paren't store method so we can do some checking with the watermarks.
	 *
	 * @return	bool	True on success.
	 **/
	public function store( $updateNulls = false )
	{
		if( !$this->id )
		{
			$this->ordering		= parent::getNextOrder();
		}
		parent::store();
	}

	/**
	 * Override parents delete method
	 **/
	public function delete( $pk = null)
	{
		parent::delete();

		// @rule: Deleting a multiple profile should revert all users using it to the default profile
		$db		= JFactory::getDBO();
		$query	= 'UPDATE ' . $db->quoteName('#__community_users') . ' SET ' . $db->quoteName('profile_id') . '=' . $db->Quote( COMMUNITY_DEFAULT_PROFILE ) . ' '
				. 'WHERE ' . $db->quoteName('profile_id') . '=' . $db->Quote( $this->id );
		$db->setQuery( $query );
		$db->execute();

		// @rule: Delete all childs related to this multiprofile
		$this->deleteChilds();
	}

	/**
	 * Delete all existing childs in commmunity_multiprofiles_fields
	 *
	 * @return	bool	True on success.
	 **/
	public function deleteChilds()
	{
		$db		= JFactory::getDBO();
		$query	= 'DELETE FROM ' . $db->quoteName('#__community_profiles_fields') . ' WHERE ' . $db->quoteName( 'parent' ) . '=' . $db->Quote( $this->id );
		$db->setQuery( $query );
		$db->execute();

		return true;
	}

	public function isChild( $fieldId )
	{
		if( $this->id == 0 )
			return false;

		if( $this->getChild( $fieldId , $this->id ) )
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if the current profile type has users already assigned.
	 *
	 * @params
	 * @return	Boolean	True if there are still users assigned to this profile type.
	 **/
	public function hasUsers()
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT COUNT(1) FROM ' . $db->quoteName( '#__community_users' ) . ' '
				. 'WHERE ' . $db->quoteName( 'profile_id' ) . '=' . $db->Quote( $this->id );
		$db->setQuery( $query );

		$result	= $db->loadResult() > 0 ? true : false;

		return $result;
	}

	/**
	 * Check current avatar's hash
	 **/
	public function isHashMatched( $profileType , $hash )
	{
		// @rule: backward compatibility. In 2.0, the hash column is a new column.
		if( empty($hash ) )
		{
			return true;
		}

		static $types	= array();
		$match			= true;

		if( empty($types) )
		{
			$model	= CFactory::getModel( 'Profile' );
			$rows	= $model->getProfileTypes();

			if( $rows )
			{
				foreach( $rows as $row )
				{
					$types[ $row->id ]	= $row;
				}
			}
		}

		if( isset( $types[ $profileType ] ) )
		{
			$match	= $types[ $profileType ]->watermark_hash == $hash;
		}

		return $match;
	}

	/**
	 * Updates existing default image that is already stored in the community_users table.
	 *
	 * @param	String	$type	Type of image, thumb or avatar.
	 * @param	String	$oldPath	The path for the old image.
	 *
	 * @return	Boolean		True on success false otherwise.
	 **/
	public function updateUserDefaultImage( $type , $oldPath )
	{
		$db		= JFactory::getDBO();
		$query	= 'UPDATE ' . $db->quoteName( '#__community_users' ) . ' '
				. 'SET ' . $db->quoteName( $type ) . '=' . $db->Quote( $this->$type ) . ' '
				. 'WHERE ' . $db->quoteName( $type ) . '=' . $db->Quote( $oldPath );
		$db->setQuery( $query );
		try {
			$db->execute();
		} catch (Exception $e) {
			return false;
		}

		// Remove the old files.
		$oldImagePath = JPATH_ROOT .'/'. CString::str_ireplace( '/' , '/' , $oldPath );

		if( JFile::exists( $oldImagePath ) )
		{
			JFile::delete( $oldImagePath );
		}

	    return true;
	}

	public function updateUserThumb( $user , $hashName )
	{
		$this->_updateUserWatermark( $user , 'thumb' , $hashName );
	}

	public function updateUserAvatar( $user , $hashName )
	{
		$this->_updateUserWatermark( $user , 'avatar' , $hashName );
	}

	private function _updateUserWatermark( $user , $type , $hashName )
	{
		$config		= CFactory::getConfig();
		$oldAvatar  = $user->_avatar;
		// @rule: This is the original avatar path
		//CFactory::load( 'helpers' , 'image' );
		$userImageType	= '_' . $type;
		$data			= @getimagesize( JPATH_ROOT .'/'. CString::str_ireplace( '/' , '/' , $user->$userImageType ) );
		$original		= JPATH_ROOT .'/images/watermarks/original' .'/'. md5( $user->id . '_' . $type ) . CImageHelper::getExtension( $data[ 'mime' ] );

		if( !$config->get('profile_multiprofile') || !JFile::exists( $original ) )
		{
			return false;
		}

		static $types	= array();

		if( empty($types) )
		{
			$model	= CFactory::getModel( 'Profile' );
			$rows	= $model->getProfileTypes();

			if( $rows )
			{
				foreach( $rows as $row )
				{
					$types[ $row->id ]	= $row;
				}
			}
		}
		$model	= CFactory::getModel( 'User' );


		if( isset( $types[ $user->_profile_id ] ) )
		{
			// Bind the data to the current object so we can access it here.
			$this->bind( $types[ $user->_profile_id ] );

			// Path to the watermark image.
			$watermarkPath	= JPATH_ROOT .'/'. CString::str_ireplace('/' , '/' , $this->watermark);

			// Retrieve original image info
			$originalData	= getimagesize( $original );

			// Generate image file name.
			$fileName	= ( $type == 'thumb' ) ? 'thumb_' : '';
			$fileName	.= $hashName;
			$fileName	.= CImageHelper::getExtension( $originalData[ 'mime' ] );

			// Absolute path to the image (local)
			$newImagePath	= JPATH_ROOT .'/'. $config->getString('imagefolder') .'/avatar' .'/'. $fileName;

			// Relative path to the image (uri)
			$newImageUri	= $config->getString('imagefolder') . '/avatar/' . $fileName;

			// Retrieve the height and width for watermark and original image.
			list( $watermarkWidth , $watermarkHeight )	= getimagesize( $watermarkPath );
			list( $originalWidth , $originalHeight )	= getimagesize( $original );

			// Retrieve the proper coordinates to the watermark location
			$position	= CImageHelper::getPositions( $this->watermark_location , $originalWidth , $originalHeight , $watermarkWidth , $watermarkHeight );

			// Create the new image with the watermarks.
			CImageHelper::addWatermark( $original , $newImagePath , $originalData[ 'mime' ] , $watermarkPath , $position->x , $position->y , false );
			$model->setImage( $user->id , $newImageUri , $type );

			// Remove the user's old image
			$oldFile	= JPATH_ROOT .'/'. CString::str_ireplace( '/' , '/' , $user->$userImageType );

			if( JFile::exists( $oldFile ) )
			{
				JFile::delete($oldFile);
			}
			if($type == 'avatar'){
				$oldImg = explode('avatar/', $oldAvatar);
				$oldImg = explode('.',$oldImg[1]);
				$oldImg = $config->getString('imagefolder') . '/avatar/'.$oldImg[0].'_stream_.'.$oldImg[1];

				JFile::copy($newImageUri, $oldImg);
			}
			// We need to update the property in CUser as well otherwise when we save the hash, it'll
			// use the old user avatar.
			$user->set( $userImageType , $newImageUri );

			// We need to restore the storage method.
			$user->set( '_storage' , 'file' );

			// Update the watermark hash with the latest hash
			$user->set( '_watermark_hash' , $this->watermark_hash );
			$user->save();

		}
		return true;
	}
}