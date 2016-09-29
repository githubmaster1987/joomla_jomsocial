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
?>

<?php if (!empty($data)) { ?>
    <ul class="joms-list--friend">
    <?php foreach ($data as $_data) { ?>
        <?php

            $filename = $_data->name;
            $filepath = $_data->filepath;
            $fileext  = strrpos( $filepath, '.' );
            $userlink = '<a href="' . CUrlHelper::userLink($_data->user->id) . '">' . $_data->user->getDisplayName() . '</a>';

            if ( $fileext !== false ) {
                $filename .= substr( $filepath, $fileext );
            }

        ?>
        <li class="joms-list__item joms-js--file-<?php echo $_data->id; ?>">

                <a href="<?php echo JURI::base() . $filepath; ?>" target="_blank" title="<?php echo $filename; ?>" class="joms-text--break"
                    onclick="joms.api.fileUpdateHit(<?php echo $_data->id; ?>);"><?php echo $filename; ?></a>
                <span class="joms-block joms-text--light"><?php echo ($_data->hits > 1 ) ? JText::sprintf('COM_COMMUNITY_FILE_HIT_PLURAL',$_data->hits) : JText::sprintf('COM_COMMUNITY_FILE_HIT_SINGULAR',$_data->hits); ?> <?php echo $_data->filesize?></span>
                <p class="uploaded"><small><?php echo JText::sprintf('COM_COMMUNITY_FILES_UPLOAD_BY' , $userlink , $_data->parentName , $_data->parentType );?></small></p>
            <?php if(isset($_data->deleteable) && $_data->deleteable) {?>
                <a href="javascript:" class="joms-button--neutral joms-button--smallest" onclick="joms.api.fileRemove('discussion', '<?php echo $_data->id; ?>');"><?php echo JText::_('COM_COMMUNITY_DELETE'); ?></a>
            <?php } ?>
        </li>
    <?php } ?>
    </ul>
<?php } else { ?>
    <span class="noFiles"><?php echo JText::_('COM_COMMUNITY_FILES_NO_FILE'); ?></span>
<?php } ?>
