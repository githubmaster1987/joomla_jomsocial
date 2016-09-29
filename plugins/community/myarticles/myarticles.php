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
require_once( JPATH_ROOT . '/components/com_content/helpers/route.php');
require_once( JPATH_ROOT . '/components/com_community/libraries/core.php');

if (!class_exists('plgCommunityMyArticles')) {

    class plgCommunityMyArticles extends CApplications {

        var $name = "My Articles";
        var $section;
        var $_name = "myarticles";

        function __construct($subject, $config) {
            parent::__construct($subject, $config);

            $this->section = trim($this->params->get('section'), ',');
            $this->db = JFactory::getDbo();
        }

        /**
         * Ajax function to save a new wall entry
         *
         * @param message	A message that is submitted by the user
         * @param uniqueId	The unique id for this group
         *
         * */
        function onProfileDisplay() {
            //Load language file.
            JPlugin::loadLanguage('plg_community_myarticles', JPATH_ADMINISTRATOR);
            $jinput = JFactory::getApplication()->input;

            // Attach CSS
            $document = JFactory::getDocument();
            $css = JURI::base() . 'plugins/community/myarticles/myarticles/style.css';
            $document->addStyleSheet($css);

            if ($jinput->request->get('task') == 'app') {
                $app = 1;
            } else {
                $app = 0;
            }

            $user = CFactory::getRequestUser();
            $userid = $user->id;

            $def_limit = $this->params->get('count', 10);


            //in app view, we will have to show all articles
            if($app){
                $def_limit = 99999;
            }

            $limit = $jinput->get('limit', $def_limit);
            $limitstart = $jinput->get('limitstart', 0);

            $row = $this->getArticle($userid, $limitstart, $limit, $this->section);

            $cat = $this->getCatAlias();
            $total = $this->countArticle($userid, $this->section);

            if($this->params->get('hide_empty', 0) && !$total) return '';

            $mainframe = JFactory::getApplication();
            $caching = $this->params->get('cache', 1);

            if ($caching) {
                $caching = $mainframe->get('caching');
            }

            $cache = JFactory::getCache('plgCommunityMyArticles');
            $cache->setCaching($caching);

            $callback = array('plgCommunityMyArticles', '_getArticleHTML');
            $content = $cache->call($callback, $userid, $limit, $limitstart, $row, $app, $total, $cat, $this->params);

            return $content;
        }

        static public function _getArticleHTML($userid, $limit, $limitstart, $row, $app, $total, $cat, $params) {

            JPluginHelper::importPlugin('content');
            $dispatcher = JDispatcher::getInstance();
            $html = "";

            if (!empty($row)) {
                $html .= '<div class="joms-app--myarticle">';
                $html .= '<ul class="joms-list--articles">';
                foreach ($row as $data) {
                    $text_limit = $params->get('limit', 50);
                    $result = $dispatcher->trigger('onPrepareContent', array(& $data, & $params, 0));

                    if (empty($cat[$data->catid])) {
                        $cat[$data->catid] = "";
                    }

                    $data->sectionid = (empty($data->sectionid)) ? 0 : $data->sectionid;
                    $link = plgCommunityMyArticles::buildLink($data->id, $data->alias, $data->catid, $cat[$data->catid], $data->sectionid);

                    $created = new JDate($data->created);
                    $date = CTimeHelper::timeLapse($created);


                    $html .= '	<li>';
                    $html .= '		<a href="' . $link . '">' . htmlspecialchars($data->title) . '</a>';
                    $html .= '<span class="joms-block joms-text--small joms-text--light">' . $date . '</span>';
                    $html .= '	</li>';
                }
                $html .= '</ul>';

                $showall = CRoute::_('index.php?option=com_community&view=profile&userid=' . $userid . '&task=app&app=myarticles');
                $html .= "<div class='joms-list--articles__footer'><small><a class='joms-button--link' href='" . $showall . "'>" . JText::_('PLG_MYARTICLES_SHOWALL') . "</a></small></div>";

                $html .= '</div>';
            } else {
                $html .= "<div class='joms-app--myarticle'><p>" . JText::_("PLG_MYARTICLES_NO_ARTICLES") . "</p></div>";

            }
            return $html;
        }

        function onAppDisplay() {
            ob_start();
            $limit = 0;
            $html = $this->onProfileDisplay($limit);
            echo $html;

            $content = ob_get_contents();
            ob_end_clean();

            return $content;
        }

        static public function buildLink($id, $alias, $catid, $catAlias, $sectionid) {
            $link = ContentHelperRoute::getArticleRoute($id . ':' . $alias, $catid . ':' . $catAlias, $sectionid);
            $link = JRoute::_($link);

            return $link;
        }

        function getArticle($userid, $limitstart, $limit, $section) {
            $condition = "";

            if ($this->params->get('display_expired', 1)) {
                $expired = "";
            } else {
                $expired = $this->getExpiredCondition();
            }

            //we need to get the article by current language
            $lang = JFactory::getLanguage();
            $currentLanguage = $lang->getTag();

            $condition .= " AND (".$this->db->quoteName('language')."=".$this->db->quote($currentLanguage)
                       ." OR ".$this->db->quoteName('language')."=".$this->db->quote('*').") ";

            $sql = "	SELECT * FROM " . $this->db->quoteName('#__content') . "
						WHERE
								" . $this->db->quoteName('created_by') . " = " . $this->db->quote($userid) . " AND
								" . $this->db->quoteName('state') . "=" . $this->db->quote(1) . "
								" . $condition . "
								" . $expired . "
						ORDER BY
								" . $this->db->quoteName('created') . " DESC
						LIMIT
								" . $limitstart . "," . $limit;

            $this->db->setQuery($sql);
            $row = $this->db->loadObjectList();
            return $row;
        }

        function countArticle($userid, $section) {
            $condition = "";
            $sql = "	SELECT
								count(id) as total
						FROM
								" . $this->db->quoteName('#__content') . "
						WHERE
								" . $this->db->quoteName('created_by') . " = " . $this->db->quote($userid) . " AND
								" . $this->db->quoteName('state') . "=" . $this->db->quote(1) . "
								" . $condition;
            $this->db->setQuery($sql);
            $count = $this->db->loadObject();
            return $count->total;
        }

        function getCatAlias() {
            $cat = array();

            $sql = "	SELECT
								" . $this->db->quoteName("id") . ",
								" . $this->db->quoteName("alias") . "
						FROM
								" . $this->db->quoteName("#__categories");

            $this->db->setQuery($sql);
            $row = $this->db->loadObjectList();

            foreach ($row as $data) {
                $cat[$data->id] = $data->alias;
            }

            return $cat;
        }

        /**
         *
         * @return string
         */
        public function getExpiredCondition() {
            $date = new JDate();
            $now = $date->toSql();

            $condition = " AND ( " . " "
                    . "( "
                    . $this->db->quoteName('publish_up') . " <= " . $this->db->quote($now) . " AND "
                    . $this->db->quoteName('publish_down') . " >= " . $this->db->quote($now) . " "
                    . ") OR "
                    . $this->db->quoteName('publish_down') . " = " . $this->db->quote("0000-00-00 00:00:00") . " "
                    . " ) ";

            return $condition;
        }

    }

}
