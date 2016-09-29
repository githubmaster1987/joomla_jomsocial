<?php
/**
 * @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
 * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author iJoomla.com <webmaster@ijoomla.com>
 * @url https://www.jomsocial.com/license-agreement
 * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
 * More info at https://www.jomsocial.com/license-agreement
 */
defined('_JEXEC') or die('Restricted access');

class CTableEventStats extends JTable
{
    var $id = null;
    var $eid = null; //event id
    var $type = null;
    var $count = null;
    var $params = null; // for future use
    var $date = null;
    var $created_at = null;

    public function __construct(&$db)
    {
        parent::__construct('#__community_event_stats', 'id', $db);
    }

    public function store($updateNulls = false){
        //quick validation
        if(!$this->eid || !$this->type){
            return;
        }

        parent::store();
    }
}
