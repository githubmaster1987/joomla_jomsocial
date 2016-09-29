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

    Class CWallsAccess implements CAccessInterface
    {
        /**
         * Method to check if a user is authorised to perform an action in this class
         *
         * @param	integer	$userId	Id of the user for which to check authorisation.
         * @param	string	$action	The name of the action to authorise.
         * @param	mixed	$asset	Name of the asset as a string.
         *
         * @return	boolean	True if authorised.
         * @since	Jomsocial 2.4
         */
        static public function authorise()
        {
            $args      = func_get_args();
            $assetName = array_shift ( $args );

            if (method_exists(__CLASS__,$assetName)) {
                return call_user_func_array(array(__CLASS__, $assetName), $args);
            } else {
                return null;
            }
        }

        static public function wallsDelete($userid, $wall)
        {
            $my = CFactory::getUser();

            //community admin can always delete
            if(COwnerHelper::isCommunityAdmin()){
                return true;
            }

            //bear in mind that not all contentid is activity id, it could be photo id or album id depending on the type
            $cid = 0;
            if($wall->params != '' && $wall->params != '{}'){
                if($wall->params instanceof JRegistry){
                    $cid = $wall->params->get('activityId',0);
                }else{
                    $wall->params = new JRegistry($wall->params);
                    $cid = $wall->params->get('activityId',0);
                }
            }elseif($wall->type == 'profile.status'){
                //in the case of profile status, the contentid is linked to the activity id
                $cid = $wall->contentid;
            }

            //check if this is a photo owner, if he is, he can always remove the comment under the photo
            if($wall->type == 'photos'){
                $photoTable = JTable::getInstance('photo','CTable');
                $photoTable->load($wall->contentid);
                if($photoTable->creator == $my->id){
                    return true;
                }
            }elseif($wall->type == 'videos'){
                $photoTable = JTable::getInstance('video','CTable');
                $photoTable->load($wall->contentid);
                if($photoTable->creator == $my->id){
                    return true;
                }
            }elseif($wall->type == 'discussions'){
                $photoTable = JTable::getInstance('discussion','CTable');
                $photoTable->load($wall->contentid);
                if($photoTable->creator == $my->id){
                    return true;
                }
            }

            $actModel = CFactory::getModel('activities');
            $activity = $actModel->getActivity($cid);

            $ownPost = ($my->id == $wall->post_by);
            $targetPost = ($activity->target == $my->id);
            $allowRemove = (($ownPost || $targetPost || $activity->actor == $my->id) && $my->id) ;

            return $allowRemove;
        }
    }