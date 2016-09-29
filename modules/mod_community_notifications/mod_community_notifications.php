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

    require_once(dirname(__FILE__) . '/helper.php');
    require_once(JPATH_BASE . '/components/com_community/libraries/core.php');
    CWindow::load();
    $document = JFactory::getDocument();
    $document->addStyleSheet(JURI::root(true) . '/modules/mod_community_notifications/style.css');
    $config = CFactory::getConfig();
    $my = CFactory::getUser();

    require(JModuleHelper::getLayoutPath('mod_community_notifications', $params->get('layout', 'default')));