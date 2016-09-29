<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

	// Check if JomSocial core file exists
	$corefile 	= JPATH_ROOT . '/components/com_community/libraries/core.php';

	jimport( 'joomla.filesystem.file' );
	if( !JFile::exists( $corefile ) )
	{
		return;
	}

	// Include JomSocial's Core file, helpers, settings...
	require_once( $corefile );
	require_once dirname(__FILE__) . '/helper.php';

	// Add proper stylesheet
    JFactory::getLanguage()->isRTL() ? CTemplate::addStylesheet('style.rtl') : CTemplate::addStylesheet('style');

    $displayType = $params->get('display_type',1); // 0 = albums, 1 = photos (default)
    $limit = $params->get('limit',20);
    $photoType = $params->get('category_type',0);
    $sortBy = $params->get('filter_by', 0);

    if($sortBy){
        // 1 = popularity
        $sortBy = 'hit';
    }

    switch($photoType){
        case 0:
            $photoType = false; // this will grab all the photos
            break;
        case 1:
            $photoType = 'user';
            break;
        case 2:
            $photoType = 'group';
            break;
        case 3:
            $photoType = 'event';
            break;
        default:
            $photoType = 'user';
    }

    if($displayType){
        $model = CFactory::getModel('photos');
        //photos
        $photos = $model->getAllPhotos(null, $photoType, $limit, array(0,10), COMMUNITY_ORDER_BY_DESC,
            $sortBy, true);

        if ($photos) {
            // Make sure it is all photo object
            foreach ($photos as $row) {
                $photo = JTable::getInstance('Photo', 'CTable');
                $photo->bind($row);
                $row = $photo;
            }
        }

        require(JModuleHelper::getLayoutPath('mod_community_photos', 'photos'));

    } else {
        $model = CFactory::getModel('photos');

        $filter = 'special';

        if($photoType == 'group' || $photoType == 'event'){
            $filter = $photoType;
        }

        $albums = $model->getAllAlbums(0, $limit, $sortBy, $filter);

        if ($albums) {
           // shuffle($latestAlbums);
            // Make sure it is all albums object
            foreach ($albums as $row) {
                $album = JTable::getInstance('Album', 'CTable');
                $album->bind($row);
                $row = $album;
            }
        }

        if (!empty($albums)) {
            for ($i = 0; $i < count($albums); $i++) {
                $row = $albums[$i];
                $row->user = CFactory::getUser($row->creator);
            }
        }

        require(JModuleHelper::getLayoutPath('mod_community_photos', 'albums'));
    }







