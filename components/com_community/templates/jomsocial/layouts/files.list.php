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

<div class="joms-tab__content">

    <?php if ( !empty($data) ) { ?>

    <h4 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_FILES_AVAILABLE')?></h4>
    <ul class="joms-list--files">
        <?php for ( $i = 0; $i <= 4; $i++ ) { ?>
            <?php if ( !empty($data[$i]) ) { ?>
            <?php

                $filename = $data[$i]->name;
                $filepath = $data[$i]->filepath;
                $fileext  = strrpos( $filepath, '.' );
                $userlink = '<a href="' . CUrlHelper::userLink($data[$i]->user->id) . '">' . $data[$i]->user->getDisplayName() . '</a>';
                $fileDownloadLink = CRoute::_('index.php?option=com_community&view=files&task=downloadfile&type='.$type.'&id='.$data[$i]->id);

                if ( $fileext !== false ) {
                    $filename .= substr( $filepath, $fileext );
                }

            ?>
            <li class="joms-list__item joms-js--file-<?php echo $data[$i]->id; ?>">
                <p>
                    <a href="<?php echo $fileDownloadLink; ?>" target="_blank" title="<?php echo $filename; ?>" onclick="joms.api.fileUpdateHit(<?php echo $data[$i]->id; ?>);">
                        <?php echo JHTML::_('string.truncate', strip_tags($filename), 40); ?>
                    </a>
                </p>
                <div>
                    <?php echo JText::sprintf( 'COM_COMMUNITY_PHOTOS_UPLOADED_BY', $userlink ); ?>
                </div>
                <small class="joms-text--light">
                    <?php echo ($data[$i]->hits > 1 ) ? JText::sprintf('COM_COMMUNITY_FILE_HIT_PLURAL',$data[$i]->hits) : JText::sprintf('COM_COMMUNITY_FILE_HIT_SINGULAR',$data[$i]->hits) ?> <?php echo $data[$i]->filesize; ?>
                    <?php if($data[$i]->deleteable) {?>
                        <a href="javascript:void(0)" class="joms-button--link" onClick="joms.api.fileRemove('<?php echo $type; ?>', '<?php echo $data[$i]->id?>');">
                            <?php echo JText::_('COM_COMMUNITY_FILES_DELETE')?>
                        </a>
                    <?php }?>
                </small>
            </li>
            <?php } ?>
        <?php } ?>
    </ul>

    <?php } else { ?>

    <h4 class="joms-text--title"><?php echo JText::_('COM_COMMUNITY_FILES_AVAILABLE')?></h4>
    <p>
        <?php echo JText::_('COM_COMMUNITY_FILES_NO_FILE')?>
    </p>

    <?php }?>

    <?php if($permission){?>
        <hr class="joms-divider">
        <a class="joms-button--neutral joms-button--small" href="javascript:" onClick="joms.api.fileUpload('<?php echo $type; ?>', '<?php echo $id; ?>');"><?php echo JText::_('COM_COMMUNITY_FILES_UPLOAD'); ?></a>
    <?php }?>
    <?php if(count($data)>5) { ?>
        <hr class="joms-divider">
        <a class="joms-button--neutral joms-button--small" href="javascript:" onClick="joms.api.fileList('<?php echo $type?>', '<?php echo $id; ?>');"><?php echo JText::_('COM_COMMUNITY_MORE'); ?></a>
    <?php }?>
</div>
