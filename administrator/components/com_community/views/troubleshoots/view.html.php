<?php

/**
 * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

if (!class_exists('CommunityViewTroubleshoots')) {

    /**
     * Configuration view for JomSocial
     */
    class CommunityViewTroubleshoots extends JViewLegacy {

        public function display($tpl = null) {
            JToolBarHelper::title(JText::_('COM_COMMUNITY_TROUBLESHOOTS'));

            require_once JPATH_COMPONENT_ADMINISTRATOR . '/libraries/troubleshoots.php';
            $troubleshoots = new CTroubleshoots();
            $this->set('troubleshoots', $troubleshoots);
            $this->set('systemRequirements', $troubleshoots->getSystemRequirements());
            $this->set('plugins', $troubleshoots->getCommunityPlugins());
            parent::display($tpl);
        }

    }

}
