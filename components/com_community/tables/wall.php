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

/**
 * Wall object
 */
class CTableWall extends JTable
{
	/** Primary key **/
	var $id 			= null;

	/** The unique id of the specific app type **/
	var $contentid		= null;

	/** The user id that posted **/
	var $post_by		= null;

	/** The IP address of the poster **/
	var $ip				= null;

	/** Message **/
	var $comment		= null;

	/** Date the comment is posted **/
	var $date			= null;

	/** Publish status of the wall **/
	var $published		= null;

	/** Application type **/
	var $type			= null;

	/**
	 * Constructor
	 */
	public function __construct( &$db )
	{
		parent::__construct( '#__community_wall', 'id', $db );
	}


	/**
	 * Store the wall data
	 *
	 */
	public function store($updateNulls = false)
	{
		// Set the defaul data if they are empty

		if( empty($this->ip) )
		{
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}

		if( empty($this->date) )
		{
			$now = JDate::getInstance();
			$this->date = $now->toSql();
		}

		if( empty($this->published) ){
			$this->published = 1;
		}

		$newEntry = ($this->id) ? false : true; // this is used to check if this is a new wall or existing ones

		$status = parent::store();

		// this feature is to bump the activities to the top by updating the update_at column if there's any new comment in the stream
		if($status && $newEntry){
			$db = JFactory::getDbo();
			//before storing, we need to check if we should allow bumping on stream object or not

			//this array is the list of types that it's contentid refers to the activityid
			$linkedToActivityArr = array(
				'profile.status'
			);

			//this is the type of activity that we shouldn't bump
			$avoidBumpArr = array(
				//'profile.avatar.upload'
			);

			$excludeQuery = '';
			if(count($avoidBumpArr) > 0){
				$excludeQuery = " AND ".$db->quoteName('comment_type')." NOT IN(".implode(',',$db->quote($avoidBumpArr)).")";
			}

            if(strpos($this->params, 'activityId') !== false) { // this is a special case where we try to cater the aggregated stream
                $params = new CParameter($this->params);
                $activityId = $params->get('activityId');
                $query = "UPDATE " . $db->quoteName('#__community_activities') . " SET " . $db->quoteName('updated_at') . "=NOW() WHERE "
                    . $db->quoteName('id') . "=" . $db->quote($activityId);
            }elseif(in_array($this->type, $linkedToActivityArr)){
				$query = "UPDATE ".$db->quoteName('#__community_activities')." SET ".$db->quoteName('updated_at')."=NOW() WHERE "
					.$db->quoteName('id')."=".$db->quote($this->contentid)
						." AND ".$db->quoteName('comment_type')." LIKE ".$db->quote($this->type.'%')
						.$excludeQuery;
            }else{
				$query = "UPDATE ".$db->quoteName('#__community_activities')." SET ".$db->quoteName('updated_at')."=NOW() WHERE "
					.$db->quoteName('comment_id')."=".$db->quote($this->contentid)
						." AND ".$db->quoteName('comment_type')." LIKE ".$db->quote($this->type.'%')
						.$excludeQuery;
			}

			$db->setQuery($query);
			$result = $db->execute();
		}
	}
}
