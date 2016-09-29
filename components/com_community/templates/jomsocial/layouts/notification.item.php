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

<?php foreach ($iRows as $row) {

    if(!$row->actor && isset($row->systemMessage)){continue;} // we hide this notification if there actor is empty

    $content = CContentHelper::injectTags($row->content, $row->params, true);

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
?>
<li>
    <div class="joms-popover__avatar">
        <div class="joms-avatar">
            <img src="<?php echo $row->actorAvatar; ?>" alt="avatar" >
        </div>
    </div>
    <div class="joms-popover__content">
        <?php echo $content; ?>
        <small><?php echo $row->timeDiff; ?></small>
    </div>
</li>
<?php } ?>
