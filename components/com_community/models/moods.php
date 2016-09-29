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

class CommunityModelMoods extends JCCModel {

    private $moods = array();
    public $enabled = true;

    /**
     * Load up all published moods on startup
     */
    public function __construct() {

        $this->enabled = CFactory::getConfig()->get("enablemood");

        $db = JFactory::getDBO();
        $sql = 'SELECT * FROM ' . $db->quoteName('#__community_moods') . ' ORDER BY ' . $db->quoteName('ordering') . ' ASC';
        $db->setQuery($sql);

        $result = $db->loadObjectList();

        // build and pre-parse assoc result array
        foreach($result as $mood)
        {
            // legacy - predefined (non-custom) moods use untraslated mood strings as identifiers
            if(!$mood->custom) {
                $mood->id = $mood->title;
                $mood->title = JText::_('COM_COMMUNITY_MOOD_SHORT_' . strtoupper($mood->title));
            }

            // apply description translations for frontend
            $mood->description = JText::_($mood->description);


            if($mood->custom) {
                $mood->title = JText::_($mood->title);
                $filename = "mood_".$mood->id.".".$mood->image;

                if(file_exists(COMMUNITY_PATH_ASSETS.$filename))
                {
                    $mood->image = JUri::root().str_replace(JPATH_ROOT,'',COMMUNITY_PATH_ASSETS).$filename;
                } else {
                    $mood->image ='';
                }
            }

            $this->moods[$mood->id] = $mood;
        }

        unset($result);
    }

    /**
     * Return all moods
     *
     * @return Array
     */
    public function getMoods() {
        return $this->moods;
    }

    /**
     * Load a single mood
     *
     * @param string|int $moodId
     *
     * @return Object
     */
    public function getMood($moodId) {

        $moodId = strtolower($moodId);

        if(array_key_exists($moodId, $this->moods)) return $this->moods[$moodId];

        return false;
    }

    /**
     * Build a HTML snippet used when rendering the stream items
     *
     * @param string|int $moodId
     *
     * @return string
     */
    public function getMoodString($moodId) {

        $mood = $this->getMood($moodId);

        if (!$mood || is_null($moodId) || $moodId == 'no mood' || $moodId == 'Mood') {
            return "";
        }

        // @todo do we really need HTML generation here? use view helper?
        // @todo SCSS
        $moodstr = '<i class="joms-emoticon joms-emo-'.$mood->id.'"';

        if($mood->image) $moodstr.=' style="background:url('.$mood->image.');background-size:contain;"';

        $moodstr.='></i> <b>'.$mood->description.'</b>';

        return $moodstr;
    }
}
