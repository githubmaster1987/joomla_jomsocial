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
 * Class to manipulate data from YouTube
 *
 * @access	public
 */
class CTableVideoYoutube extends CVideoProvider
{
	var $xmlContent = null;
	var $url = '';
	var $videoId = null;

	/**
	 * Return feedUrl of the video
	 */
	public function getFeedUrl()
	{
        return 'https://www.youtube.com/watch?v=' . $this->getId();
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

		// Connect and get the remote video
		if ( $this->xmlContent == 'Invalid id')
		{
			throw new Exception(JText::_('COM_COMMUNITY_VIDEOS_INVALID_VIDEO_ID_ERROR'));
			return false;
		}

		if($this->xmlContent == 'Video not found')
		{
			throw new Exception(JText::_('COM_COMMUNITY_VIDEOS_YOUTUBE_ERROR'));
			return false;
		}





		return true;
	}

	/**
	 * Extract YouTube video id from the video url submitted by the user
	 *
	 * @access	public
	 * @param	video url
	 * @return videoid
	 */
	public function getId()
	{
		if($this->videoId){
			return $this->videoId;
		}

		preg_match_all('~
	        # Match non-linked youtube URL in the wild. (Rev:20111012)
	        https?://         # Required scheme. Either http or https.
	        (?:[0-9A-Z-]+\.)? # Optional subdomain.
	        (?:               # Group host alternatives.
	          youtu\.be/      # Either youtu.be,
	        | youtube\.com    # or youtube.com followed by
	          \S*             # Allow anything up to VIDEO_ID,
	          [^\w\-\s;]       # but char before ID is non-ID char.
	        )                 # End host alternatives.
	        ([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
	        (?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
	        (?!               # Assert URL is not pre-linked.
	          [?=&+%\w]*      # Allow URL (query) remainder.
	          (?:             # Group pre-linked alternatives.
	            [\'"][^<>]*>  # Either inside a start tag,
	          | </a>          # or inside <a> element text contents.
	          )               # End recognized pre-linked alts.
	        )                 # End negative lookahead assertion.
	        [?=&+%\w]*        # Consume any URL (query) remainder.
	        ~ix',
			$this->url, $matches);

    	if( isset($matches) && !empty($matches[1]) ){
    		return $matches[1][0];
		}

		return false;
	}

	/**
	 * Return the video provider's name
	 *
	 */
	public function getType()
	{
		return 'youtube';
	}

	public function getTitle()
	{
        $pattern = '/og:title"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getDescription()
	{
        $pattern = '/og:description"\s+content="([^"]+)"/i';
        preg_match( $pattern, $this->xmlContent, $matches );
        return !empty( $matches[1] ) ? $matches[1] : '';
	}

	public function getDuration()
	{
        $pattern = '/itemprop="duration"\s+content="PT(\d+)M(\d+)S"/i';
        preg_match( $pattern, $this->xmlContent, $matches );

        if ( isset($matches[1]) && isset($matches[2]) ) {
            return ((int) $matches[1]) * 60 + ((int) $matches[2]);
        }

        return '';
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
		return CVideosHelper::getIURL('https://img.youtube.com/vi/' . $this->getId() . '/hqdefault.jpg');
	}

	/**
	 *
	 *
	 * @return $embedvideo specific embeded code to play the video
	 */
    public function getViewHTML($videoId, $videoWidth, $videoHeight, $data = array())
    {
        $config = CFactory::getConfig();
        if (!$videoId)
        {
            $videoId    = $this->getId();
        }

        $html  = '<div class="joms-media--video joms-js--video" data-id="' . $videoId . '" data-type="' . $this->getType() . '" data-path="' . $data['path'] . '">';
        $html .= '<img src="' . $data['thumbnail'] . '">';
        $html .= '<a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play"><div class="mejs-overlay-button"></div></a>';
        $html .= '</div>';
        return $html;
    }
}
