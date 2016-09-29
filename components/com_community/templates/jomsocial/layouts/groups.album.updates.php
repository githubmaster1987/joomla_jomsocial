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
$isPhotoModal = $config->get('album_mode') == 1;

?>

<?php if ( $albums ) { ?>
<div class="joms-module__wrapper">
    <div class="joms-tab__bar">
        <a href="#joms-group--photos" class="active"><?php echo JText::_('COM_COMMUNITY_GROUPS_LATEST_ALBUM_UPDATE_TITLE'); ?></a>
    </div>

	<div id="#joms-group--photos" class="joms-tab__content">
		<ul class="joms-list--photos">
		<?php foreach ( $albums as $album ) { ?>
			<li class="joms-list__item">
				<a
                    <?php if ( $isPhotoModal ) { ?>
                    href="javascript:" onclick="joms.api.photoOpen('<?php echo $album['album_id']; ?>', '');"
                    <?php } else { ?>
                    href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=album&albumid='.$album['album_id'].'&groupid='.$album['groupid']); ?>"
                    <?php } ?>
                >
					<img src="<?php echo $album['album_thumb']; ?>" title="<?php echo $album['group_name']; ?>" alt=" <?php echo $album['album_name']; ?> " />
				</a>
			</li>
		<?php } ?>
		</ul>
	</div>
</div>
<?php } ?>
