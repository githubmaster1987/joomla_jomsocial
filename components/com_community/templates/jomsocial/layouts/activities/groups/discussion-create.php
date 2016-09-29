<?php
    /**
     * @copyright (C) 2014 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
    // Check to ensure this file is included in Joomla!

    defined('_JEXEC') or die('Restricted access');
?>

<div class="joms-media">
    <h4 class="joms-text--title">
        <a href="<?php echo $stream->link; ?>">
        <?php echo CActivities::truncateComplex($stream->title, 60, true); ?>
        </a>
    </h4>
    <p class="joms-text--desc"><?php echo CActivities::format($attachment->message); ?></p>
    <?php echo $stream->group->name ?>
</div>
