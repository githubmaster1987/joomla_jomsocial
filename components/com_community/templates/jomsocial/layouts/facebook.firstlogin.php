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

<div>
    <div class="joms-stream__header" style="padding:0">
        <div class="joms-avatar--stream">
            <a href="<?php echo (isset($userInfo['profile_url'])) ? $userInfo['profile_url'] : $userInfo['link'] ; ?>"><img src="<?php echo $userInfo['pic_square']; ?>" alt="<?php echo $userInfo['name']; ?>" ></a>
        </div>
        <div class="joms-stream__meta">
            <span><?php echo JText::sprintf('COM_COMMUNITY_FACEBOOK_CONNECT_DESCRIPTION', $userInfo['name']); ?></span>
        </div>
    </div>
</div>

<div style="margin-top:15px">
    <strong><?php echo JText::_('COM_COMMUNITY_I_AM_CURRENTLY'); ?></strong>

    <div class="joms-form__group" style="margin-top:15px; color:inherit">
        <span style="width:30px">
            <input type="radio" name="membertype" id="joms-js--fbc-newuser" value="1" checked="checked">
        </span>
        <div>
            <label for="joms-js--fbc-newuser" style="font-size:inherit">
                <strong><?php echo JText::_('COM_COMMUNITY_A_NEW_USER'); ?></strong>
                <p class="joms-help"><?php echo JText::_('COM_COMMUNITY_NEW_MEMBER_DESCRIPTION'); ?></p>
            </label>
        </div>
    </div>

    <div class="joms-form__group" style="margin-top:15px; color:inherit">
        <span style="width:30px">
            <input type="radio" name="membertype" id="joms-js--fbc-existing" value="2">
        </span>
        <div>
            <label for="joms-js--fbc-existing" style="font-size:inherit">
                <strong><?php echo JText::_('COM_COMMUNITY_MEMBER_OF_SITE'); ?></strong>
                <p class="joms-help"><?php echo JText::_('COM_COMMUNITY_EXISTING_SITE_MEMBER_DESCRIPTION'); ?></p>
            </label>
        </div>
    </div>

    <?php if ( $config->get('enableterms') ) { ?>
        <div class="joms-form__group joms-js--fbc-tnc" style="margin-top:15px; color:inherit">
        <span style="width:30px">
            <input type="checkbox" name="tnc" id="joms-js--fbc-tnc-checkbox" value="Y" class="required" style="margin:0">
        </span>
            <div>
                <p class="joms-help joms-js--fbc-tnc-error" style="color:red; display:none"><?php echo JText::_('COM_COMMUNITY_FACEBOOK_ACCEPT_TNC_ERROR'); ?></p>
                <label for="joms-js--fbc-tnc-checkbox" style="font-size:inherit; margin:0">
                    <?php
                    echo JText::_('COM_COMMUNITY_I_HAVE_READ') .
                        ' <a href="javascript:" class="joms-js--fbc-tnc-toggle">' . JText::_('COM_COMMUNITY_TERMS_AND_CONDITION') . '</a>.';
                    ?>
                </label>
                <div class="joms-help joms-js--fbc-tnc-desc" style="display:none;max-height:150px;overflow:auto">
                    <?php echo nl2br( $config->get('registrationTerms') ); ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <div class="joms-form__group" style="margin-top:15px">
        <div>
            <p class="joms-help" style="color:red"><?php echo JText::_('COM_COMMUNITY_LINKING_NOTICE'); ?></p>
        </div>
    </div>
</div>

<script>
    joms.jQuery('[name=membertype]').change(function() {
        joms.jQuery('.joms-js--fbc-tnc')[ this.value == 2 ? 'hide' : 'show' ]();
    });

    joms.jQuery('.joms-js--fbc-tnc-toggle').click(function() {
        joms.jQuery('.joms-js--fbc-tnc-desc').toggle();
    });
</script>
