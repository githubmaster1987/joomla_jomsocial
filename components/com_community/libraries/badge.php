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

class CBadge {

    private $user;
    private $db;

    /**
     * Constructor
     *
     * @param $user
     * @param int $fakePoints - optional, override use rpoints for debug
     */
    public function __construct($user, $fakePoints=0) {
        $this->user     = $user;

        $this->points   = (int) $this->user->getKarmaPoint();
        if($this->points < 0) $this->points = 0;

        if($fakePoints) $this->points = $fakePoints;

        $this->db       = JFactory::getDBO();
    }

    /**
     * Return a badge
     *
     * @param bool $json
     * @return object / json
     */
    public function getBadge($json = false) {

        jimport('joomla.filesystem.file');

        $badges = new stdClass();
        $badges->current = null;
        $badges->next    = null;

        // Get current badge
        $sql = 'SELECT * FROM #__community_badges ' .
            ' WHERE '.$this->db->quoteName('points').' <= '.$this->points.
            ' AND     '.$this->db->quoteName('published').'= 1 '.
            ' ORDER by '.$this->db->quoteName('points').' DESC LIMIT 1';

        $this->db->setQuery($sql);
        $result=$this->db->loadObjectList();

        if($result) {
            $badges->current = $this->parseResult($result[0]);
        }

        // Get next badge
        $sql = 'SELECT * FROM #__community_badges ' .
            ' WHERE '.$this->db->quoteName('points').' > '.$this->points.
            ' AND     '.$this->db->quoteName('published').'= 1 '.
            ' ORDER by '.$this->db->quoteName('points').' ASC LIMIT 1';

        $this->db->setQuery($sql);
        $result=$this->db->loadObjectList();

        if($result) {
            $badges->next = $this->parseResult($result[0]);
        }

        if($json) return json_encode($badges);
        return $badges;
    }

    private function parseResult($result) {
        $result->image = JUri::root().'components/com_community/assets/badge_'.$result->id.'.'.$result->image;
        if($this->points < $result->points) {
            $result->progress = sprintf('%.2f',$this->points / $result->points);
        }

        return json_decode(json_encode($result), FALSE);
    }
}