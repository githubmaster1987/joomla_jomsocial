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

    if(CSystemHelper::tfaEnabled()) {
        // Include the component HTML helpers.
        JHtml::_('behavior.formvalidator');

        JFactory::getDocument()->addScriptDeclaration("
        Joomla.submitbutton = function(task)
        {
            if (task == 'user.cancel' || document.formvalidator.isValid(document.getElementById('user-form')))
            {
                Joomla.submitform(task, document.getElementById('user-form'));
            }
        };

        Joomla.twoFactorMethodChange = function(e)
        {
            var selectedPane = 'com_users_twofactor_' + jQuery('#jform_twofactor_method').val();

            jQuery.each(jQuery('#com_users_twofactor_forms_container>div'), function(i, el) {
                if (el.id != selectedPane)
                {
                    jQuery('#' + el.id).hide(0);
                }
                else
                {
                    jQuery('#' + el.id).show(0);
                }
            });
        };
    ");
    }

?>
<?php echo $miniheader; ?>
<div class="joms-page">
    <h3 class="joms-page__title">
        <?php echo $title; ?>
    </h3>

    <?php //echo $submenu; ?>

    <div class="joms-tab__bar">
        <a class="joms-profile--information" href="#joms-profile--information"><?php echo JText::_('COM_COMMUNITY_PROFILE_SETTING_INFO'); ?></a>
        <a class="joms-profile--account" href="#joms-profile--account"><?php echo JText::_('COM_COMMUNITY_PROFILE_SETTING_ACCOUNT'); ?></a>
    </div>

    <div id="joms-profile--information" class="joms-tab__content" style="display:none;">

        <div class="joms-gap"></div>

        <?php if ($showProfileType) { ?>
            <div>
                <?php if ($multiprofile->id != COMMUNITY_DEFAULT_PROFILE) { ?>
                    <?php echo JText::sprintf('COM_COMMUNITY_CURRENT_PROFILE_TYPE', $multiprofile->name); ?>
                <?php } else { ?>
                    <?php echo JText::_('COM_COMMUNITY_CURRENT_DEFAULT_PROFILE_TYPE'); ?>
                <?php } ?>
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=multiprofile&task=changeprofile'); ?>"><?php echo JText::_('COM_COMMUNITY_CHANGE'); ?></a>
            </div>
        <?php } ?>

        <form name="jsform-profile-edit" id="frmSaveProfile" action="<?php echo CRoute::getURI(); ?>" method="POST"
              class="js-form">
            <?php
                foreach ($fields as $name => $fieldGroup) {
                    // if there is no field for this group, move to next.
                    if (count($fieldGroup) == 0) {
                        continue;
                    }
                    if ($name != 'ungrouped') {
                        ?>

                        <legend class="joms-form__legend"><?php echo JText::_($name); ?></legend>

                    <?php
                    }
                    ?>

                    <?php
                    foreach ($fieldGroup as $f) {
                        $f = Joomla\Utilities\ArrayHelper::toObject($f);
                        // DO not escape 'SELECT' values. Otherwise, comparison for
                        // selected values won't work
                        if ($f->type != 'select') {
                            $f->value = $this->escape($f->value);
                        }
                        ?>

                        <div class="joms-form__group has-privacy" for="field<?php echo $f->id; ?>">
                            <span title="<?php echo JText::_($f->tips)?>"><?php echo JText::_($f->name); ?></span>
                            <?php echo CProfileLibrary::getFieldHTML($f, ''); ?>
                            <?php echo CPrivacy::getHTML('privacy' . $f->id, $f->access); ?>
                        </div>

                    <?php
                    }
                    ?>

                <?php
                }
            ?>

            <?php if (!empty($afterFormDisplay)) { ?>
                <?php echo $afterFormDisplay; ?>
            <?php } ?>

            <div class="joms-form__group">
                <span></span>
                <input type="hidden" name="action" value="profile"/>
                <?php echo JHTML::_('form.token'); ?>
                <input type="submit"
                       class="joms-button--primary joms-button--full-small"
                       value="<?php echo JText::_('COM_COMMUNITY_SAVE_CHANGES_BUTTON'); ?>"/>
            </div>
        </form>

    </div>

    <div id="joms-profile--account" class="joms-tab__content" style="display:none;">

        <form name="jsform-profile-edit" id="frmSaveDetailProfile" action="<?php echo CRoute::getURI(); ?>"
              method="POST" class="js-form" onsubmit="return joms_validate_form( this );">

            <?php if (!empty($beforeFormDisplay)) { ?>
                <div class="before-form">
                    <?php echo $beforeFormDisplay; ?>
                </div>
            <?php } ?>

            <!-- username  -->
            <div class="joms-form__group" for="username">
                <span><?php echo JText::_('COM_COMMUNITY_PROFILE_USERNAME'); ?></span>
                <input class="joms-input" type="text" name="username" <?php echo ($canEditUsername) ? 'data-required="true" data-validation="username"' : 'disabled' ?>
                       value="<?php echo $this->escape($user->get('username')); ?>"
                       data-current="<?php echo $this->escape($user->get('username')); ?>">
            </div>

            <!-- fullname -->
            <?php if (!$isUseFirstLastName) { ?>
                <div class="joms-form__group" for="fullname">
                    <span><?php echo JText::_('COM_COMMUNITY_PROFILE_YOURNAME'); ?></span>
                    <input class="joms-input" type="text" id="name" name="name" size="40"
                           value="<?php echo $this->escape($user->get('name')); ?>"/>
                </div>
            <?php } ?>

            <!-- email -->
            <div class="joms-form__group" for="email">
                <span><?php echo JText::_('COM_COMMUNITY_EMAIL'); ?></span>
                <input type="text" class="joms-input" id="jsemail" name="jsemail"
                       value="<?php echo $this->escape($user->get('email')); ?>"/>
                <input type="hidden" id="email" name="email" value="<?php echo $user->get('email'); ?>"/>
                <input type="hidden" id="emailpass" name="emailpass" id="emailpass"
                       value="<?php echo $this->escape($user->get('email')); ?>"/>
            </div>

            <?php if (!$associated) : ?>
                <?php if ($user->get('password')) : ?>
                    <!-- password -->
                    <div class="joms-form__group" for="password">
                        <span><?php echo JText::_('COM_COMMUNITY_PASSWORD'); ?></span>
                        <input id="jspassword" name="jspassword" class="joms-input" type="password" value=""/>
                    </div>
                    <!-- verify password -->
                    <div class="joms-form__group" for="verifypassword">
                        <span><?php echo JText::_('COM_COMMUNITY_VERIFY_PASSWORD'); ?></span>
                        <input id="jspassword2" name="jspassword2" class="joms-input" type="password" value=""/>
                    </div>

                <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($params)) {
                echo $params->render('params');
            }
            ?>

            <div class="joms-form__group" for="dst-offset">
                <span><?php echo JText::_('COM_COMMUNITY_DAYLIGHT_SAVING_OFFSET'); ?></span>
                <?php echo $offsetList; ?>
            </div>

            <!-- group buttons -->
            <input type="hidden" name="id" value="<?php echo $user->get('id'); ?>"/>
            <input type="hidden" name="gid" value="<?php echo $user->get('gid'); ?>"/>
            <input type="hidden" name="option" value="com_community"/>
            <input type="hidden" name="view" value="profile"/>
            <input type="hidden" name="task" value="edit"/>
            <input type="hidden" id="password" name="password"/>
            <input type="hidden" id="password2" name="password2"/>

            <?php
            //since 4.1, do not show jfbc when user is logged in because jfbc codes doesnt allow that
            if ($config->get('fbconnectkey') && $config->get('fbconnectsecret') && !$config->get('usejfbc')) {

                ?>
                <div class="joms-form__group" for="verifypassword">
                    <span></span>
                    <h4 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_ASSOCIATE_FACEBOOK_LOGIN'); ?></h4>
                    <?php if ($isAdmin) { ?>
                        <div
                            class="small facebook"><?php echo JText::_('COM_COMMUNITY_ADMIN_NOT_ALLOWED_TO_ASSOCIATE_FACEBOOK'); ?></div>
                    <?php } else {
                        if ($associated) { ?>
                            <div><?php echo JText::_('COM_COMMUNITY_ACCOUNT_ALREADY_MERGED'); ?></div>
                            <div>
                                <input<?php echo !empty($fbPostStatus) ? ' checked="checked"' : ''; ?> type="checkbox"
                                                                                                       id="postFacebookStatus"
                                                                                                       name="postFacebookStatus">
                                <label for="postFacebookStatus"
                                       style="display: inline;"><?php echo JText::_('COM_COMMUNITY_ALLOW_SITE_TO_PUBLISH_UPDATES_TO_YOUR_FACEBOOK_ACCOUNT'); ?></label>
                            </div>
                        <?php } else { ?>
                            <?php echo $fbHtml; ?>
                        <?php }
                    } ?>
                </div>
            <?php } ?>

            <?php if(CSystemHelper::tfaEnabled()){?>
                <div class="control-group">
                    <div class="control-label">
                        <label id="jform_twofactor_method-lbl" for="jform_twofactor_method" class="hasTooltip"
                               title="<strong><?php echo JText::_('COM_COMMUNITY_SETUP_AUTH_CONFIG') ?></strong><br /><?php echo JText::_('COM_COMMUNITY_SETUP_AUTH_CONFIG') ?>">
                            <?php echo JText::_('COM_COMMUNITY_SETUP_AUTH_CONFIG'); ?>
                        </label>
                    </div>
                    <div class="controls">
                        <?php echo JHtml::_('select.genericlist', Usershelper::getTwoFactorMethods(), 'jform[twofactor][method]', array('onchange' => 'Joomla.twoFactorMethodChange()'), 'value', 'text', $otpConfig->method, 'jform_twofactor_method', false) ?>
                    </div>
                </div>
                <div id="com_users_twofactor_forms_container">
                    <?php foreach($tfaForm as $form): ?>
                        <?php $style = $form['method'] == $otpConfig->method ? 'display: block' : 'display: none'; ?>
                        <div id="com_users_twofactor_<?php echo $form['method'] ?>" style="<?php echo $style; ?>">
                            <?php echo $form['form'] ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php } ?>

            <div class="joms-form__group">
                <span></span>
                <input type="hidden" name="action" value="detail"/>
                <?php echo JHTML::_('form.token'); ?>
                <input type="submit" class="joms-button--primary joms-button--full-small"
                       value="<?php echo JText::_('COM_COMMUNITY_SAVE_CHANGES_BUTTON'); ?>"/>
            </div>

        </form>

    </div>

</div>

<script>

    // Validate form before submit.
    function joms_validate_form( form ) {
        if ( window.joms && joms.util && joms.util.validation ) {
            joms.util.validation.validate( form, function( errors ) {
                if ( !errors ) {
                    joms.jQuery( form ).removeAttr('onsubmit');
                    setTimeout(function() {
                        joms.jQuery( form ).find('input[type=submit]').click();
                    }, 500 );
                }
            });
        }
        return false;
    }

// set initial opened tab based on the href
(function( href ) {
    var id = 'joms-profile--' + ( href.match(/#detailSet/) ? 'account' : 'information' );
    document.getElementById( id ).style.display = '';
    document.getElementsByClassName( id )[0].className += ' active';
})( window.location.href );
</script>
