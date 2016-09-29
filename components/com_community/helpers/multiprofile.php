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

require_once( JPATH_ROOT.'/components/com_community/helpers/validate.php' );

class CMultiprofileHelper{

    //check if the profile exists and activated
    public static function isActiveProfile($id)
    {
        $db = JFactory::getDbo();
        $query = "SELECT id FROM " . $db->quoteName('#__community_profiles')
            . " WHERE " . $db->quoteName('id') . "=" . $db->quote($id)
            . " AND ".$db->quoteName('published')."=1";
        $db->setQuery($query);
        $result = $db->loadResult();
        return ($result) ? true : false;
    }

}
