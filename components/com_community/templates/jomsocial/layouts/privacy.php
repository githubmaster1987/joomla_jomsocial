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

$ddClassnames = array(
    PRIVACY_PUBLIC  => 'joms-icon-earth',
    PRIVACY_MEMBERS => 'joms-icon-users',
    PRIVACY_FRIENDS => 'joms-icon-user',
    PRIVACY_PRIVATE => 'joms-icon-lock'
);

$ddPermissions = array(
    PRIVACY_PUBLIC  => 'COM_COMMUNITY_PRIVACY_PUBLIC',
    PRIVACY_MEMBERS => 'COM_COMMUNITY_PRIVACY_SITE_MEMBERS',
    PRIVACY_FRIENDS => 'COM_COMMUNITY_PRIVACY_FRIENDS',
    PRIVACY_PRIVATE => 'COM_COMMUNITY_PRIVACY_ME'
);

//@since 4.1, we will follow the profile privacy default settings if nothing is set yet
$config = CFactory::getConfig();
$defaultProfilePrivacy = $config->get('privacyprofile');

switch($defaultProfilePrivacy){
    case '0':
        $defaultProfilePrivacy = PRIVACY_PUBLIC;
        break;
    case '20':
        $defaultProfilePrivacy = PRIVACY_MEMBERS;
        break;
    case '30':
        $defaultProfilePrivacy = PRIVACY_FRIENDS;
        break;
    default:
        $defaultProfilePrivacy = PRIVACY_PRIVATE;
}

$selectedAccess = ($selectedAccess) ? $selectedAccess : $defaultProfilePrivacy;

if ($type === 'select') {

?>
<div class="joms-select--wrapper">
<select class="joms-select" name="<?php echo $nameAttribute ?>">
    <?php if (isset($access['public']) && $access['public'] === true) { ?>
    <option value="<?php echo PRIVACY_PUBLIC; ?>"<?php echo $selectedAccess == PRIVACY_PUBLIC ? ' selected="selected"' : ''; ?>>
        <?php echo JText::_( $ddPermissions[PRIVACY_PUBLIC] ); ?>
    </option>
    <?php } ?>
    <?php if (isset($access['members']) && $access['members'] === true) { ?>
    <option value="<?php echo PRIVACY_MEMBERS; ?>"<?php echo $selectedAccess == PRIVACY_MEMBERS ? ' selected="selected"' : ''; ?>>
        <?php echo JText::_( $ddPermissions[PRIVACY_MEMBERS] ); ?>
    </option>
    <?php } ?>
    <?php if (isset($access['friends']) && $access['friends'] === true) { ?>
    <option value="<?php echo PRIVACY_FRIENDS; ?>"<?php echo $selectedAccess == PRIVACY_FRIENDS ? ' selected="selected"' : ''; ?>>
        <?php echo JText::_( $ddPermissions[PRIVACY_FRIENDS] ); ?>
    </option>
    <?php } ?>
    <?php if (isset($access['self']) && $access['self'] === true) { ?>
    <option value="<?php echo PRIVACY_PRIVATE; ?>"<?php echo $selectedAccess == PRIVACY_PRIVATE ? ' selected="selected"' : ''; ?>>
        <?php echo JText::_( $ddPermissions[PRIVACY_PRIVATE] ); ?>
    </option>
    <?php } ?>
</select>
</div>  
<?php

} else {

?>
<div class="joms-button--full-small joms-button--privacy" data-ui-object="joms-dropdown-button" data-name="<?php echo $nameAttribute; ?>">
    <svg viewBox="0 0 16 16" class="joms-icon">
        <use xlink:href="<?php echo CRoute::getURI(); ?>#<?php echo ( isset($ddClassnames[$selectedAccess]) ? $ddClassnames[$selectedAccess] : '' ); ?>"></use>
    </svg>
    <span><?php echo ( isset($ddPermissions[$selectedAccess]) ? JText::_( $ddPermissions[$selectedAccess] ) : '' ); ?></span>
    <input type="hidden" name="<?php echo $nameAttribute; ?>" value="<?php echo $selectedAccess; ?>" data-ui-object="joms-dropdown-value" />
</div>

<ul class="joms-dropdown joms-dropdown--privacy" data-name="<?php echo $nameAttribute; ?>">
    <?php if (isset($access['public']) && $access['public'] === true) { ?>
    <li data-value="<?php echo PRIVACY_PUBLIC; ?>" data-classname="<?php echo $ddClassnames[PRIVACY_PUBLIC]; ?>">
        <svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="<?php echo CRoute::getURI(); ?>#<?php echo $ddClassnames[PRIVACY_PUBLIC]; ?>"></use></svg>
        <span><?php echo JText::_( $ddPermissions[PRIVACY_PUBLIC] ); ?></span>
    </li>
    <?php } ?>
    <?php if (isset($access['members']) && $access['members'] === true) { ?>
    <li data-value="<?php echo PRIVACY_MEMBERS; ?>" data-classname="<?php echo $ddClassnames[PRIVACY_MEMBERS]; ?>">
        <svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="<?php echo CRoute::getURI(); ?>#<?php echo $ddClassnames[PRIVACY_MEMBERS]; ?>"></use></svg>
        <span><?php echo JText::_( $ddPermissions[PRIVACY_MEMBERS] ); ?></span>
    </li>
    <?php } ?>
    <?php if (isset($access['friends']) && $access['friends'] === true) { ?>
    <li data-value="<?php echo PRIVACY_FRIENDS; ?>" data-classname="<?php echo $ddClassnames[PRIVACY_FRIENDS]; ?>">
        <svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="<?php echo CRoute::getURI(); ?>#<?php echo $ddClassnames[PRIVACY_FRIENDS]; ?>"></use></svg>
        <span><?php echo JText::_( $ddPermissions[PRIVACY_FRIENDS] ); ?></span>
    </li>
    <?php } ?>
    <?php if (isset($access['self']) && $access['self'] === true) { ?>
    <li data-value="<?php echo PRIVACY_PRIVATE; ?>" data-classname="<?php echo $ddClassnames[PRIVACY_PRIVATE]; ?>">
        <svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="<?php echo CRoute::getURI(); ?>#<?php echo $ddClassnames[PRIVACY_PRIVATE]; ?>"></use></svg>
        <span><?php echo JText::_( $ddPermissions[PRIVACY_PRIVATE] ); ?></span>
    </li>
    <?php } ?>
</ul>
<?php } ?>
