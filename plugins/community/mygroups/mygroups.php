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

if (!class_exists('plgCommunityMyGroups')) {
    class plgCommunityMyGroups extends CApplications
    {
        var $_user = null;
        var $name = "Friends";
        var $_name = 'mygroups';

        function __construct(& $subject, $config)
        {
            $this->_user = CFactory::getRequestUser();
            parent::__construct($subject, $config);
        }

        function onProfileDisplay()
        {
            JPlugin::loadLanguage( 'plg_community_mygroups', JPATH_ADMINISTRATOR );
            return $this->_getMyGroupsHTML();
        }

        public function _getMyGroupsHTML($userid = null)
        {
            $document = JFactory::getDocument();
            $is_rtl = ($document->direction == 'rtl') ? 'dir="rtl"' : '';

            $html = '';

            $groupsModel = CFactory::getModel('groups');

            $my = CFactory::getUser($userid);
            $user = CFactory::getRequestUser();

            $this->loadUserParams();
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

            if ($relation >= $params->get('privacyGroupsView')) {

                // count the groups
                $groups = $groupsModel->getGroups(
                    $user->id,
                    'latest',
                    false
                );

                $total = count($groups);
                if($this->params->get('hide_empty', 0) && !$total) return '';

                $count = $this->userparams->get('count', $this->params->get('count', 10) );

                $groupsModel->setState('limit', $count);
                $groups = $groupsModel->getGroups(
                    $user->id,
                    'latest',
                    false
                );

                if ($groups) {
                    shuffle($groups);
                }
                $i=0;
                ob_start();
                ?>

                <?php
                if ($groups) {
                    ?>
                    <?php foreach($groups as $group) {

                        if($i >= $count) break;

                        $table	=  JTable::getInstance( 'Group' , 'CTable' );
                        $table->load($group->id);

                        if($table->unlisted && !$groupsModel->isMember($my->id, $table->id)) continue;

                        $i++;
                        ?>
                        <div class="joms-stream__header">

                            <div class="joms-avatar--stream">
                                <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&groupid='.$group->id.'&task=viewgroup'); ?>">
                                    <img src="<?php echo $table->getThumbAvatar(); ?>" alt="<?php echo CStringHelper::escape( $group->name ); ?>" >
                                </a>
                            </div>


                            <div class="joms-stream__meta">
                                <a class="joms-text--title" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&groupid='.$group->id.'&task=viewgroup'); ?>">
                                    <?php echo $group->name; ?>
                                </a>

                                <a href="<?php echo CRoute::_( "index.php?option=com_community&view=groups&task=viewmembers&groupid=" . $group->id ); ?>" class="joms-block"><small>
                                        <?php echo JText::sprintf( (!CStringHelper::isSingular($group->membercount)) ? 'COM_COMMUNITY_GROUPS_MEMBERS_MANY':'COM_COMMUNITY_GROUPS_MEMBERS_SINGULAR', $group->membercount); ?>
                                    </small></a>

                            </div>
                        </div>

                    <?php } ?>

                <?php } else {
                    ?>
                    <div><?php echo JText::_('COM_COMMUNITY_NO_GROUPS_YET'); ?></div>
                <?php
                }

                if($i < $total) { ?>
                    <div class="joms-gap"></div>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=mygroups&userid='.$userid); ?>">
                        <span><?php echo JText::_('PLG_MYGROUPS_VIEWALL_GROUPS');?></span>
                        <span>(<?php echo $total;?>)</span>
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
