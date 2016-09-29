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

    class plgCommunityMyRSSFeeds extends CApplications
    {
        var $name		= 'Feeds';
        var $_name		= 'feeds';
        var $_user		= null;

        function onProfileDisplay()
        {
            JPlugin::loadLanguage( 'plg_community_myrssfeeds', JPATH_ADMINISTRATOR );

            // Attach CSS
            $document	= JFactory::getDocument();
            $css		= JURI::base() . 'plugins/community/feeds/feeds/style.css';
            $document->addStyleSheet($css);

            $model 	= CFactory::getModel('profile');
            $my		= CFactory::getUser();
            $user	= CFactory::getRequestUser();
            $this->loadUserParams();
            $mainframe	= JFactory::getApplication();
            $data		= $model->getViewableProfile( $user->id );
            $path		= $this->userparams->get( 'path' , '' );
            $limit		= $this->userparams->get( 'count' , '' );
            $cacheable	= $this->params->get( 'cache' , 1 );
            $cacheable	= $cacheable ? $mainframe->getCfg( 'caching' ) : $cacheable;

            $cache		= JFactory::getCache('community');
            $cache->setCaching( $cacheable );
            $content	= $cache->call( array( $this, '_getFeedHTML') , $path, $limit, $this->getLayout() );

            return $content;
        }

        /**
         * Return html-formatted stream display
         * @param  obj $act activityTableObject
         * @return html
         */
        public function getStreamHTML($act){
            return "hello";
        }

        function _getFeedHTML( $url , $limit , $layout )
        {
            if( empty( $url ) )
            {
                ob_start();
                ?>
                <div id="application-feeds">
                    <div class="cAlert cEmpty">
                        <!-- <img class="icon-nopost" src="<?php echo JURI::root();?>components/com_community/assets/error.gif" alt="" /> -->
                        <span class="content-nopost"><?php echo JText::_('PLG_FEEDS_INVALID_FEED_PATH');?></span>
                    </div>
                </div>
                <?php
                $html	= ob_get_contents();
                ob_end_clean();

                return $html;
            }

            $feed = new JFeedFactory;
            $feed = $feed->getFeed($url);
            //$items	= $feed->get_items( 0 , $limit );

            switch($layout)
            {
                case "sidebar-top":
                case "sidebar-bottom":
                case "sidebar-bottom-stacked" :
                case "sidebar-top-stacked" :
                    $content = self::getWidgetLayout($feed, $limit);
                    break;
                case "content":
                default:
                    $content = self::getContentLayout($feed, $limit);
                    break;
            }

            return $content;
        }

        static public function getContentLayout($items, $limit)
        {
            ob_start();
            if(count($items) > 0)
            {
                ?>
                <div id="application-feeds" >
                    <?php
                        for($i = 0; ($i < count($items) && ($i<$limit)); $i++)
                        {
                            $item = $items[$i];
                            $feed	= $item->get_feed();
                            ?>
                            <div class="feed-row">
                                <div class="feed-date">
                                    <?php echo $item->get_date('j'); ?>
                                    <?php echo $item->get_date('M'); ?>
                                    <?php echo $item->get_date('Y'); ?>
                                </div>
                                <h4><a href="<?php echo $item->get_permalink(); ?>" rel="nofollow"><?php echo $item->get_title(); ?></a></h4>
                                <div class="feed-content joms-text--desc">
                                    <?php echo $item->get_content(); ?>
                                </div>
                            </div>
                        <?php
                        }
                    ?>
                </div>
            <?php
            }
            else
            {
                ?>
                <div id="application-feeds">
					<span class="content-nopost">
					   <?php echo JText::_('PLG_FEEDS_UNABLE_TO_READ_FEED_CONTENT');?>
				   </span>
                </div>
            <?php
            }
            $content	= ob_get_contents();
            ob_end_clean();

            return $content;
        }

        static public function getWidgetLayout($items, $limit)
        {
            ob_start();
            if(count($items) > 0)
            {
                ?>
                <div id="application-feeds">
                    <?php
                        for($i = 0; $i < 10 ; $i++)
                        {
                            $item = $items[$i];
                            $date = new JDate($item->publishedDate);
                            ?>
                            <div class="feed-row">
                                <div class="feed-date">
                                    <?php echo $date->format('j M Y'); ?>
                                </div>
                                <h4><a href="<?php echo $item->uri; ?>" rel="nofollow"><?php echo $item->title; ?></a></h4>
                                <div class="feed-content joms-text--desc">
                                    <?php echo $item->content; ?>
                                </div>
                            </div>
                        <?php
                        }
                    ?>
                </div>
            <?php
            }
            else
            {
                ?>
                <div>
                    <?php echo JText::_('PLG_FEEDS_UNABLE_TO_READ_FEED_CONTENT');?>
                </div>
            <?php
            }
            $content	= ob_get_contents();
            ob_end_clean();

            return $content;
        }

    }
