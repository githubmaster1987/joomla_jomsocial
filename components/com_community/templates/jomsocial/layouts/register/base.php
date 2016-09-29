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

<div class="joms-page">

    <h3 class="joms-page__title">
        <?php echo JText::_('COM_COMMUNITY_REGISTER'); ?>
    </h3>

    <form method="POST" action="<?php echo CRoute::getURI(); ?>" onsubmit="return joms_validate_form( this );">

    <?php if ($isUseFirstLastName) { ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_FIRST_NAME'); ?> <span class="joms-required">*</span></span>
            <input type="text" name="jsfirstname" value="<?php echo $data['html_field']['jsfirstname']; ?>" class="joms-input"
                data-required="true">
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_LAST_NAME'); ?> <span class="joms-required">*</span></span>
            <input type="text" name="jslastname" value="<?php echo $data['html_field']['jslastname']; ?>" class="joms-input"
                data-required="true">
        </div>

    <?php } else { ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_NAME'); ?> <span class="joms-required">*</span></span>
            <input type="text" name="jsname" value="<?php echo $data['html_field']['jsname']; ?>" class="joms-input"
                data-required="true">
        </div>

    <?php } ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_USERNAME'); ?> <span class="joms-required">*</span></span>
            <input type="text" name="jsusername" value="<?php echo $data['html_field']['jsusername']; ?>" class="joms-input"
                data-required="true" data-validation="username">
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_EMAIL'); ?> <span class="joms-required">*</span></span>
            <input type="text" id="jsemail" name="jsemail" value="<?php echo $data['html_field']['jsemail']; ?>" class="joms-input"
                data-required="true" data-validation="email" data-verify="[name=jsemailconfirm]">
        </div>

        <?php if ($isConfirmEmail) { ?>
        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_VERIFY_EMAIL'); ?> <span class="joms-required">*</span></span>
            <input type="text" name="jsemailconfirm" value="<?php echo isset($data['html_field']['jsemailconfirm']) ? $data['html_field']['jsemailconfirm'] : ''; ?>" class="joms-input"
                data-required="true" data-validation="email:#jsemail">
        </div>
        <?php } ?>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_PASSWORD'); ?> <span class="joms-required">*</span></span>
            <input type="password" id="jspassword" name="jspassword" value="" class="joms-input" autocomplete="off"
                data-required="true" data-validation="password" data-verify="[name=jspassword2]">
        </div>

        <div class="joms-form__group">
            <span><?php echo JText::_('COM_COMMUNITY_VERIFY_PASSWORD'); ?> <span class="joms-required">*</span></span>
            <input type="password" name="jspassword2" value="" class="joms-input" autocomplete="off"
                data-required="true" data-validation="password:#jspassword">
        </div>

        <?php if ( !empty($recaptchaHTML) ) { ?>
        <div class="joms-form__group">
            <span></span>
            <?php echo $recaptchaHTML; ?>
        </div>
        <?php } ?>

        <div class="joms-form__group">
            <span></span>
            <?php echo JText::_('COM_COMMUNITY_REGISTER_REQUIRED_FIELDS'); ?>
        </div>

        <div class="joms-form__group">
            <span></span>
            <button type="submit" name="submit" class="joms-button__next joms-button--full-small">
                <?php echo JText::_('COM_COMMUNITY_NEXT'); ?>
                <span class="joms-loading" style="display:none">&nbsp;
                    <img src="<?php echo JURI::root(true) ?>/components/com_community/assets/ajax-loader.gif" alt="loader">
                </span>
            </button>

            <?php if ( $config->get('enableterms') ) { ?>
            <div class="joms-checkbox">
                <input type="checkbox" name="tnc" value="Y" data-message="<?php echo JText::_('COM_COMMUNITY_REGISTER_ACCEPT_TNC'); ?>">
                <span><?php echo JText::_('COM_COMMUNITY_I_HAVE_READ') . ' <a href="javascript:" onclick="joms.api.tnc(0);">' . JText::_('COM_COMMUNITY_TERMS_AND_CONDITION') . '</a>.'; ?></span>
            </div>
            <?php } ?>

        </div>

        <?php if ( $fbHtml ) { ?>
        <div class="joms-form__group">
            <span></span>
            <?php echo $fbHtml;?>
        </div>
        <?php } ?>

        <input type="hidden" name="isUseFirstLastName" value="<?php echo $isUseFirstLastName; ?>">
        <input type="hidden" name="task" value="register_save">
        <input type="hidden" name="id" value="0">
        <input type="hidden" name="gid" value="0">
        <input type="hidden" id="authenticate" name="authenticate" value="0">
        <input type="hidden" id="authkey" name="authkey" value="">

    </form>

</div>

<script>

    // Validate form before submit.
    function joms_validate_form( form ) {
        if ( window.joms && joms.util && joms.util.validation ) {
            // Prevents repeated clicks.
            if ( window.joms_validating_form ) return;
            window.joms_validating_form = true;
            joms.jQuery('.joms-loading').show();

            joms.util.validation.validate( form, function( errors ) {
                if ( !errors ) {
                    joms.jQuery( form ).removeAttr('onsubmit');
                    setTimeout(function() {
                        joms.jQuery( form ).find('button[type=submit]').click();
                    }, 500 );
                } else {
                    joms.jQuery('.joms-loading').hide();
                    window.joms_validating_form = false;
                }
            });
        }
        return false;
    }

    joms.onStart(function( $ ) {
        function insertAuthkey() {
            joms.ajax({
                func: 'register,ajaxGenerateAuthKey',
                data: [ '_dummy_' ],
                callback: function( json ) {
                    joms.jQuery('#authenticate').val( 1 );
                    joms.jQuery('#authkey').val( json.authKey );
                    joms.jQuery('#login-form input, #form-login input, form[name=login] input').filter(function() {
                        return this.name.match(/[0-9a-z]{32}/);
                    }).prop( 'name', json.authKey );
                }
            });
        }

        var timer = setInterval(function() {
            if ( joms.ajax ) {
                clearInterval( timer );
                insertAuthkey();
            }
        }, 100);
    });
</script>
