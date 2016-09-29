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

require_once (COMMUNITY_COM_PATH.'/models/videos.php');

/**
 * Class to manipulate data from vimeo video
 *
 * @access	public
 */
class CTableVideoVimeo extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';
	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'http://vimeo.com/api/v2/video/' .$this->videoId.'.xml';
	}

	/*
	 * Return true if successfully connect to remote video provider
	 * and the video is valid
	 */
	public function isValid()
	{
		if ( !parent::isValid())
		{
			return false;
		}
		//get vimeo error
		if(strpos($this->xmlContent,'not found.')){
			$vimeoError	= JText::_('COM_COMMUNITY_VIDEOS_FETCHING_VIDEO_ERROR');
			throw new Exception($vimeoError);
		}

		$parser = new SimpleXMLElement($this->xmlContent); //JFactory::getXMLParser('Simple');
		$videoElement = $parser->video;

		if( empty($videoElement) )
		{
			$this->setError( JText::_('COM_COMMUNITY_VIDEOS_FETCHING_VIDEO_ERROR') );
			return false;
		}

		//get Video title
		$this->title = (string)$videoElement->title;

		//Get Video duration
		$this->duration = (int)$videoElement->duration;

		//Get Video thumbnail
		$this->thumbnail = (string)$videoElement->thumbnail_large;

		//Get Video description
		$this->description = (string)$videoElement->description;

		return true;
	}


	/**
	 * Extract Vimeo video id from the video url submitted by the user
	 *
	 * @access	public
	 * @param	video url
	 * @returns videoid
	 */
	public function getId()
	{
	    $pattern = '/vimeo.com\/([^\/]+\/)*([0-9]+)[#\/\?]?/';
	    preg_match( $pattern, $this->url, $matches );

        return !empty( $matches[2] ) ? $matches[2] : null;
	}

	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'vimeo';
	}

	public function getTitle()
	{
		$title	= '';
		$title	= $this->title;

		return $title;
	}

	/**
	 *
	 * @param $videoId
	 * @return unknown_type
	 */
	public function getDescription()
	{
		$description	= '';
		$description = $this->description;
		return $description;
	}

	public function getDuration()
	{
		$duration	= '';
		$duration	= $this->duration;

		return $duration;
	}

	/**
	 *
	 * @param $videoId
	 * @return unknown_type
	 */
	public function getThumbnail()
	{
		$thumbnail	= '';
		$thumbnail	= $this->thumbnail;

		return CVideosHelper::getIURL($thumbnail);
	}

	/**
	 *
	 *
	 * @return $embedCode specific embeded code to play the video
	 */
	public function getViewHTML($videoId, $videoWidth, $videoHeight)
	{
		if (!$videoId)
		{
			$videoId	= $this->videoId;
		}

		$embedCode = '<iframe src="'.CVideosHelper::getIURL('http://player.vimeo.com/video/' . $videoId ).'" width="' . $videoWidth . '" height="' . $videoHeight . '" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';

        return $embedCode;
	}
}
