<?php
/**
* @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
* @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
* @author iJoomla.com <webmaster@ijoomla.com>
* @url https://www.jomsocial.com/license-agreement
* The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
* More info at https://www.jomsocial.com/license-agreement
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' ); 

$svgPath = CFactory::getPath('template://assets/icon/joms-icon.svg');
include_once $svgPath;

?>


<div class="joms-module--topmembers">

    <?php if ( !empty($users) ) { ?>
    <ul class="joms-list">
        <?php foreach ($users as $user) { ?>
            <li class="joms-stream__header no-gap" >
                <div class="joms-popover__avatar">
                    <div class="joms-avatar">
                        <img src="<?php echo $user->avatar; ?>"
                            title="<?php echo JText::sprintf('MOD_COMMUNITY_TOPMEMBERS_GO_TO_PROFILE', CStringHelper::escape( $user->name ) ); ?>"
                            alt="<?php echo CStringHelper::escape( $user->name ); ?>"
                            data-author="<?php echo $user->id; ?>">
                    </div>
                </div>
                <div class="joms-popover__content">
                    <h5><a href="<?php echo $user->link; ?>"><?php echo $user->name; ?></a></h5>
                    <ul class="joms-list joms-text--light joms-list--inline">
                        <li >
                            <small>
                            <svg class="joms-icon" viewBox="0 0 14 20">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-star"/>
                            </svg>
                            <!-- TOTAL OF USER POINT -->
                            <span><?php echo $user->userpoints; ?></span>
                            </small>
                        </li>
                        <li>
                            <small>
                            <svg class="joms-icon" viewBox="0 0 14 20">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-thumbs-up"/>
                            </svg>
                            <!-- TOTAL OF LIKE -->
                            <span><?php echo $user->likes; ?></span>
                            </small>
                        </li>
                        <li>
                            <small>
                            <svg class="joms-icon" viewBox="0 0 14 20">
                                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-eye"/>
                            </svg>
                            <!-- TOTAL OF VIEW -->
                            <span><?php echo $user->views; ?></span>
                            </small>
                        </li>
                    
                    </ul>
                </div>
            </li>
        <?php } ?>
    </ul>
    <?php } else { ?>
    <div class="joms-blankslate"><?php echo JText::_('MOD_COMMUNITY_TOPMEMBERS_NO_MEMBERS'); ?></div>
    <?php } ?>

</div>
