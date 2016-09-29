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

    require_once ( JPATH_ROOT .'/components/com_community/models/models.php');

    // Deprecated since 1.8.x to support older modules / plugins
    //CFactory::load( 'tables', 'like' );

    class CommunityModelLike extends JCCModel
    {

        /**
         * remove like column based on element and uid
         * @param $element
         * @param $uid
         * @return mixed
         */
        public function removeLikes($element, $uid)
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $conditions = array(
                $db->quoteName('element') . ' = ' . $db->quote($element),
                $db->quoteName('uid') . ' = ' . $db->quote($uid)
            );

            $query->delete($db->quoteName('#__community_likes'));
            $query->where($conditions);

            $db->setQuery($query);
            $result = $db->execute();

            return $result;

        }
    }

?>
