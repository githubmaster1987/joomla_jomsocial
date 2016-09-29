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

$item_per_page = 8;
$count = count( $users );
$pages = ceil( $count / $item_per_page );

?>

<div style="max-height:250px; overflow:auto; padding: 10px;">

<?php

for ($i = 0; $i < $count; $i++) {
    $user = $users[$i];

?>

<div>
    <div style="padding: 2px 0;" class="joms-stream__header">
        <div class="joms-avatar--comment <?php echo CUserHelper::onlineIndicator($user); ?>">
            <img data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>">
        </div>
        <div class="joms-stream__meta">
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
            <span class="joms-stream__time"> </span>
        </div>
    </div>
</div>

<?php } ?>

</div>
