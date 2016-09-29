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

class CPhotosHelper {

    /**
     * Get photo ID of stream ID
     * @param int $streamID Stream id of photo (cover, avatar,...)
     * @return mixed Null when failed.
     */
    public static function getPhotoOfStream($streamID) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        /* Get stream params */
        $query->select($db->quoteName('params'))
                ->from($db->quoteName('#__community_activities'))
                ->where($db->quoteName('id') . '=' . $db->quote($streamID));
        $db->setQuery($query);
        $params = $db->loadResult();
        /* Params is valid */
        if ($params !== null) {
            /* Decode JSON */
            $params = json_decode($params);
            /* Get photo ID */
            $query->clear()->select($db->quoteName('id'))
                    ->from($db->quoteName('#__community_photos'))
                    ->where($db->quoteName('image') . '=' . $db->quote($params->attachment));
            $db->setQuery($query);
            return $db->loadResult();
        }
        return null;
    }

    /**
     * Checks if the photo watermark is enabled and the watermark exists
     */
    public static function photoWatermarkEnabled(){
        $config = CFactory::getConfig();
        if($config->get('photo_watermark') && file_exists(JPATH_ROOT .'/'. COMMUNITY_WATERMARKS_PATH .'/'.WATERMARK_DEFAULT_NAME.'.png')){
            return true;
        }
        return false;
    }

    /**
     *
     * @param type $type
     * @param type $id
     * @param type $sourceX
     * @param type $sourceY
     * @param type $width
     * @param type $height
     */
    public static function updateAvatar($type, $id, $sourceX, $sourceY, $width, $height) {
        $filter = JFilterInput::getInstance();

        /* Filter input values */
        $type    = $filter->clean($type, 'string');
        $id      = $filter->clean($id, 'integer');
        $sourceX = $filter->clean($sourceX, 'float');
        $sourceY = $filter->clean($sourceY, 'float');
        $width   = $filter->clean($width, 'float');
        $height  = $filter->clean($height, 'float');

        $cTable = JTable::getInstance(ucfirst($type), 'CTable');
        $cTable->load($id);

        $cTable->storage = 'file';
        $cTable->store();

        $srcPath = JPATH_ROOT . '/' . $cTable->avatar;
        $destPath = JPATH_ROOT . '/' . $cTable->thumb;

        /* */
        $config = CFactory::getConfig();
        $avatarFolder = ($type != 'profile' && $type != '') ? $type . '/' : '';
        /* Get original image */
        $originalPath = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar' . '/' . $avatarFolder . '/' . $type . '-' . basename($cTable->avatar);

        /**
         * If original image does not exists than we use source image
         * @todo we should get from facebook original avatar file
         */
        if (!JFile::exists($originalPath)) {
            $originalPath = $srcPath;
        }

        $srcPath = str_replace('/', '/', $srcPath);
        $destPath = str_replace('/', '/', $destPath);

        $info = getimagesize($srcPath);
        $destType = $info['mime'];

        $destWidth = COMMUNITY_SMALL_AVATAR_WIDTH;
        $destHeight = COMMUNITY_SMALL_AVATAR_WIDTH;

        /* thumb size */
        $currentWidth = $width;
        $currentHeight = $height;

        /* avatar size */
        $imageMaxWidth = 160;
        $imageMaxHeight = 160;

        /**
         * @todo Should we generate new filename and update into database ?
         */
        /* do avatar resize */
        CImageHelper::resize($originalPath, $srcPath, $destType, $imageMaxWidth, $imageMaxHeight, $sourceX, $sourceY, $currentWidth, $currentHeight);
        /* do thumb resize */
        CImageHelper::resize($originalPath, $destPath, $destType, $destWidth, $destHeight, $sourceX, $sourceY, $currentWidth, $currentHeight);

        /**
         * Now we do check and process watermark
         */
        /* Check multiprofile to reapply watermark for thumbnail */
        $my = CFactory::getUser();
        $profileType = $my->getProfileType();
        $multiprofile = JTable::getInstance('MultiProfile', 'CTable');
        $multiprofile->load($profileType);
        $useWatermark = $profileType != COMMUNITY_DEFAULT_PROFILE && $config->get('profile_multiprofile') && !empty($multiprofile->watermark) ? true : false;

        if ($useWatermark && $type == 'profile') {
            $watermarkPath = JPATH_ROOT . '/' . CString::str_ireplace('/', '/', $multiprofile->watermark);
            list($watermarkWidth, $watermarkHeight) = getimagesize($watermarkPath);
            list($thumbWidth, $thumbHeight) = getimagesize($destPath);
            list($avatarWidth, $avatarHeight) = getimagesize($srcPath);

            // Avatar Properties
            $avatarPosition = CImageHelper::getPositions($multiprofile->watermark_location, $avatarWidth, $avatarHeight, $watermarkWidth, $watermarkHeight);
            // The original image file will be removed from the system once it generates a new watermark image.
            CImageHelper::addWatermark($srcPath, $srcPath, $destType, $watermarkPath, $avatarPosition->x, $avatarPosition->y);

            $thumbPosition = CImageHelper::getPositions($multiprofile->watermark_location, $thumbWidth, $thumbHeight, $watermarkWidth, $watermarkHeight);
            /* addWatermark into thumbnail */
            CImageHelper::addWatermark($destPath, $destPath, $destType, $watermarkPath, $thumbPosition->x, $thumbPosition->y);
        }

        // we need to update the activity stream of group if applicable, so the cropped image will be updated as well
        if($type == 'group'){
            $groupParams = new JRegistry($cTable->params);
            $actId = $groupParams->get('avatar_activity_id');

            if($actId){
                $act = JTable::getInstance('Activity','CTable');
                $act->load($actId);
                $actParams = new JRegistry($act->params);
                $actParams->set('avatar_cropped_thumb',$cTable->avatar);
                $act->params = $actParams->toString();
                $act->store();
            }
        }

        $connectModel = CFactory::getModel('connect');

        // For facebook user, we need to add the watermark back on
        if ($connectModel->isAssociated($my->id) && $config->get('fbwatermark') && $type == 'profile') {
            list( $watermarkWidth, $watermarkHeight ) = getimagesize(FACEBOOK_FAVICON);
            CImageHelper::addWatermark($destPath, $destPath, $destType, FACEBOOK_FAVICON, ( $destWidth - $watermarkWidth), ( $destHeight - $watermarkHeight));
        }
    }

}
