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

require_once ( JPATH_ROOT .'/components/com_community/models/models.php');

jimport( 'joomla.filesystem.file');

// Deprecated since 1.8.x to support older modules / plugins
//CFactory::load( 'tables' , 'featured' );

class CommunityModelFeatured extends JCCModel
{
	public function isExists( $type, $cid )
	{
		$db		= $this->getDBO();

		$query	= 'SELECT COUNT(1) FROM '. $db->quoteName('#__community_featured')
				. ' WHERE '. $db->quoteName('type').'=' . $db->Quote( $type ) . ' '
				. ' AND '. $db->quoteName('cid').'=' . $db->Quote( $cid );
		$db->setQuery($query);
		$exists	= ( $db->loadResult() >= 1) ? true : false;
		return $exists;
	}

    /**
     * Get a list of featured stream in this format $array[streamtype][targetid][0..n]
     * @return mixed
     */
    public function getStreamFeaturedList(){

        static $run = false;
        static $formattedResult = array();

        if($run){
            return $formattedResult;
        }

        $db = $this->getDbo();
        $query = "SELECT * FROM ".$db->quoteName("#__community_featured")
            ."WHERE ".$db->quoteName('type')." IN ('stream.group', 'stream.event', 'stream.frontpage', 'stream.profile')";
        $db->setQuery($query);
        $results = $db->loadObjectList();
        $run = true;

        foreach($results as $result){
            $formattedResult[$result->type][$result->target_id][] = $result;
        }

        return $formattedResult;
    }


    public function getAllStreamFeaturedId(){
        $db = $this->getDbo();
        $query = "SELECT cid FROM ".$db->quoteName("#__community_featured")
            ."WHERE ".$db->quoteName('type')." IN ('stream.group', 'stream.event', 'stream.frontpage', 'stream.profile')";
        $db->setQuery($query);
        $results = $db->loadColumn();

        return $results;
    }

    public function insertFeaturedStream($activityId, $streamType, $targetId){
        $my = CFactory::getUser(); // current user
        $featuredTable = JTable::getInstance('Featured','CTable');

        //set the featured stream type
        $featuredType = '';

        switch($streamType){
            case 'profile':
            case 'profiles':
                $featuredType = 'stream.profile';
                break;
            case 'frontpage':
                $featuredType = 'stream.frontpage';
                break;
            case 'event':
            case 'events':
                $featuredType = 'stream.event';
                break;
            case 'group':
            case 'groups':
                $featuredType = 'stream.group';
                break;
            default:
                return false;
        }

        $featuredTable->cid = $activityId;
        $featuredTable->type = $featuredType;
        $featuredTable->target_id = $targetId;
        $featuredTable->created_by = $my->id;
        $featuredTable->created = JDate::getInstance()->toSql();

        return $featuredTable->store();

    }

    /**
     * delete featured stream based on id and stream type(if applicable)
     * @param $activityId
     * @param string $streamType - optional
     * @param string $contextId
     */
    public function deleteFeaturedStream($activityId , $streamType = '', $contextId = ''){

        $streamTypeQuery = "('stream.group', 'stream.event', 'stream.frontpage', 'stream.profile') ";
        if($streamType != ''){
            $streamTypeQuery = "('".$streamType."')";
        }

        $db		= JFactory::getDBO();

        $extraQuery = '';
        if($contextId != ''){
            $extraQuery = " AND ".$db->quoteName('target_id')." = ".$db->quote($contextId);
        }


        $query	= "DELETE FROM " . $db->quoteName( '#__community_featured' )
            . " WHERE " . $db->quoteName( 'type' ) . " IN "
            . $streamTypeQuery
            . " AND " . $db->quoteName( 'cid' ) . '=' . $db->Quote( $activityId )
            . $extraQuery;
        $db->setQuery( $query );
        return $db->execute();

    }

    public function getStreamFeatured(){

    }
}