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
$isPhotoModal = $config->get('album_mode') == 1;
?>
<div class="joms-page">
	<h3 class="joms-page__title"><?php echo JText::_('COM_COMMUNITY_NOTIFICATIONS'); ?></h3>
    <?php //echo $submenu;?>

	<p class="joms-centered">
		<?php echo JText::sprintf('COM_COMMUNITY_PROFILE_NOTIFICATION_LIST_LIMIT_MSG',$config->get('maxnotification'));?>
	</p>

	<?php if($notifications) : ?>
		<?php
			foreach( $notifications as $row ) {
			$user	= CFactory::getUser( $row->actor );
		?>
		<div class="joms-stream__container joms-stream--discussion">
			<div class="joms-stream__header">
				<div class="joms-avatar--stream <?php echo CUserHelper::onlineIndicator($user); ?>">
					<a href="<?php echo CContentHelper::injectTags('{url}',$row->params,true); ?>">
						<img src="<?php echo $user->getThumbAvatar(); ?>" alt="<?php echo $user->getDisplayName(); ?>" data-author="<?php echo $user->id; ?>" />
					</a>
				</div>
				<div class="joms-stream__meta">
					<div class="joms-stream__time">
						<small><?php echo CTimeHelper::timeLapse(CTimeHelper::getDate($row->created)); ?></small>
					</div>
				</div>
			</div>
			<div class="joms-stream__body">
				<div class="cStream-Headline"><?php

					$content =  CContentHelper::injectTags($row->content,$row->params,true);

					if ( $isPhotoModal && $row->cmd_type == 'notif_photos_like' ) {
						preg_match_all('/(albumid|photoid)=(\d+)/', $content, $matches);

						// Get albumid and photoid.
						$albumid = false;
						$photoid = false;
						foreach ( $matches[1] as $index => $varname ) {
							if ( $varname == 'albumid' ) {
								$albumid = $matches[2][ $index ];
							} else if ( $varname == 'photoid' ) {
								$photoid = $matches[2][ $index ];
							}
						}

						preg_match('/href="[^"]+albumid[^"]+"/', $content, $matches);

						$content = preg_replace(
							'/href="[^"]+albumid[^"]+"/',
							'href="javascript:" onclick="joms.api.photoOpen(\''
							. ( $albumid ? $albumid : '' ) . '\', \'' . ( $photoid ? $photoid : '' ) . '\');"',
							$content
						);
					}
					echo $content;
					?></div>
			</div>
		</div>
		<?php } ?>
	<?php endif; ?>

<?php if ($pagination->getPagesLinks() && ($pagination->pagesTotal > 1 || $pagination->total > 1) ) { ?>
    <div class="joms-pagination">
        <?php echo $pagination->getPagesLinks(); ?>
    </div>
<?php } ?>
</div>

<script>
joms.jQuery(".cProfile-DataStream p a").each(function(key,val){
	joms.jQuery(val).attr("target","_blank");
	joms.jQuery(val).click(function(e){
		if (!e) var e = window.event;
		e.cancelBubble = true;
		if (e.stopPropagation) e.stopPropagation();
	});
});
joms.jQuery(".cProfile-DataStream li").each(function(key,val){
	joms.jQuery(val).click(function(e){
		var link = joms.jQuery(this).find("a").attr("href");
		if (link.length > 0){
			window.open(link,null);
		}
	});
});
</script>
