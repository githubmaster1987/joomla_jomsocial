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
?>

<form method="POST" action="<?php echo CRoute::getURI();?>">

    <div class="joms-form__group">
        <span>
            <?php echo JText::_('COM_COMMUNITY_USERNAME'); ?>
            <span class="joms-required">*</span>
            <small>(<a href="<?php echo CRoute::_('index.php?option=' . COM_USER_NAME . '&view=remind'); ?>" tabindex="5"><?php
                echo JText::_('COM_COMMUNITY_FORGOT'); ?></a>)</small>
        </span>
        <input class="joms-input" type="text" name="username" tabindex="1" required="">
    </div>

    <div class="joms-form__group">
        <span>
            <?php echo JText::_('COM_COMMUNITY_PASSWORD'); ?>
            <span class="joms-required">*</span>
            <small>(<a href="<?php echo CRoute::_('index.php?option=' . COM_USER_NAME . '&view=reset'); ?>" tabindex="6"><?php
                echo JText::_('COM_COMMUNITY_FORGOT'); ?></a>)</small>
        </span>
        <input class="joms-input" type="password" name="<?php echo COM_USER_PASSWORD_INPUT; ?>" tabindex="2" required="">
    </div>

    <?php if(CSystemHelper::tfaEnabled()){?>
        <div class="joms-form__group">
            <span>
                <?php echo JText::_('COM_COMMUNITY_AUTHENTICATION_KEY'); ?>
                    <span class="joms-required">*</span>
            </span>
            <input class="joms-input" type="text" name="secretkey" placeholder="<?php echo JText::_('COM_COMMUNITY_AUTHENTICATION_KEY'); ?>">
        </div>
    <?php } ?>

    <div class="joms-form__group">
        <span></span>
        <?php if (JPluginHelper::isEnabled('system', 'remember')) { ?>
        <label>
            <input type="checkbox" name="remember" value="yes" alt="<?php echo JText::_('COM_COMMUNITY_REMEMBER_MY_DETAILS'); ?>" tabindex="3">
            <?php echo JText::_('COM_COMMUNITY_REMEMBER_MY_DETAILS'); ?>
        </label>
        <?php } ?>
    </div>

    <div class="joms-form__group">
        <span></span>
        <input type="hidden" name="option" value="<?php echo COM_USER_NAME; ?>">
        <input type="hidden" name="task" value="<?php echo COM_USER_TAKS_LOGIN; ?>">
        <input type="hidden" name="return" value="<?php echo $return; ?>">
        <input type="submit" name="submit" class="joms-button--primary joms-button--full-small" value="<?php echo JText::_('COM_COMMUNITY_LOGIN_BUTTON'); ?>" tabindex="4">
        <div class="joms-js--token"><?php echo JHTML::_('form.token'); ?></div>
    </div>

    <div class="joms-form__group">
        <?php if ($useractivation) { ?>
        <a href="<?php echo CRoute::_('index.php?option=com_community&view=register&task=activation'); ?>">
            <?php echo JText::_('COM_COMMUNITY_RESEND_ACTIVATION_CODE'); ?>
        </a>
        <?php } ?>
        <?php if ($allowUserRegister) { ?>
        - <a href="<?php echo CRoute::_('index.php?option=com_community&view=register', false); ?>">
            <?php echo JText::_('COM_COMMUNITY_REGISTER_NOW_TO_GET_CONNECTED'); ?>
        </a>
        <?php } ?>
    </div>

</form>

<?php echo $fbHtml; ?>
