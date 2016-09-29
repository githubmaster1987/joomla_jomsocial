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

jimport('joomla.application.component.controller');

if (!class_exists('CommunityControllerTroubleshoots')) {

    /**
     * JomSocial Component Controller
     */
    class CommunityControllerTroubleshoots extends CommunityController
    {

        public function ajaxCleanStream()
        {
            $response	= new JAXResponse();
            $streamTable = JTable::getInstance('activity', 'cTable');
            $db = JFactory::getDbo();
            $query = "SELECT id, actor, target FROM " . $db->quoteName('#__community_activities');
            $results = $db->setQuery($query)->loadObjectList();
            $totalStreamRemoved = 0;
            if($results) {
                $activity = JTable::getInstance('activity', 'CTable');

                foreach ($results as $result) {
                    // we will go through both actor and target if specified
                    if ($result->actor) { // make sure the actor isn't 0
                        $user = JFactory::getUser($result->actor);
                        if (!$user->id) {
                            $activity->load($result->id);
                            $totalStreamRemoved++;
                            $activity->delete();
                            continue; //doesn't have to check for target since its already removed
                        }
                    }

                    if ($result->target) { // make sure the actor isn't 0
                        $user = JFactory::getUser($result->actor);
                        if (!$user->id) {
                            $activity->load($result->id);
                            $totalStreamRemoved++;
                            $activity->delete();
                        }
                    }
                }
            }

            $response->addScriptCall(
                "
                $('#clean_stream').show();
                $('p.clean_stream').html('".JText::sprintf('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP_STREAM_SUCCESS',$totalStreamRemoved)."');
                "
            );
            return $response->sendResponse();
        }

        /**
         * Clean up images from avatar that belongs to nobody,
         * which happens when someone deleted the item but never gets deleted in file
         *
         */
        public function ajaxCleanLocalOrphanedAvatar()
        {
            $response = new JAXResponse();
            $config = CFactory::getConfig();
            $imageFormat = array('jpg', 'jpeg', 'png', 'gif'); //to only accept this format
            $avatarFolderPath = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/avatar/';
            $avatarFilesList = array(); //collection of all the avatar files path that can be compared to the photo table

            try {
                $di = new RecursiveDirectoryIterator($avatarFolderPath);
                //loop through all the folders and get all the files inside.
                foreach (new RecursiveIteratorIterator($di) as $filePath => $file) {
                    $filename = basename($filePath);
                    $fileExt = explode('.', $filename);
                    $fileNameWithoutExt = $fileExt[0];
                    $fileExt = $fileExt[count($fileExt) - 1];
                    if (in_array($fileExt, $imageFormat)) {


                        if (strpos($filename, '_stream_') !== false) {
                            $checkfile = explode('_stream_', $filename);
                            $checkfile = $checkfile[0];
                        } elseif (strpos($filename, 'original_') !== false) {
                            $checkfile = explode('original_', $filename);
                            $checkfile = $checkfile[1];
                        } elseif (strpos($filename, 'profile-') !== false) {
                            $checkfile = explode('profile-', $filename);
                            $checkfile = $checkfile[1];
                        } elseif (strpos($filename, 'thumb_') !== false) {
                            $checkfile = explode('thumb_', $filename);
                            $checkfile = $checkfile[1];
                        } elseif (strpos($filename, 'group-') !== false) {
                            $checkfile = explode('group-', $filename);
                            $checkfile = $checkfile[1];
                        } else {
                            $checkfile = $filename;
                        }

                        $relativePath = str_replace(JPATH_ROOT . '/', '', $filePath); // this will get the relative path
                        $checkfile = str_replace($filename, $checkfile,
                            $relativePath); // this will get the final results of the file path we can use to check


                        $avatarFilesList[] = array(
                            'absolute_path' => $filePath,
                            'filename' => $filename,
                            'checkFile' => $checkfile //this format is used to search within the tables
                        );
                    }
                }

                $db = JFactory::getDbo();
                //since we have all the list from the images in folder, grab all the avatar from db
                $query = "SELECT image FROM " . $db->quoteName('#__community_photos') . " WHERE " .
                    $db->quoteName('image') . " LIKE " . $db->quote("%avatar%");

                $db->setQuery($query);
                $photoTableResults = $db->loadColumn();

                //grab from user table
                $query = "SELECT avatar FROM " . $db->quoteName('#__community_users') . " WHERE " .
                    $db->quoteName('avatar') . " LIKE " . $db->quote("%avatar%");

                $db->setQuery($query);
                $userTableResults = $db->loadColumn();

                //grab from group table
                $query = "SELECT avatar FROM " . $db->quoteName('#__community_groups') . " WHERE " .
                    $db->quoteName('avatar') . " LIKE " . $db->quote("%avatar%");

                $db->setQuery($query);
                $groupTableResults = $db->loadColumn();

                $allTableResults = array_merge($photoTableResults, $userTableResults, $groupTableResults);

                $totalAvatarCleaned = 0;
                foreach ($avatarFilesList as $avatar) {
                    if (!in_array($avatar['checkFile'], $allTableResults)) {
                        //if the file is not in the records, delete it
                        if (file_exists($avatar['absolute_path'])) {
                            $totalAvatarCleaned++;
                            //delete this file
                            unlink($avatar['absolute_path']);
                        }
                    }
                }

                $response->addScriptCall(
                    "
                $('#clean_avatar_local').show();
                $('p.clean_avatar_local').html('" . JText::sprintf('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP_AVATAR_SUCCESS',
                        $totalAvatarCleaned) . "');
                "
                );
            } catch (Exception $e) {
                $response->addScriptCall(
                    "
                $('#clean_avatar_local').show();
                $('p.clean_avatar_local').html('" . $e . "');
                "
                );
            }

            return $response->sendResponse();
        }

        public function ajaxCleanS3OrphanedAvatar()
        {
            $response = new JAXResponse();
            $s3Storage = CStorage::getStorage('s3');
            try {
                $files = $s3Storage->getFileList('images/avatar/');//this will get all the files within the folder
            }catch(Exception $e){
                $response->addScriptCall(
                    "
                $('#clean_avatar_s3').show();
                $('p.clean_avatar_s3').html('".JText::_('S3 did not setup properly')."');
                "
                );

                return $response->sendResponse();
            }

            $imageFormat = array('jpg', 'jpeg', 'png', 'gif'); //to only accept this format

            foreach ($files as $filePath => $fileInfo) {
                $filename = basename($filePath);
                $fileExt = explode('.', $filename);
                $fileExt = $fileExt[count($fileExt) - 1];
                if (in_array($fileExt, $imageFormat)) {


                    if (strpos($filename, '_stream_') !== false) {
                        $checkfile = explode('_stream_', $filename);
                        $checkfile = $checkfile[0];
                    } elseif (strpos($filename, 'original_') !== false) {
                        $checkfile = explode('original_', $filename);
                        $checkfile = $checkfile[1];
                    } elseif (strpos($filename, 'profile-') !== false) {
                        $checkfile = explode('profile-', $filename);
                        $checkfile = $checkfile[1];
                    } elseif (strpos($filename, 'thumb_') !== false) {
                        $checkfile = explode('thumb_', $filename);
                        $checkfile = $checkfile[1];
                    } elseif (strpos($filename, 'group-') !== false) {
                        $checkfile = explode('group-', $filename);
                        $checkfile = $checkfile[1];
                    } else {
                        $checkfile = $filename;
                    }

                    $relativePath = str_replace(JPATH_ROOT . '/', '', $filePath); // this will get the relative path
                    $checkfile = str_replace($filename, $checkfile,
                        $relativePath); // this will get the final results of the file path we can use to check


                    $avatarFilesList[] = array(
                        'absolute_path' => $filePath,
                        'filename' => $filename,
                        'checkFile' => $checkfile //this format is used to search within the tables
                    );
                }
            }

            $db = JFactory::getDbo();
            //since we have all the list from the images in folder, grab all the avatar from db
            $query = "SELECT image FROM " . $db->quoteName('#__community_photos') . " WHERE " .
                $db->quoteName('image') . " LIKE " . $db->quote("%avatar%");

            $db->setQuery($query);
            $photoTableResults = $db->loadColumn();

            //grab from user table
            $query = "SELECT avatar FROM " . $db->quoteName('#__community_users') . " WHERE " .
                $db->quoteName('avatar') . " LIKE " . $db->quote("%avatar%");

            $db->setQuery($query);
            $userTableResults = $db->loadColumn();

            //grab from group table
            $query = "SELECT avatar FROM " . $db->quoteName('#__community_groups') . " WHERE " .
                $db->quoteName('avatar') . " LIKE " . $db->quote("%avatar%");

            $db->setQuery($query);
            $groupTableResults = $db->loadColumn();

            $allTableResults = array_merge($photoTableResults, $userTableResults, $groupTableResults);

            $totalAvatarCleaned = 0;
            foreach ($avatarFilesList as $avatar) {
                if (!in_array($avatar['checkFile'], $allTableResults)) {
                    //if the file is not in the records, delete it
                    $totalAvatarCleaned++;
                    //delete this file
                    $s3Storage->delete($avatar['absolute_path']);

                    //remove from the s3 table if there's any
                    $query = "DELETE FROM ".$db->quoteName('#__community_storage_s3')." WHERE ".$db->quoteName('storageid')."=".$db->quote($avatar['absolute_path']);
                    $db->setQuery($query);
                    $db->execute();
                }
            }

            $response->addScriptCall(
                "
                $('#clean_avatar_s3').show();
                $('p.clean_avatar_s3').html('".JText::sprintf('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP_AVATAR_SUCCESS',$totalAvatarCleaned)."');
                "
            );

            return $response->sendResponse();
        }

        /**
         * Clean up images from cover that belongs to nobody,
         * which happens when someone deleted the item but never gets deleted in file
         *
         */
        public function ajaxCleanLocalOrphanedCover()
        {
            $response = new JAXResponse();
            $config = CFactory::getConfig();
            $imageFormat = array('jpg', 'jpeg', 'png', 'gif'); //to only accept this format
            $avatarFolderPath = JPATH_ROOT . '/' . $config->getString('imagefolder') . '/cover/';
            $coverFilesList = array(); //collection of all the cover files path that can be compared to the photo table

            try {
                $di = new RecursiveDirectoryIterator($avatarFolderPath);
                //loop through all the folders and get all the files inside.
                foreach (new RecursiveIteratorIterator($di) as $filePath => $file) {
                    $filename = basename($filePath);
                    $fileExt = explode('.', $filename);
                    $fileNameWithoutExt = $fileExt[0];
                    $fileExt = $fileExt[count($fileExt) - 1];
                    if (in_array($fileExt, $imageFormat)) {

                        //its either thumb or the original name (covers only produce 2 copies)
                        if (strpos($filename, 'thumb_') !== false) {
                            $checkfile = explode('thumb_', $filename);
                            $checkfile = $checkfile[1];
                        } else {
                            $checkfile = $filename;
                        }

                        $relativePath = str_replace(JPATH_ROOT . '/', '', $filePath); // this will get the relative path
                        $checkfile = str_replace($filename, $checkfile,
                            $relativePath); // this will get the final results of the file path we can use to check


                        $coverFilesList[] = array(
                            'absolute_path' => $filePath,
                            'filename' => $filename,
                            'checkFile' => $checkfile //this format is used to search within the tables
                        );
                    }
                }

                $db = JFactory::getDbo();
                //since we have all the list from the images in folder, grab all the covers from db
                $query = "SELECT image FROM " . $db->quoteName('#__community_photos') . " WHERE " .
                    $db->quoteName('image') . " LIKE " . $db->quote("%cover%");

                $db->setQuery($query);
                $photoTableResults = $db->loadColumn();

                //grab from user table
                $query = "SELECT cover FROM " . $db->quoteName('#__community_users') . " WHERE " .
                    $db->quoteName('cover') . " LIKE " . $db->quote("%cover%");

                $db->setQuery($query);
                $userTableResults = $db->loadColumn();

                //grab from group table
                $query = "SELECT cover FROM " . $db->quoteName('#__community_groups') . " WHERE " .
                    $db->quoteName('cover') . " LIKE " . $db->quote("%cover%");

                $db->setQuery($query);
                $groupTableResults = $db->loadColumn();

                //grab from event table
                $query = "SELECT cover FROM " . $db->quoteName('#__community_events') . " WHERE " .
                    $db->quoteName('cover') . " LIKE " . $db->quote("%cover%");

                $db->setQuery($query);
                $eventTableResults = $db->loadColumn();

                $allTableResults = array_merge($photoTableResults, $userTableResults, $groupTableResults, $eventTableResults);

                $totalCoverCleaned = 0;
                foreach ($coverFilesList as $cover) {
                    if (!in_array($cover['checkFile'], $allTableResults)) {
                        //if the file is not in the records, delete it
                        if (file_exists($cover['absolute_path'])) {
                            $totalCoverCleaned++;
                            //delete this file
                            unlink($cover['absolute_path']);
                        }
                    }
                }

                $response->addScriptCall(
                    "
                $('#clean_cover_local').show();
                $('p.clean_cover_local').html('" . JText::sprintf('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP_AVATAR_SUCCESS',
                        $totalCoverCleaned) . "');
                "
                );
            } catch (Exception $e) {
                $response->addScriptCall(
                    "
                $('#clean_cover_local').show();
                $('p.clean_cover_local').html('" . $e . "');
                "
                );
            }

            return $response->sendResponse();
        }


        //functioning but not used since cover is not stored in s3
        public function ajaxCleanS3OrphanedCover()
        {
            $response = new JAXResponse();
            $s3Storage = CStorage::getStorage('s3');
            try {
                $files = $s3Storage->getFileList('images/cover/');//this will get all the files within the folder
            }catch(Exception $e){
                $response->addScriptCall(
                    "
                $('#clean_avatar_s3').show();
                $('p.clean_avatar_s3').html('".JText::_('S3 did not setup properly')."');
                "
                );

                return $response->sendResponse();
            }

            $imageFormat = array('jpg', 'jpeg', 'png', 'gif'); //to only accept this format
            $coverFilesList = array();

            foreach ($files as $filePath => $fileInfo) {
                $filename = basename($filePath);
                $fileExt = explode('.', $filename);
                $fileExt = $fileExt[count($fileExt) - 1];
                if (in_array($fileExt, $imageFormat)) {
                    if (strpos($filename, 'thumb_') !== false) {
                        $checkfile = explode('thumb_', $filename);
                        $checkfile = $checkfile[1];
                    } else {
                        $checkfile = $filename;
                    }

                    $relativePath = str_replace(JPATH_ROOT . '/', '', $filePath); // this will get the relative path
                    $checkfile = str_replace($filename, $checkfile,
                        $relativePath); // this will get the final results of the file path we can use to check


                    $coverFilesList[] = array(
                        'absolute_path' => $filePath,
                        'filename' => $filename,
                        'checkFile' => $checkfile //this format is used to search within the tables
                    );
                }
            }

            $db = JFactory::getDbo();
            //since we have all the list from the images in folder, grab all the avatar from db
            $query = "SELECT image FROM " . $db->quoteName('#__community_photos') . " WHERE " .
                $db->quoteName('image') . " LIKE " . $db->quote("%cover%");

            $db->setQuery($query);
            $photoTableResults = $db->loadColumn();

            //grab from user table
            $query = "SELECT avatar FROM " . $db->quoteName('#__community_users') . " WHERE " .
                $db->quoteName('cover') . " LIKE " . $db->quote("%cover%");

            $db->setQuery($query);
            $userTableResults = $db->loadColumn();

            //grab from group table
            $query = "SELECT avatar FROM " . $db->quoteName('#__community_groups') . " WHERE " .
                $db->quoteName('cover') . " LIKE " . $db->quote("%cover%");

            $db->setQuery($query);
            $groupTableResults = $db->loadColumn();

            //grab from event table
            $query = "SELECT cover FROM " . $db->quoteName('#__community_events') . " WHERE " .
                $db->quoteName('cover') . " LIKE " . $db->quote("%cover%");

            $db->setQuery($query);
            $eventTableResults = $db->loadColumn();

            $allTableResults = array_merge($photoTableResults, $userTableResults, $groupTableResults, $eventTableResults);

            $totalCoverCleaned = 0;
            foreach ($coverFilesList as $cover) {
                if (!in_array($cover['checkFile'], $allTableResults)) {
                    //if the file is not in the records, delete it
                    $totalCoverCleaned++;
                    //delete this file
                    $s3Storage->delete($cover['absolute_path']);

                    //remove from the s3 table if there's any
                    $query = "DELETE FROM ".$db->quoteName('#__community_storage_s3')." WHERE ".$db->quoteName('storageid')."=".$db->quote($cover['absolute_path']);
                    $db->setQuery($query);
                    $db->execute();
                }
            }

            $response->addScriptCall(
                "
                $('#clean_cover_s3').show();
                $('p.clean_cover_s3').html('".JText::sprintf('COM_COMMUNITY_TROUBLESHOOTS_CLEANUP_AVATAR_SUCCESS',$totalCoverCleaned)."');
                "
            );

            return $response->sendResponse();
        }
    }
}
