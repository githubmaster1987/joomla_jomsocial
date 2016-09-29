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
?>
<fieldset class="adminform joms-js--digestprev">
    <table class="admintable" cellspacing="1">
        <tbody>
            <tr>
                <td width="200" class="key">
                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_PREVIEW_USER_HINT') ?>">
                        <?php echo JText::_('COM_COMMUNITY_DIGEST_PREVIEW_USER_LABEL'); ?></span>
                </td>
                <td valign="top">
                    <input type="text" name="username" value="" size="10" autocomplete="off" placeholder="<?php echo JText::_('COM_COMMUNITY_DIGEST_PREVIEW_USER_SEARCH') ?>" style="margin-bottom:0">
                    <div style="position:relative;height:1px;z-index:1">
                        <div class="joms-js--digestprev-users" style="position:absolute;left:0;top:0;width:208px;border:1px solid rgb(245,153,66);border-top:0 none;display:none;background:#FFF;padding:5px">
                            <div class="joms-js--loading" style="display:none">
                                <img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader">
                            </div>
                            <div class="joms-js--digestprev-result"></div>
                        </div>
                    </div>
                    <div class="joms-js--digestprev-selected" style="padding:5px 0 10px;display:none"></div>
                </td>
            </tr>
            <tr>
                <td width="200" class="key">
                    <span class="js-tooltip" title="<?php echo JText::_('COM_COMMUNITY_DIGEST_PREVIEW_INACTIVE_HINT') ?>">
                        <?php echo JText::_('COM_COMMUNITY_DIGEST_PREVIEW_INACTIVE_LABEL'); ?></span>
                </td>
                <td valign="top">
                    <input type="text" name="inactive" value="" size="10" class="joms-js--inactive">
                </td>
            </tr>
            <tr>
                <td width="200" class="key">
                    <span>&nbsp;</span>
                </td>
                <td valign="top">
                    <button class="btn btn-primary btn-small joms-js--preview">
                        <?php echo JText::_('COM_COMMUNITY_DIGEST_PREVIEW_BUTTON'); ?>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</fieldset>
<script>
    (function() {
        var $input, $div, $selected, $inactive, $btn, $doc, admin_site, live_site, xhr;

        function init() {
            admin_site = window.jax_live_site;
            live_site = admin_site.replace('/administrator', '');

            $input = window.jQuery('[name=username]');
            $div = window.jQuery('.joms-js--digestprev-users');
            $selected = window.jQuery('.joms-js--digestprev-selected').empty();
            $inactive = window.jQuery('.joms-js--inactive');
            $btn = window.jQuery('.joms-js--preview');
            $doc = window.jQuery(document);

            $input.on('keyup', search );
            $div.on('click', 'a[data-id]', select );
            $btn.on('click', preview );
            $doc.on('click', documentClick );
        }

        function search() {
            var keyword = $input.val() || '';
            if ( !keyword.replace(/^\s+|\s+$/g, '') ) {
                return;
            }

            if ( xhr ) {
                xhr.abort();
            }

            var $dropdown = $div.find('.joms-js--digestprev-result').hide(),
                $loading = $div.find('.joms-js--loading').show();

            $div.show();

            window.jax_live_site = live_site;
            xhr = joms.ajax({
                func: 'search,ajaxSearch',
                data: [ keyword ],
                callback: function( json ) {
                    var $form, $btn, html, i, max;

                    $loading.hide();

                    if ( json.error ) {
                        $dropdown.html( json.error ).show();
                        return;
                    }

                    if ( json.length ) {
                        html = '';
                        for ( i = 0; i < json.length; i++ ) {
                            html += '<div style="padding:2px 0"><a href="javascript:" data-id="' + json[i].id + '" style="color:inherit;text-decoration:none;display:block">';
                            html += '<img src="' + json[i].thumb + '" width="32"> &nbsp;';
                            html += '<span>' + json[i].name + '</span>';
                            html += '</a></div>';
                        }

                        $dropdown.html( html ).show();
                    }
                }
            });
            window.jax_live_site = admin_site;
        }

        function select( e ) {
            var $el = $( e.currentTarget );

            $input.data('id', $el.data('id')).val('');
            $div.hide();
            $selected.show().html([
                '<img src="', $el.find('img').attr('src'), '" width="32"> &nbsp;',
                '<span>', $el.find('span').text(), '</span>'
            ].join(''));
        }

        function preview( e ) {
            var id = +$input.data('id'),
                inactive = +$inactive.val() || 0;

            cWindowShow('jax.call("community", "admin,digest,ajaxGetPreview", ' + id + ', ' + inactive + ');', '&nbsp;' , 800 , 450 );
            window.jQuery('#js-cpanel .modal').css({
                width: 800,
                marginLeft: 0,
                left: '50%',
                transform: 'translate(-50%, 0)'
            });
        }

        function documentClick() {
            var $el = window.jQuery( this );
            if ( $el.closest( $btn.add( $div ) ).length < 1 ) {
                $div.hide();
            }
        }

        var timer = setInterval(function() {
            if ( window.jQuery ) {
                clearInterval( timer );
                init();
            }
        }, 1000 );
    })();
</script>
