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

require_once( JPATH_ROOT .'/components/com_community/libraries/core.php');

if(!class_exists('plgCommunityMyTaggedVideos'))
{
	class plgCommunityMyTaggedVideos extends CApplications
	{
		var $name		= 'MyTaggedVideos';
		var $_name		= 'myTaggedVideos';
		var $_user		= null;


	    function __construct(& $subject, $config)
	    {
            parent::__construct($subject, $config);
            $this->db = JFactory::getDbo();
			$this->_my = CFactory::getUser();
	    }

		/**
		 * Ajax function to save a new wall entry
		 *
		 * @param message	A message that is submitted by the user
		 * @param uniqueId	The unique id for this group
		 *
		 **/
		function onProfileDisplay()
		{
			JPlugin::loadLanguage( 'plg_community_mytaggedvideos', JPATH_ADMINISTRATOR );
			$mainframe = JFactory::getApplication();
            $jinput = JFactory::getApplication()->input;

			// Attach CSS
			$document	= JFactory::getDocument();
			// $css		= JURI::base() . 'plugins/community/myvideos/style.css';
			// $document->addStyleSheet($css);
			$user     = CFactory::getRequestUser();
			$userid	= $user->id;
			$this->loadUserParams();

			$limit = $this->params->get('count', 6);
			$limitstart = $jinput->get('limitstart', 0);
			$row = $this->getVideos($userid);
			$total = count($row);

            //we must filter the results
            $results = array();
            $limitCount = 0;
            foreach($row as $result){
                if(!CPrivacy::isAccessAllowed($this->_my->id, $userid, 'custom', $result->permissions)){
                    continue;
                }

                $results[] = $result;

                if(++$limit == $limitCount){
                    break;
                }
            }

            if($this->params->get('hide_empty', 0) && !$total) return '';

			$caching = $this->params->get('cache', 1);
			if($caching)
			{
				$caching = $mainframe->getCfg('caching');
			}

			$cache = JFactory::getCache('plgCommunityMyTaggedVideos');
			$cache->setCaching($caching);
			$callback = array('plgCommunityMyTaggedVideos', '_getLatestVideosHTML');
			$content = $cache->call($callback, $userid, $this->userparams->get('count', 5 ), $limitstart, $results, $total);

			return $content;
		}

		static public function _getLatestVideosHTML($userid, $limit, $limitstart, $row, $total)
		{
			//
			//CFactory::load( 'models' , 'videos' );
			$video = JTable::getInstance( 'Video' , 'CTable' );

			ob_start();
			if(!empty($row))
			{
				?>

					<ul class="joms-list--half clearfix">
				<?php
				$i = 1;
				foreach($row as $data)
				{
					if($i > $limit){
						break;
					}
					$i++;
					$video->load( $data->id );
					$link = plgCommunityMyTaggedVideos::buildLink($data->id);
					$thumbnail	= $video->getThumbnail();
					?>
						<li class="joms-list__item">
							<a href="<?php echo $link; ?>" class="joms-block" >
								<img title="<?php echo CTemplate::escape($video->getTitle());?>" src="<?php echo $thumbnail; ?>"/>
								<span class="joms-video__duration"><?php echo $video->getDurationInHMS()?></span>
							</a>
						</li>
					<?php
				}
				?>
					</ul>

                    <div class="joms-gap"></div>

					<a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos&sort=tagged&userid='.$userid); ?>">
						<span><?php echo JText::_('PLG_MYTAGGEDVIDEOS_VIEWALL_VIDEOS');?></span>
					</a>


				<?php
			}
			else
			{
				?>
				<div><?php echo JText::_('PLG_MYTAGGEDVIDEOS_NO_VIDEOS')?></div>
				<?php
			}
			?>

			<?php
			$contents  = ob_get_contents();
			@ob_end_clean();
			$html = $contents;

			return $html;
		}

		public function getVideos($userid)
		{
			//get videos from the user
			//CFactory::load('models', 'videos');
			$model	= CFactory::getModel( 'VideoTagging' );

            if ($this->_my->id == $userid || COwnerHelper::isCommunityAdmin()) {
                $permission = 40;
            } elseif (CFriendsHelper::isConnected($this->_my->id, $userid)) {
                $permission = 30;
            } elseif ($this->_my->id != 0) {
                $permission = 20;
            } else {
                $permission = 10;
            }

			$videos = $model->getTaggedVideosByUser($userid, $permission);

			return $videos;
		}

		static public function buildLink($videoId)
		{
			$video	= JTable::getInstance( 'Video' , 'CTable' );
			$video->load( $videoId );

			return $video->getURL();
		}

	}
}
