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
?>

<?php foreach ($members as $member) { ?>
    <li class="joms-list__item">
        <div class="joms-avatar">
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $member->id );?>">
                <img data-author="<?php echo $member->id ?>"
                    alt="<?php echo $this->escape( $member->getDisplayName() ); ?>"
                    title="<?php echo $member->getDisplayName(); ?>"
                    src="<?php echo $member->getThumbAvatar(); ?>">
            </a>
        </div>
    </li>
<?php } ?>
