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
 * Class to manipulate data from Metacafe
 *
 * @access	public
 */
class CTableVideoMetacafe extends CVideoProvider
{
	var $xmlContent = null;
	var $url 		= '';
	var $videoId	= '';

	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
		return 'http://www.metacafe.com/watch/' . $this->getId() .'/';
	}

	/*
	 * Return true if successfully connect to remote video provider
	 * and the video is valid
	 */
	public function isValid()
	{
		// Connect and get the remote video
        if ( !parent::isValid())
		{
			return false;
		}

        $pattern = '/property="video:duration"/';
        if ( !preg_match($pattern, $this->xmlContent) ) {
            return false;
        }

		return true;
	}

	/**
	 * Extract MetaCafe video id from the video url submitted by the user
	 *
	 * @access	public
	 * @param	video url
	 * @return videoid
	 */
	public function getId()
	{
        $pattern = '/metacafe\.com\/watch\/([^\/]+)/i';
        preg_match( $pattern, $this->url, $matches );
        return !empty( $matches[1] ) ? $matches[1] : null;
	}

	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'metacafe';
	}

	public function getTitle()
	{
        $pattern = '/og:title"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	/**
	 * Get video's description from videoid
	 *
	 * @access 	public
	 * @param 	videoid
	 * @return description
	 */
	public function getDescription()
	{
        $pattern = '/og:description"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	/**
	 * Get video duration
	 *
	 * @return $duration seconds
	 */
	public function getDuration()
	{
        $pattern = '/video:duration"\s+content="(\d+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	/**
	 * Get video's thumbnail URL from videoid
	 *
	 * @access 	public
	 * @param 	videoid
	 * @return url
	 */
	public function getThumbnail()
	{
		$thumbnail	= '';
        $pattern = '/og:image"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        if ( $matches ) {
            $thumbnail = $matches[1];
        }

		return CVideosHelper::getIURL($thumbnail);
	}

	/**
	 *
	 *
	 * @return $embedvideo specific embeded code to play the video
	 */
	public function getViewHTML( $videoId, $videoWidth, $videoHeight )
	{
		if (!$videoId) {
			$videoId = $this->videoId;
		}
        return '<iframe src="http://www.metacafe.com/embed/' . $videoId . '/" width="' . $videoWidth . '" height="' . $videoHeight . '" allowFullScreen frameborder=0></iframe>';
	}
}
