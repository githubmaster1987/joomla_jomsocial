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

    require_once JPATH_ROOT . '/components/com_community/libraries/core.php';
    require_once JPATH_ROOT . '/components/com_community/libraries/template.php';

    if (!class_exists('CWall')) {
        class CWall
        {

            static public function _processWallContent($comment)
            {
                // Convert video link to embedded video
                $comment = CVideosHelper::getVideoLink($comment);

                return $comment;
            }

            /**
             * Method to get the walls HTML form
             *
             * @param    userId
             * @param    uniqueId
             * @param    appType
             * @param    $ajaxFunction    Optional ajax function
             * */
            static public function getWallInputForm($uniqueId, $ajaxAddFunction, $ajaxRemoveFunc, $viewAllLink = '')
            {
                $my = CFactory::getUser();

                // Hide the input form completely from visitors
                if ($my->id == 0) {
                    return '';
                }

                $tmpl = new CTemplate();

                return $tmpl->set('uniqueId', $uniqueId)
                    ->set('viewAllLink', $viewAllLink)
                    ->set('ajaxAddFunction', $ajaxAddFunction)
                    ->set('ajaxRemoveFunc', $ajaxRemoveFunc)
                    ->fetch('wall/form');
            }

            /**
             * @param $uniqueId
             * @param $message
             * @param $appType
             * @param $creator
             * @param $isOwner
             * @param string $processFunc
             * @param string $templateFile
             * @param int $wallId
             * @param int $photoId to attach photoid in the wall if exists
             * @return stdClass
             */
            static public function saveWall(
                $uniqueId,
                $message,
                $appType,
                &$creator,
                $isOwner,
                $processFunc = '',
                $templateFile = 'wall/content',
                $wallId = 0,
                $photoId = 0
            ) {
                $my = CFactory::getUser();

                // Add some required parameters, otherwise assert here
                CError::assert($uniqueId, '', '!empty', __FILE__, __LINE__);
                CError::assert($appType, '', '!empty', __FILE__, __LINE__);
                CError::assert($my->id, '', '!empty', __FILE__, __LINE__);

                // Load the models

                $wall = JTable::getInstance('Wall', 'CTable');
                $wall->load($wallId);

                if ($wallId == 0) {
                    // Get current date
                    $now = JDate::getInstance();
                    $now = $now->toSql();

                    // Set the wall properties
                    $wall->type = $appType;
                    $wall->contentid = $uniqueId;
                    $wall->post_by = $creator->id;

                    $wall->date = $now;
                    $wall->published = 1;

                    // @todo: set the ip address
                    $wall->ip = $_SERVER['REMOTE_ADDR'];
                }

                //if photo id is not 0, this wall is appended with a picture
                if ($photoId > 0) {
                    //lets check if the photo belongs to the uploader
                    $photo = JTable::getInstance('Photo', 'CTable');
                    $photo->load($photoId);

                    //save the data into the wall table
                    $wallParam = new CParameter($wall->params);

                    if ($photo->creator == $my->id && $photo->albumid == '-1') {
                        $wallParam->set('attached_photo_id', $photoId);

                        //sets the status to ready so that it wont be deleted on cron run
                        $photo->status = 'ready';
                        $photo->store();
                    }

                    $wall->params = $wallParam->toString();

                } elseif ($photoId == -1) {
                    //if there is nothing, remove the param if applicable
                    $wallParam = new CParameter($wall->params);

                    //delete from db and files
                    $photoModel = CFactory::getModel('photos');
                    if($wallParam->get('attached_photo_id') > 0) {
                        $photoTable = $photoModel->getPhoto($wallParam->get('attached_photo_id'));
                        $photoTable->delete();
                    }

                    $wallParam->set('attached_photo_id', 0);

                    $wall->params = $wallParam->toString();
                }

                /* URL fetch */
                $graphObject = CParsers::linkFetch($message);
                if ($graphObject) {
                    $graphObject->merge(new CParameter($wall->params));
                    $wall->params = $graphObject->toString();
                }

                $wall->comment = $message;

                // Store the wall message
                $wall->store();

                // Convert it to array so that the walls can be processed by plugins
                $args = array();
                $args[0] = $wall;

                //Process wall comments
                $comment = new CComment();
                $wallComments = $wall->comment;
                $wall->comment = $comment->stripCommentData($wall->comment);

                // Trigger the wall comments
                CWall::triggerWallComments($args);

                $wallData = new stdClass();

                $wallData->id = $wall->id;
                $wallData->content = CWallLibrary::_getWallHTML(
                    $wall,
                    $wallComments,
                    $appType,
                    $isOwner,
                    $processFunc,
                    $templateFile
                );


                $wallData->content = CStringHelper::replaceThumbnails($wallData->content);
                CTags::add($wall);
                return $wallData;
            }

            /**
             * @todo Still under working
             * @param type $wall
             */
            public static function addWall($wall)
            {
                $my = CFactory::getUser();
                /**
                 * @todo Field properties will need to check in JTable not here
                 */
                // Add some required parameters, otherwise assert here
                CError::assert($uniqueId, '', '!empty', __FILE__, __LINE__);
                CError::assert($appType, '', '!empty', __FILE__, __LINE__);
                CError::assert($message, '', '!empty', __FILE__, __LINE__);
                CError::assert($my->id, '', '!empty', __FILE__, __LINE__);
            }

            /**
             * @param <type> $act
             */
            static public function getActivityContentHTML($act)
            {

                CFactory::getModel('wall');
                $config = CFactory::getConfig();

                $wall = JTable::getInstance('Wall', 'CTable');
                $wall->load($act->cid);

                $comment = new CComment();
                //$wall->comment = $comment->stripCommentData($wall->comment);
                // Trigger the wall applications / plugins
                $walls = array();
                $walls[] = $wall;
                CWall::triggerWallComments($walls);

                $wall->comment = CWallLibrary::_getWallHTML($wall, null, 'profile', true, null, 'wall/content');

                $tmpl = new CTemplate();
                return $tmpl->set('comment', $wall->comment)
                    ->fetch('activity.wall.post');
            }

            /**
             * Return html-free summary of the wall content
             */
            public static function getWallContentSummary($wallId)
            {

                CFactory::getModel('wall');
                $config = CFactory::getConfig();

                $wall = JTable::getInstance('Wall', 'CTable');
                $wall->load($wallId);

                $comment = new CComment();
                $wall->comment = JHTML::_(
                    'string.truncate',
                    $comment->stripCommentData($wall->comment),
                    $config->getInt('streamcontentlength')
                );

                $tmpl = new CTemplate();
                return $tmpl->set('comment', CStringHelper::escape($wall->comment))
                    ->fetch('activity.wall.post');
            }

            public function canComment($appType, $uniqueId)
            {
                $my = CFactory::getUser();
                $allowed = false;

                switch ($appType) {
                    case 'groups':
                        $group = JTable::getInstance('Group', 'CTable');
                        $group->load($uniqueId);

                        $allowed = $group->isMember($my->id);
                        break;
                    default:
                        $allowed = true;
                        break;
                }
                return $allowed;
            }

            /**
             * Fetches the wall content template and returns the wall data in HTML format
             *
             * @param    appType            The application type to load the walls from
             * @param    uniqueId        The unique id for the specific application
             * @param    isOwner            Boolean value if the current browser is owner of the specific app or profile
             * @param    limit            The limit to display the walls
             * @param    templateFile    The template file to use.
             * */
            static public function getWallContents(
                $appType,
                $uniqueId,
                $isOwner,
                $limit = 0,
                $limitstart = 0,
                $templateFile = 'wall/content',
                $processFunc = '',
                $param = null,
                $banned = 0
            ) {
                CError::assert($appType, '', '!empty', __FILE__, __LINE__);
                //CError::assert($uniqueId, '', '!empty', __FILE__, __LINE__);

                $config = CFactory::getConfig();

                $html = '<div class="joms-comment joms-js--comments joms-js--comments-' . $uniqueId . '" data-id="' . $uniqueId . '" data-type="' . $appType . '">';
                $model = CFactory::getModel('wall');

                if ($limit == 0){
                    $limit = 20000; // let there be no limit at all
                }


                if($appType == 'albums' || $appType == 'photos' || $appType == 'videos'){
                    $order='DESC';
                    $walls = $model->getPost($appType, $uniqueId, $limit, $limitstart, $order);
                    if(count($walls)){
                        $walls = array_reverse($walls);
                    }
                }else{
                    // Special 'discussions'
                    $order='DESC';
                    $walls = $model->getPost($appType, $uniqueId, $limit, $limitstart, $order);
                }

                // Special 'discussions'
                $discussionsTrigger = false;
                //$order = $config->get('group_discuss_order');
                if (($appType == 'discussions') && ($order == 'DESC')) {
                    $walls = array_reverse($walls);
                    $discussionsTrigger = true;
                }

                if ($walls) {
                    //Process wall comments
                    $wallComments = array();
                    $comment = new CComment();

                    for ($i = 0; $i < count($walls); $i++) {
                        // Set comments
                        $wall = $walls[$i];
                        $wallComments[] = $wall->comment;

                        if (CFactory::getUser($wall->post_by)->block) {
                            $wall->comment = JText::_('COM_COMMUNITY_CENSORED');
                            $wall->params = '';
                        } else {
                            $wall->comment = $comment->stripCommentData($wall->comment);
                        }

                        // Change '->created to lapse format if stream uses lapse format'
                        if ($config->get('activitydateformat') == 'lapse') {
                            //$wall->date = CTimeHelper::timeLapse($wall->date);
                        }
                    }

                    // Trigger the wall applications / plugins
                    CWall::triggerWallComments($walls);

                    for ($i = 0; $i < count($walls); $i++) {
                        if ($banned == 1) {
                            $html .= CWallLibrary::_getWallHTML(
                                $walls[$i],
                                $wallComments[$i],
                                $appType,
                                $isOwner,
                                $processFunc,
                                $templateFile,
                                $banned
                            );
                        } else {
                            $html .= CWallLibrary::_getWallHTML(
                                $walls[$i],
                                $wallComments[$i],
                                $appType,
                                $isOwner,
                                $processFunc,
                                $templateFile
                            );
                        }
                    }

                    // if ($appType == 'discussions') {
                    //     $wallCount = CWallLibrary::getWallCount('discussions', $uniqueId);
                    //     $limitStart = $limitstart + $limit;

                    //     if ($wallCount > $limitStart) {
                    //         $groupId = JRequest::getInt('groupid');
                    //         $groupId = empty($groupId) ? $param : $groupId;

                    //         if ($discussionsTrigger) {
                    //             $html = CWallLibrary::_getOlderWallsHTML($groupId, $uniqueId, $limitStart) . $html;
                    //         } else {
                    //             $html .= CWallLibrary::_getOlderWallsHTML($groupId, $uniqueId, $limitStart);
                    //         }
                    //     }
                    // }
                }

                $html .= '</div>';

                return $html;
            }

            static public function _getOlderWallsHTML($groupId, $discussionId, $limitStart)
            {
                $config = CFactory::getConfig();
                $order = $config->get('group_discuss_order');
                $buttonText = '';

                $buttonText = ($order == 'ASC') ? JText::_('COM_COMMUNITY_GROUPS_OLDER_WALL') : JText::_(
                    'COM_COMMUNITY_MORE'
                );

                ob_start();
                ?>
                <div class="joms-newsfeed-more" id="wall-more">
                    <a class="more-wall-text" href="javascript:void(0);"
                       onclick="joms.walls.more();"><?php echo $buttonText; ?></a>

                    <div class="loading"></div>
                </div>
                <input type="hidden" id="wall-groupId" value="<?php echo $groupId; ?>"/>
                <input type="hidden" id="wall-discussionId" value="<?php echo $discussionId; ?>"/>
                <input type="hidden" id="wall-limitStart" value="<?php echo $limitStart; ?>"/>
                <?php
                $moreWalls = ob_get_contents();
                ob_end_clean();

                return $moreWalls;
            }

            static public function _getWallHTML(
                $wall,
                $wallComments,
                $appType,
                $isOwner,
                $processFunc,
                $templateFile,
                $banned = 0
            ) {
                $my = CFactory::getUser();
                $user = CFactory::getUser($wall->post_by);
                $date = CTimeHelper::getDate($wall->date);

                $config = CFactory::getConfig();

                // @rule: for site super administrators we want to allow them to view the remove link
                $isOwner = COwnerHelper::isCommunityAdmin() ? true : $isOwner;
                $isEditable = CWall::isEditable($processFunc, $wall->id);

                $commentsHTML = '';

                $comment = new CComment();
                /*
                 * @todo 3.3 revise what is this code about
                 */
                // If the wall post is a user wall post (in profile pages), we
                // add wall comment feature
                if ($appType == 'user' || $appType == 'groups' || $appType == 'events') {
                    if ($banned == 1) {
                        $commentsHTML = $comment->getHTML($wallComments, 'wall-cmt-' . $wall->id, false);
                    } else {
                        $commentsHTML = $comment->getHTML(
                            $wallComments,
                            'wall-cmt-' . $wall->id,
                            CWall::canComment($wall->type, $wall->contentid)
                        );
                    }
                }

                $avatarHTML = CUserHelper::getThumb($wall->post_by, 'avatar');


                // Change '->created to lapse format if stream uses lapse format'
                if ($config->get('activitydateformat') == 'lapse') {
                    $wall->created = CTimeHelper::timeLapse($date);
                } else {
                    $wall->created = $date->Format(JText::_('DATE_FORMAT_LC2'), true);
                }

                $wallParam = new CParameter($wall->params);
                $photoThumbnail = '';
                $paramsHTML = '';
                $image = (array)$wallParam->get('image');

                if ($wallParam->get('attached_photo_id') > 0) {
                    $photo = JTable::getInstance('Photo', 'CTable');
                    $photo->load($wallParam->get('attached_photo_id'));
                    $photoThumbnail = $photo->getThumbURI();
                } else {
                    if ($wallParam->get('title')) {

                        $video = self::detectVideo($wallParam->get('url'));
                        $url = $wallParam->get('url') ? $wallParam->get('url') : '#';

                        if($config->get('enable_embedly') && $url != '#'){
                            $paramsHTML .="<a href=\"".$url."\" class=\"embedly-card\" data-card-controls=\"0\" data-card-theme=\"".$config->get('enable_embedly_card_template')."\" data-card-align=\"".$config->get('enable_embedly_card_position')."\">".JText::_('COM_COMMUNITY_EMBEDLY_LOADING')."</a>";
                        }elseif (is_object($video)) {

                            $paramsHTML .= '<div class="joms-media--video joms-js--video"';
                            $paramsHTML .= ' data-type="' . $video->type . '"';
                            $paramsHTML .= ' data-id="' . $video->id . '"';
                            $paramsHTML .= ' data-path="' . ( $video->type === 'file' ? JURI::root(true) . '/' : '' ) . $video->path . '"';
                            $paramsHTML .= ' style="margin-top:10px;">';
                            $paramsHTML .= '<div class="joms-media__thumbnail">';
                            $paramsHTML .= '<img src="' . $video->getThumbnail() . '">';
                            $paramsHTML .= '<a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play joms-js--video-play-' . $wall->id . '">';
                            $paramsHTML .= '<div class="mejs-overlay-button"></div>';
                            $paramsHTML .= '</a>';
                            $paramsHTML .= '</div>';
                            $paramsHTML .= '<div class="joms-media__body">';
                            $paramsHTML .= '<h4 class="joms-media__title">' . JHTML::_('string.truncate', $video->title, 50, true, false) . '</h4>';
                            $paramsHTML .= '<p class="joms-media__desc">' . JHTML::_('string.truncate', $video->description, $config->getInt('streamcontentlength'), true, false) . '</p>';
                            $paramsHTML .= '</div>';
                            $paramsHTML .= '</div>';

                        } else {
                            $paramsHTML .= '<div class="joms-gap"></div>';
                            $paramsHTML .= '<div class="joms-media--album joms-js--comment-preview">';
                            if ($isOwner) {
                                $paramsHTML .= '<span data-action="remove-preview" class="joms-fetched-close" style="top:0;right:0;left:auto" onclick="joms.api.commentRemovePreview(\'' . $wall->id . '\');"><i class="joms-icon-remove"></i></span>';
                            }
                            if ($wallParam->get('image')) {
                                $paramsHTML .= '<div class="joms-media__thumbnail">';
                                $paramsHTML .= '<a href="' . $wallParam->get('link') ? $wallParam->get('link') : '#' . '">';
                                $paramsHTML .= '<img src="' . array_shift($image) . '" />';
                                $paramsHTML .= '</a>';
                                $paramsHTML .= '</div>';
                            }
                            $paramsHTML .= '<div class="joms-media__body">';
                            $paramsHTML .= '<h4 class="joms-media__title"><a href="' . $url . '">' . $wallParam->get('title') . '</a></h4>';
                            $paramsHTML .= '<p class="joms-media__desc">' . CStringHelper::trim_words($wallParam->get('description')) . '</p>';

                            if ($wallParam->get('link')) {
                                $paramsHTML .= '<cite>' . preg_replace('#^https?://#', '', $wallParam->get('link')) . '</cite>';
                            }

                            $paramsHTML .= '</div></div>';}
                    }
                }

                $CComment = new CComment();
                $wall->originalComment = $wall->comment;
                $wall->comment = $CComment->stripCommentData($wall->comment);
                $CTemplate = new CTemplate();
                $wall->comment = CStringHelper::autoLink($wall->comment);

                $wall->comment = nl2br($wall->comment);
                $wall->comment = CUserHelper::replaceAliasURL($wall->comment);
                $wall->comment = CStringHelper::getEmoticon($wall->comment);
                $wall->comment = CStringHelper::converttagtolink($wall->comment); // convert to hashtag

                $canDelete = $my->authorise('community.delete','walls', $wall);

                $like = new CLike();
                $likeCount = $like->getLikeCount('comment', $wall->id);
                $isLiked = $like->userLiked('comment', $wall->id, $my->id) == COMMUNITY_LIKE;

                // Create new instance of the template
                $tmpl = new CTemplate();
                return $tmpl->set('id', $wall->id)
                    ->set('author', $user->getDisplayName())
                    ->set('avatarHTML', $avatarHTML)
                    ->set('authorLink', CUrlHelper::userLink($user->id))
                    ->set('created', $wall->created)
                    ->set('content', $wall->comment)
                    ->set('commentsHTML', $commentsHTML)
                    ->set('avatar', $user->getThumbAvatar())
                    ->set('isMine', $isOwner)
                    ->set('canDelete', $canDelete)
                    ->set('isEditable', $isEditable)
                    ->set('processFunc', $processFunc)
                    ->set('config', $config)
                    ->set('photoThumbnail', $photoThumbnail)
                    ->set('paramsHTML', $paramsHTML)
                    ->set('wall', $wall)
                    ->set('likeCount', $likeCount)
                    ->set('isLiked', $isLiked)
                    ->fetch($templateFile);
            }

            static public function getViewAllLinkHTML($link, $count = null)
            {
                if (!$link) {
                    return '';
                }

                $config = CFactory::getConfig();
                $tmpl = new CTemplate();

                return $tmpl->set('viewAllLink', $link)
                    ->set('count', $count)
                    ->set('commentDiff', $count - $config->get('stream_default_comments'))
                    ->fetch('wall/misc');
            }

            static public function getWallCount($appType, $uniqueId)
            {
                $model = CFactory::getModel('wall');
                $count = $model->getCount($uniqueId, $appType);
                return $count;
            }

            /**
             * @todo: change this to a simple $my->authorise
             * @param type $processFunc
             * @param type $wallId
             * @return type
             */
            static public function isEditable($processFunc, $wallId)
            {
                $func = explode(',', $processFunc);

                if (count($func) < 2) {
                    return false;
                }

                $controller = $func[0];
                $method = 'edit' . $func[1] . 'Wall';

                if (count($func) > 2) {
                    //@todo: plugins
                }

                return CWall::_callFunction($controller, $method, array($wallId));
            }

            public function _checkWallFunc($processFunc)
            {

            }

            static public function _callFunction($controller, $method, $arguments)
            {
                require_once(JPATH_ROOT . '/components/com_community/controllers/controller.php');
                require_once(JPATH_ROOT . '/components/com_community/controllers' . '/' . JString::strtolower(
                        $controller
                    ) . '.php');

                $controller = JString::ucfirst($controller);
                $controller = 'Community' . $controller . 'Controller';
                $controller = new $controller();

                // @rule: If method not exists, we need to do some assertion here.
                if (!method_exists($controller, $method)) {
                    JFactory::getApplication()->enqueueMessage(JText::_('Method not found'), 'error');
                }

                return call_user_func_array(array($controller, $method), $arguments);
            }

            public function addWallComment($type, $cid, $comment)
            {
                $my = CFactory::getUser();
                $table = JTable::getInstance('CTable', 'Wall');

                $table->contentid = $cid;
                $table->type = $type;
                $table->comment = $comment;
                $table->post_by = $my->id;

                $table->store();
                return $table->id;
            }

            /**
             * Formats the comment in the rows
             *
             * @param Array    An array of wall objects
             * */
            static public function triggerWallComments(&$rows, $newlineReplace = true)
            {
                CError::assert($rows, 'array', 'istype', __FILE__, __LINE__);

                require_once(COMMUNITY_COM_PATH . '/libraries/apps.php');
                $appsLib = CAppPlugins::getInstance();
                $appsLib->loadApplications();

                for ($i = 0; $i < count($rows); $i++) {
                    if (isset($rows[$i]->comment) && (!empty($rows[$i]->comment))) {
                        $args = array();
                        if(!$newlineReplace){
                            // if newline replace is false, pass the information to the comment to leave out the newline
                            // replace in wall.trigger
                            $rows[$i]->newlineReplace = false;
                        }
                        $args[] = $rows[$i];

                        $appsLib->triggerEvent('onWallDisplay', $args);
                    }
                }
                return true;
            }

            /**
             * Return formatted comment given the wall item
             */
            public static function formatComment($wall)
            {
                $config = CFactory::getConfig();
                $my = CFactory::getUser();
                $actModel = CFactory::getModel('activities');
                $like = new CLike();

                $likeCount = $like->getLikeCount('comment', $wall->id);
                $isLiked = $like->userLiked('comment', $wall->id, $my->id);

                $user = CFactory::getUser($wall->post_by);

                // Censor if the user is banned
                if ($user->block) {
                    $wall->comment = $origComment = JText::_('COM_COMMUNITY_CENSORED');
                    $wall->params = new CParameter(); // strip all the params
                } else {
                    // strip out the comment data
                    $CComment = new CComment();
                    $wall->comment = $CComment->stripCommentData($wall->comment);

                    // Need to perform basic formatting here
                    // 1. support nl to br,
                    // 2. auto-link text
                    $CTemplate = new CTemplate();
                    $wall->comment = $origComment = $CTemplate->escape($wall->comment);
                    $wall->comment = CStringHelper::autoLink($wall->comment);
                }

                $commentsHTML = '';
                $commentsHTML .= '<div class="cComment wall-coc-item" id="wall-' . $wall->id . '"><a href="' . CUrlHelper::userLink(
                        $user->id
                    ) . '"><img src="' . $user->getThumbAvatar() . '" alt="avatar" class="wall-coc-avatar" /></a>';
                $date = new JDate($wall->date);
                $commentsHTML .= '<a class="wall-coc-author" href="' . CUrlHelper::userLink(
                        $user->id
                    ) . '">' . $user->getDisplayName() . '</a> ';
                $commentsHTML .= $wall->comment;
                $commentsHTML .= '<span class="wall-coc-time">' . CTimeHelper::timeLapse($date);

                $cid = isset($wall->contentid) ? $wall->contentid : null;

                $activity = $actModel->getActivity($cid);

                $ownPost = ($my->id == $wall->post_by);
                $allowRemove = $my->authorise('community.delete','walls', $wall);

                $canEdit = ($config->get('wallediting') && $my->id == $wall->post_by) || COwnerHelper::isCommunityAdmin(); // only poster can edit

                if ($allowRemove) {
                    $commentsHTML .= ' <span class="wall-coc-remove-link">&#x2022; <a href="#removeComment">' . JText::_(
                            'COM_COMMUNITY_WALL_REMOVE'
                        ) . '</a></span>';
                }

                $commentsHTML .= '</span>';
                $commentsHTML .= '</div>';

                $editHTML = '';
                if ($config->get('wallediting') && $ownPost || COwnerHelper::isCommunityAdmin()) {
                    $editHTML .= '<a href="javascript:" class="joms-button--edit">';
                    $editHTML .= '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="' . CRoute::getURI() . '#joms-icon-pencil"></use></svg>';
                    $editHTML .= '<span>' . JText::_('COM_COMMUNITY_EDIT') . '</span>';
                    $editHTML .= '</a>';
                }

                $removeHTML = '';
                if ($allowRemove) {
                    $removeHTML .= '<a href="javascript:" class="joms-button--remove">';
                    $removeHTML .= '<svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="' . CRoute::getURI() . '#joms-icon-remove"></use></svg>';
                    $removeHTML .= '<span>' . JText::_('COM_COMMUNITY_WALL_REMOVE') . '</span>';
                    $removeHTML .= '</a>';
                }

                $removeTagHTML = '';

                if (CActivitiesHelper::hasTag($my->id, $wall->comment)) {
                    $removeTagHTML = '<span><a data-action="remove-tag" data-id="' . $wall->id . '" href="javascript:">' . JText::_(
                            'COM_COMMUNITY_WALL_REMOVE_TAG'
                        ) . '</a></span>';
                }

                /* user deleted */
                if ($user->guest == 1) {
                    $userLink = '<span class="cStream-Author">' . $user->getDisplayName() . '</span> ';
                } else {
                    $userLink = '<a class="cStream-Avatar cStream-Author cFloat-L" href="' . CUrlHelper::userLink(
                            $user->id
                        ) . '"> <img class="cAvatar" src="' . $user->getThumbAvatar() . '"> </a> ';
                }

                $params = $wall->params;
                $paramsHTML = '';
                $image = (array)$params->get('image');

                $photoThumbnail = false;

                if ($params->get('attached_photo_id') > 0) {
                    $photo = JTable::getInstance('Photo', 'CTable');
                    $photo->load($params->get('attached_photo_id'));
                    $photoThumbnail = $photo->getThumbURI();
                    $paramsHTML .= '<div style="padding: 5px 0"><img class="joms-stream-thumb" src="' . $photoThumbnail . '" /></div>';
                } else {
                    if ($params->get('title')) {

                        $video = self::detectVideo($params->get('url'));
                        $url = $params->get('url') ? $params->get('url') : '#';

                        if($config->get('enable_embedly') && $url != '#'){
                            $paramsHTML .="<a href=\"".$url."\" class=\"embedly-card\" data-card-controls=\"0\" data-card-theme=\"".$config->get('enable_embedly_card_template')."\" data-card-align=\"".$config->get('enable_embedly_card_position')."\">".JText::_('COM_COMMUNITY_EMBEDLY_LOADING')."</a>";
                        }elseif (is_object($video)) {

                            $paramsHTML .= '<div class="joms-media--video joms-js--video"';
                            $paramsHTML .= ' data-type="' . $video->type . '"';
                            $paramsHTML .= ' data-id="' . $video->id . '"';
                            $paramsHTML .= ' data-path="' . ( $video->type === 'file' ? JURI::root(true) . '/' : '' ) . $video->path . '"';
                            $paramsHTML .= ' style="margin-top:10px;">';
                            $paramsHTML .= '<div class="joms-media__thumbnail">';
                            $paramsHTML .= '<img src="' . $video->getThumbnail() . '">';
                            $paramsHTML .= '<a href="javascript:" class="mejs-overlay mejs-layer mejs-overlay-play joms-js--video-play joms-js--video-play-' . $wall->id . '">';
                            $paramsHTML .= '<div class="mejs-overlay-button"></div>';
                            $paramsHTML .= '</a>';
                            $paramsHTML .= '</div>';
                            $paramsHTML .= '<div class="joms-media__body">';
                            $paramsHTML .= '<h4 class="joms-media__title">' . JHTML::_('string.truncate', $video->title, 50, true, false) . '</h4>';
                            $paramsHTML .= '<p class="joms-media__desc">' . JHTML::_('string.truncate', $video->description, $config->getInt('streamcontentlength'), true, false) . '</p>';
                            $paramsHTML .= '</div>';
                            $paramsHTML .= '</div>';

                        } else {
                            $paramsHTML .= '<div class="joms-gap"></div>';
                            $paramsHTML .= '<div class="joms-media--album joms-relative joms-js--comment-preview">';
                            if ($user->id == $my->id || COwnerHelper::isCommunityAdmin()) {
                                $paramsHTML .= '<span class="joms-media__remove" data-action="remove-preview" onClick="joms.api.commentRemovePreview(\'' . $wall->id . '\');"><svg viewBox="0 0 16 16" class="joms-icon"><use xlink:href="#joms-icon-remove"></use></svg></span>';
                            }

                            if ($params->get('image')) {

                                $paramsHTML .= $params->get('link');

                                $paramsHTML .= '<div class="joms-media__thumbnail">';
                                $paramsHTML .= '<a href="' . $params->get('link') ? $params->get('link') : '#' . '">';
                                $paramsHTML .= '<img src="' . array_shift($image) . '" />';
                                $paramsHTML .= '</a>';
                                $paramsHTML .= '</div>';
                            }


                            if($config->get('enable_embedly') && $url != '#'){
                                $paramsHTML .="<a href=\"".$url."\" class=\"embedly-card\" data-card-controls=\"0\" data-card-theme=\"".$config->get('enable_embedly_card_template')."\" data-card-align=\"".$config->get('enable_embedly_card_position')."\">".JText::_('COM_COMMUNITY_EMBEDLY_LOADING')."</a>";
                            }else {
                                $paramsHTML .= '<div class="joms-media__body">';
                                $paramsHTML .= '<a href="' . $url . '">';
                                $paramsHTML .= '<h4 class="joms-media__title">' . $params->get('title') . '</h4>';
                                $paramsHTML .= '<p class="joms-media__desc">' . CStringHelper::trim_words($params->get('description')) . '</p>';

                                if ($params->get('link')) {
                                    $paramsHTML .= '<span class="joms-text--light"><small>' . preg_replace('#^https?://#',
                                            '', $params->get('link')) . '</small></span>';
                                }
                                $paramsHTML .= '</a></div>';
                            }
                            $paramsHTML .= '</div>';
                        }
                    }
                }

                if (!$params->get('title') && $params->get('url')) {
                    $paramsHTML .= '<div class="joms-gap"></div>';
                    $paramsHTML .= '<div class="joms-media--album">';
                    $paramsHTML .= '<a href="' . $params->get('url') . '">';
                    $paramsHTML .= '<img class="joms-stream-thumb" src="' . $params->get('url') . '" />';
                    $paramsHTML .= '</a>';
                    $paramsHTML .= '</div>';
                }

                $wall->comment = nl2br($wall->comment);
                $wall->comment = CUserHelper::replaceAliasURL($wall->comment);
                $wall->comment = CStringHelper::getEmoticon($wall->comment);
                $wall->comment = CStringHelper::converttagtolink($wall->comment); // convert to hashtag

                $template = new CTemplate();
                $template
                    ->set('wall', $wall)
                    ->set('originalComment', $origComment)
                    ->set('date', $date)
                    ->set('isLiked', $isLiked)
                    ->set('likeCount', $likeCount)
                    ->set('canRemove',$allowRemove)
                    ->set('canEdit', $canEdit)
                    ->set('canRemove', $allowRemove)
                    ->set('user', $user)
                    ->set('photoThumbnail', $photoThumbnail)
                    ->set('paramsHTML', $paramsHTML);

                $commentsHTML = $template->fetch('stream/single-comment');

                return $commentsHTML;
            }
            private static function detectVideo($url)
            {
                if(!strlen($url)) return 0;

                $video = JTable::getInstance('Video', 'CTable');
                if(!$video->init($url))     return 1;

                return $video;
            }

            public static function getWallUser($contentid, $type = '')
            {
                $wallModel = CFactory::getModel('wall');
                return $wallModel->getPostUserslist($contentid, $type);
            }

        }
    }

    /**
     * Maintain classname compatibility with JomSocial 1.6 below
     */
    class CWallLibrary extends CWall
    {

    }
