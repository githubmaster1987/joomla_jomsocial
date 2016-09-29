<?php
    /**
     * @copyright (C) 2013 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */
    defined('_JEXEC') or die();

    $appName = explode('.', $act->app);
    $appName = $appName[0];

    // Grab primary object to be used in permission checking, defined by appname
    $obj = $act;
    if ($appName == 'groups') {
        $obj = $this->group;
    }

    if ($appName == 'events') {
        $obj = $this->event;
    }

    $my = CFactory::getUser();
    $allowLike = ($my->authorise('community.add', 'activities.like.' . $this->act->actor, $obj));
    $showLocation = !empty($this->act->location);
    // @todo: delete permission shoudl be handled within ACL system
    $allowDelete = (($act->actor == $my->id) || $isCommunityAdmin || ($act->target == $my->id)) && ($my->id != 0);
    // Allow system message deletion only for admin
    if ($act->app == 'users.featured') {
        $allowDelete = $isCommunityAdmin;
    }

    $allowComment = CActivitiesHelper::isActionAllowed($act->app, 'comment');
    $allowComment = $allowComment && ($my->authorise('community.add', 'activities.comment.' . $this->act->actor, $obj));

    if ($act->app == 'groups.discussion.reply' || $act->app == 'groups.discussion' || $act->app == 'groups.bulletin' ||
        $act->app == 'events' || $act->app == 'groups' || strpos($act->app, 'featured') !== false || $act->app=='albums.comment') {
        $allowComment = false;
    }
    // Allow comment for system post
    if ($appName == 'system') {
        $allowComment = !empty($my->id);
    }
    // No like/comment support from the activity stream
    if ($appName == 'photos' || $appName == 'videos') {
        // $allowLike = false;
        // $allowComment = false;
    }


    if ($appName == 'kunena') {
        $allowLike = true;
        $allowComment = false;
    }

    if (!$my->id || $act->app == 'videos.comment') {
        $allowLike = false;
        $allowComment = false;
    }

    //temp fix to pass variables to loaded template
    $this->set('allowLike', $allowLike)
        ->set('allowComment', $allowComment);

    $this->load('stream/actions');
    $this->load('stream/comment');
