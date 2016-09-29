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

$model      = CFactory::getModel( 'photos');
$photos     = $model->getPopularPhotos( 8 , 0 );

$config = CFactory::getConfig();
$isPhotoModal = $config->get('album_mode') == 1;

?>

<div class="joms-stream__body joms-stream-box" >
    <h4 ><?php echo JText::_('COM_COMMUNITY_ACTIVITIES_TOP_PHOTOS'); ?></h4>
    <ul class="joms-list--thumbnail">
        <?php foreach( $photos as $photo ) { ?>
            <li class="joms-list__item">
                <a title="<?php echo $this->escape($photo->caption); ?>"
                    <?php if ( $isPhotoModal ) { ?>
                    href="javascript:" onclick="joms.api.photoOpen('<?php echo $photo->albumid ?>', '<?php echo $photo->id ?>')"
                    <?php } else { ?>
                    href="<?php echo $photo->getPhotoLink(); ?>"
                    <?php } ?>
                    >
                    <?php $user = CFactory::getUser($photo->creator); ?>
                    <img alt="<?php echo $this->escape($photo->caption);?>" src="<?php echo $photo->getThumbURI();?>" />
                </a>
            </li>
        <?php
        }
        ?>
    </ul>
</div>
