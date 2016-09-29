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

<div class="joms-landing <?php if (isset($settings) && !$settings['general']['enable-frontpage-image']) echo "no-image"; ?>">
    <div class="joms-landing__cover">
        <?php if (isset($settings) && $settings['general']['enable-frontpage-image']) { ?>
            <div class="joms-landing__image" style="background:url(<?php echo $heroImage; ?>) no-repeat center center;">
            </div>
        <?php } ?>
        <div class="joms-landing__content">
            <div class="joms-landing__text">
                <h2><?php echo JText::_('COM_COMMUNITY_GET_CONNECTED_TITLE'); ?></h2>
                <?php if (isset($settings) && $settings['general']['enable-frontpage-paragraph']) { ?>
                    <p><?php echo JText::_('COM_COMMUNITY_HERO_PARAGRAPH'); ?></p>
                <?php } ?>
            </div>

            <?php  if ($allowUserRegister) : ?>
                <div class="joms-landing__signup">
                    <button class="joms-button--signup"
                            onclick="location.href='<?php echo CRoute::_('index.php?option=com_community&view=register', false); ?>'">
                        <svg viewBox="0 0 16 16" class="joms-icon joms-icon--white">
                            <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-signup"></use>
                        </svg>
                        <?php echo JText::_('COM_COMMUNITY_JOIN_US_NOW'); ?></button>
                </div>
            <?php  endif; ?>
        </div>
    </div>
    <div class="joms-landing__action <?php if(CSystemHelper::tfaEnabled()) { echo 'tfaenabled'; } ?>">
        <form class="joms-form joms-js-form--login" action="<?php echo CRoute::getURI(); ?>" method="post" name="login" id="form-login">
            <div class="joms-input--append">
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-user"></use>
                </svg>
                <input type="text" name="username" class="joms-input" placeholder="<?php echo JText::_('COM_COMMUNITY_USERNAME'); ?>">
            </div>
            <div class="joms-input--append">
                <svg viewBox="0 0 16 16" class="joms-icon">
                    <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-lock"></use>
                </svg>
                <input type="password" name="password" class="joms-input" placeholder="<?php echo JText::_('COM_COMMUNITY_PASSWORD'); ?>">
            </div>
            <?php if(CSystemHelper::tfaEnabled()){?>
                <div class="joms-input--append">
                    <svg viewBox="0 0 16 16" class="joms-icon">
                        <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-key"></use>
                    </svg>
                    <input type="text" name="secretkey" class="joms-input" placeholder="<?php echo JText::_('COM_COMMUNITY_AUTHENTICATION_KEY'); ?>">
                </div>
            <?php } ?>
            <button class="joms-button--login"><?php echo JText::_('COM_COMMUNITY_LOGIN') ?></button>

            <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
                <div class="joms-checkbox">
                    <input type="checkbox" value="yes" name="remember">
                    <span><?php echo JText::_('COM_COMMUNITY_REMEMBER_MY_DETAILS'); ?></span>
                </div>
            <?php endif; ?>

            <a href="<?php echo CRoute::_('index.php?option=' . COM_USER_NAME . '&view=remind'); ?>"><?php echo JText::_('COM_COMMUNITY_FORGOT_USERNAME_LOGIN'); ?></a>
            <a href="<?php echo CRoute::_('index.php?option=' . COM_USER_NAME . '&view=reset'); ?>"
               tabindex="6"><?php echo JText::_('COM_COMMUNITY_FORGOT_PASSWORD_LOGIN'); ?></a>
            <?php if ($useractivation) { ?>
                <a href="<?php echo CRoute::_('index.php?option=com_community&view=register&task=activation'); ?>"
                   class="login-forgot-username"><?php echo JText::_('COM_COMMUNITY_RESEND_ACTIVATION_CODE'); ?></a>
            <?php } ?>
            <input type="hidden" name="option" value="<?php echo COM_USER_NAME; ?>"/>
            <input type="hidden" name="task" value="<?php echo COM_USER_TAKS_LOGIN; ?>"/>
            <input type="hidden" name="return" value="<?php echo $return; ?>"/>
            <div class="joms-js--token"><?php echo JHTML::_('form.token'); ?></div>
        </form>

        <script>
            joms.onStart(function( $ ) {
                $('.joms-js-form--login').on( 'submit', function( e ) {
                    e.preventDefault();
                    e.stopPropagation();
                    joms.ajax({
                        func: 'system,ajaxGetLoginFormToken',
                        data: [],
                        callback: function( json ) {
                            var form = $('.joms-js-form--login');
                            if ( json.token ) {
                                form.find('.joms-js--token input').prop('name', json.token);
                            }
                            form.off('submit').submit();
                        }
                    });
                }).find('[name=username],[name=password],[name=secretkey]').attr('autocapitalize', 'off');
            });
        </script>

        <?php echo $fbHtml; ?>

    </div>
</div>
