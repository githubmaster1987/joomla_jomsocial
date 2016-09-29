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

$config = CFactory::getConfig();

$mood = $this->acts[0]->params->get( 'mood', NULL );

$isPhotoModal = $config->get('album_mode') == 1;

?>

<?php if (count($photos) > 0) { ?>

    <p><?php $title = $this->acts[0]->title;

        echo CActivities::shorten($title, $this->acts[0]->id, 0, $config->getInt('streamcontentlength'));

        ?></p>

    <?php if (count($photos) > 1) { ?>

        <div class="joms-media--images">
        <?php foreach ($photos as $photo) { ?>
            <a
                <?php if ($isPhotoModal) { ?>
                href="javascript:" onclick="joms.api.photoOpen('<?php echo $photo->albumid; ?>', '<?php echo $photo->id; ?>');"
                <?php } else { ?>
                href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $photo->albumid . '&photoid=' . $photo->id); ?>"
                <?php } ?>
            >
                <img src="<?php echo $photo->getImageURI(); ?>" alt="<?php echo $this->escape($photo->caption); ?>">
            </a>
        <?php } ?>
        </div>

        <div class="joms-media--loading">
            <div class="cEmpty small joms-rounded">
                <?php echo JText::_('COM_COMMUNITY_PHOTOS_BEING_LOADED'); ?>
            </div>
        </div>

    <?php } else { ?>

        <?php foreach ($photos as $photo) {
            //since 4.2 A photo can be gif photo
            $gifFile = '';
            if($photo->params != '' && $photo->params != '{}'){
                $params = new JRegistry($photo->params);
                $gifFile = $params->get('animated_gif','');
            }
            ?>
            <a
                <?php if ($gifFile) { ?>
                href="javascript:"
                <?php } else if ($isPhotoModal) { ?>
                href="javascript:" onclick="joms.api.photoOpen('<?php echo $photo->albumid; ?>', '<?php echo $photo->id; ?>');"
                <?php } else { ?>
                href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $photo->albumid . '&photoid=' . $photo->id); ?>"
                <?php } ?>
            >
                <div class="joms-media--image" <?php if ($gifFile) echo 'style="display:inline-block;max-width:100%;"' ?>>
                    <?php if ($gifFile) { ?>
                        <img src="" alt="" data-src="<?php echo $photo->getGifURI(); ?>" style="display:none" onclick="jQuery(this).hide().next().show();" />
                        <div style="position:relative;display:inline-block;max-width:100%;" onclick="joms_tmp = jQuery(this).hide().prev(); joms_tmp.attr('src', joms_tmp.data('src')); joms_tmp.show();">
                            <img src="<?php echo $photo->getImageURI(); ?>" alt="<?php echo $this->escape($photo->caption); ?>" />
                            <div class="play-button"></div>
                        </div>
                    <?php } else { ?>
                        <img src="<?php echo $photo->getImageURI(); ?>" alt="<?php echo $this->escape($photo->caption); ?>" />
                    <?php } ?>
                </div>
            </a>
        <?php } ?>

    <?php } ?>

<?php } else { ?>

    <div class="cEmpty small joms-rounded">
        <?php echo JText::_('COM_COMMUNITY_PHOTO_REMOVED'); ?>
    </div>

<?php } ?>
