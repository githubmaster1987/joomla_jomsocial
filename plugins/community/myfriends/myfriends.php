<?php
    /**
     * @copyright (C) 2014 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
// no direct access
    defined('_JEXEC') or die('Restricted access');

    require_once(JPATH_ROOT . '/components/com_community/libraries/core.php');

    if (!class_exists('plgCommunityMyFriends')) {
        class plgCommunityMyFriends extends CApplications
        {
            var $_user = null;
            var $name = "Friends";
            var $_name = 'myfriends';

            function __construct(& $subject, $config)
            {
                $this->_user = CFactory::getRequestUser();
                parent::__construct($subject, $config);
            }

            function onProfileDisplay()
            {
                JPlugin::loadLanguage( 'plg_community_myfriends', JPATH_ADMINISTRATOR );
                return $this->_getMyFriendsHTML();
            }

            public function _getMyFriendsHTML($userid = null)
            {
                $document = JFactory::getDocument();
                $this->loadUserParams();
                $count = $this->userparams->get('count', $this->params->get('count', 10) );

                $is_rtl = ($document->direction == 'rtl') ? 'dir="rtl"' : '';

                $html = '';

                $friendsModel = CFactory::getModel('friends');

                $my = CFactory::getUser($userid);
                $user = CFactory::getRequestUser();

                $params = $user->getParams();

                // site visitor
                $relation = 10;

                // site members
                if ($my->id != 0) {
                    $relation = 20;
                }

                // friends
                if (CFriendsHelper::isConnected($my->id, $user->id)) {
                    $relation = 30;
                }

                // mine
                if (COwnerHelper::isMine($my->id, $user->id)) {
                    $relation = 40;
                }

                // @todo: respect privacy settings
                if ($relation >= $params->get('privacyFriendsView')) {
                    $friends = $friendsModel->getFriends(
                        $user->id,
                        'latest',
                        false,
                        '',
                        $count + $count
                    );

                    // randomize the friend count
                    if ($friends) {
                        shuffle($friends);
                    }
                    $total = $user->getFriendCount();
                    if($this->params->get('hide_empty', 0) && !$total) return '';
                    ob_start();
                    ?>

                    <?php if ($friends) {
                            ?>
                            <ul class='joms-list--thumbnail'>
                                <?php
                                    for ($i = 0; $i < count($friends); $i++) {
                                        if($i>=$count) break;
                                        $friend =& $friends[$i];
                                        ?>
                                        <li class='joms-list__item'>
                                            <div class="joms-avatar <?php echo CUserHelper::onlineIndicator($friend); ?>">
                                                <a href="<?php echo CRoute::_(
                                                    'index.php?option=com_community&view=profile&userid=' . $friend->id
                                                ); ?>" >
                                                    <img alt="<?php echo $friend->getDisplayName(); ?>"
                                                         title="<?php echo $friend->getTooltip(); ?>"
                                                         src="<?php echo $friend->getThumbAvatar(); ?>"
                                                         data-author="<?php echo $friend->id; ?>"
                                                         />
                                                </a>
                                            </div>
                                        </li>
                                    <?php } ?>
                            </ul>
                        <?php
                        } else {
                            ?>
                            <div class="cEmpty"><?php echo JText::_('COM_COMMUNITY_NO_FRIENDS_YET'); ?></div>
                    <?php }
                    if($total>$count) {
                    ?>

                    <div class="joms-gap"></div>

                    <a href="<?php echo CRoute::_(
                        'index.php?option=com_community&view=friends&userid=' . $user->id
                    ); ?>">
                        <span><?php echo JText::_('COM_COMMUNITY_FRIENDS_VIEW_ALL'); ?></span>
                        <span <?php echo $is_rtl; ?> > (<?php echo $total; ?>)</span>
                    </a>
                        <?php } ?>

                    <?php
                    $html = ob_get_contents();
                    ob_end_clean();
                }

                return $html;
            }

        }
    }
