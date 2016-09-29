<?php
/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die();

$user = CFactory::getUser($this->act->actor);

$date = JDate::getInstance($act->created);
if ( $config->get('activitydateformat') == "lapse" ) {
    $createdTime = CTimeHelper::timeLapse($date);
} else {
    $createdTime = $date->format($config->get('profileDateFormat'));
}

$event = JTable::getInstance('Event','CTable');
$event->load($this->act->eventid);

?>

<div class="joms-stream__header">
    <div class="joms-avatar--comment <?php echo CUserHelper::onlineIndicator($user); ?>">
        <img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" data-author="<?php echo $user->id; ?>">
    </div>
    <div class="joms-stream__meta">
        <a href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
        <span><?php echo JText::sprintf('COM_COMMUNITY_CHANGE_EVENT_S_AVATAR', $event->getLink(), $event->title); ?></span>
        <span class="joms-stream__time"><small>
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$act->actor.'&actid='.$act->id); ?>">
                <?php echo $createdTime; ?>
                </a>
            </small></span>
    </div>
    <?php
        $my = CFactory::getUser();
        $this->load('activities.stream.options');

    ?>
</div>

<div class="joms-stream__body">
    <div class="joms-avatar">
        <a href="javascript:" onclick="joms.api.photoZoom('<?php echo $event->getAvatar(); ?>');">
            <img src="<?php echo $event->getAvatar(); ?>" alt="avatar" >
        </a>
    </div>
</div>

<?php $this->load('stream/footer'); ?>
