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

$usersModel         = CFactory::getModel( 'user' );
$now                = new JDate();
$date               = CTimeHelper::getDate();
$users              = $usersModel->getLatestMember(10);
$totalRegistered    = count($users);
$title              = JText::sprintf('COM_COMMUNITY_TOTAL_USERS_REGISTERED_THIS_MONTH_ACTIVITY_TITLE', $totalRegistered , $date->monthToString($now->format('%m')));

?>

<div class="joms-stream__body joms-stream-box">

    <h4><?php echo JText::_('COM_COMMUNITY_LAST_10_USERS_REGISTERED'); ?></h4>

        <?php if($totalRegistered > 0) { ?>

            <div class="joms-list--block">
            <?php foreach($users  as $user ) { ?>
            <?php
                $registerDate = $user->registerDate;
            ?>
                <div class="joms-stream__header system">
                    <div class= "joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
                        <a href="<?php echo CUrlHelper::userLink($user->id); ?>" class="joms-avatar">
                        <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" >
                    </a>
                    </div>
                    <div class="joms-stream__meta">
                        <a href="<?php echo CUrlHelper::userLink($user->id); ?>">
                            <h4 class="reset-gap"><?php echo $user->getDisplayName(); ?></h4>
                        </a>

                        <small class=" joms-text--light"><?php echo JText::_('COM_COMMUNITY_MEMBER_SINCE'); ?>: <?php echo JHTML::_('date', $registerDate , JText::_('DATE_FORMAT_LC1')); ?>
                        </small>
                    </div>

                </div>
            <?php } ?>
            </div>
        <?php } ?>



</div>
