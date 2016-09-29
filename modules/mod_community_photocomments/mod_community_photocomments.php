<?php

    /**
     * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
    // no direct access
    defined('_JEXEC') or die('Restricted access');

    // Check if JomSocial core file exists
    $corefile = JPATH_ROOT . '/components/com_community/libraries/core.php';

    jimport('joomla.filesystem.file');
    if (!JFile::exists($corefile)) {
        return;
    }

    // Include JomSocial's Core file, helpers, settings...
    require_once($corefile);
    require_once dirname(__FILE__) . '/helper.php';

    $params->def('privacy', 0);
    $comments = modCommunityPhotoComments::getList($params);
    $config = CFactory::getConfig();
    $user = CFactory::getUser();

    $isPhotoModal = $config->get('album_mode') == 1;
    require(JModuleHelper::getLayoutPath('mod_community_photocomments', $params->get('layout', 'default')));
    $document = JFactory::getDocument();
