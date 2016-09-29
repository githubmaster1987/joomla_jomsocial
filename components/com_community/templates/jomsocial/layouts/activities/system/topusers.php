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

$model		= CFactory::getModel( 'user' );
$members		= $model->getPopularMember( 10 );
$html    = '';

?>

<div class="joms-stream__body joms-stream-box" >
    <h4 ><?php echo JText::_('COM_COMMUNITY_ACTIVITIES_TOP_PROFILES'); ?></h4>
    <div class="joms-list--block">
        <?php foreach( $members as $user ) {
                $numFriends = $user->getFriendCount();
        ?>
        <div class="joms-stream__header system">
            <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id );?>">
                <img alt="<?php echo $this->escape($user->getDisplayName());?>" src="<?php echo $user->getThumbAvatar();?>" data-author="<?php echo $user->id; ?>" />
                </a>
            </div>
            <div class="joms-stream__meta">
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id );?>">
                    <h4 class="reset-gap"><?php echo CTooltip::cAvatarTooltip($user); ?></h4>
                </a>
                <?php
                    $isFriend =  CFriendsHelper::isConnected( $user->id, $my->id );
                    $addFriend  = ((! $isFriend) && ($my->id != 0) && $my->id != $user->id) ? true : false;
                    if($addFriend) {
                    $isWaitingApproval =    CFriendsHelper::isWaitingApproval($my->id, $user->id); ?>
                    <?php if(isset($user->isMyFriend) && $user->isMyFriend==1){ ?>
                        <a href="javascript:void(0)" onclick="joms.api.friendAdd('<?php echo $user->id;?>')"><span><?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?></span></a>
                        <?php } else { ?>
                        <?php if(!$isWaitingApproval){?>
                            <a href="javascript:void(0)" onclick="joms.api.friendAdd('<?php echo $user->id;?>')"><span><?php echo JText::_('COM_COMMUNITY_PROFILE_ADD_AS_FRIEND'); ?></span></a>
                        <?php } else { ?>
                        <span class="joms-text--light"><?php echo JText::_('COM_COMMUNITY_PROFILE_PENDING_FRIEND_REQUEST'); ?></span>
                        <?php }?>
                    <?php } ?>
                    <?php } else { ?>
                    <?php if( ($my->id != $user->id) && ($my->id !== 0) ) { ?>
                        <span class="joms-text--light"> <?php echo JText::_('COM_COMMUNITY_PROFILE_ADDED_AS_FRIEND'); ?></span>
                    <?php } } ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
