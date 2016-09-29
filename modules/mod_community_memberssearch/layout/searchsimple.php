<?php
    /**
     * @copyright (C) 2015 iJoomla, Inc. - All rights reserved.
     * @license GNU General Public License, version 2 (http://www.gnu.org/licenses/gpl-2.0.html)
     * @author iJoomla.com <webmaster@ijoomla.com>
     * @url https://www.jomsocial.com/license-agreement
     * The PHP code portions are distributed under the GPL license. If not otherwise stated, all images, manuals, cascading style sheets, and included JavaScript *are NOT GPL, and are released under the IJOOMLA Proprietary Use License v1.0
     * More info at https://www.jomsocial.com/license-agreement
     */

defined('_JEXEC') or die('Unauthorized Access');
$svgPath = CFactory::getPath('template://assets/icon/joms-icon.svg');
include_once $svgPath;

?>

<div class="joms-module joms-module--membersearch">

<!-- simple search form -->
<form name="jsform-search-simplesearch" class="js-form" action="<?php echo CRoute::_('index.php?option=com_community&view=search&task=display') ?>"
      method="post">

    <div class="joms-form__group">
        <input type="text" class="joms-input--search" autocomplete="off" placeholder="<?php echo JText::_('MOD_COMMUNITY_MEMBERSSEARCH_START_TYPING') ?>" name="q" style="width:75%" />
        <span class="joms-gap--inline"></span>
        <button type="submit" class="joms-button--primary joms-button--small joms-inline--desktop">
            <svg class="joms-icon joms-icon--white" viewBox="0 0 14 20">
                <use xlink:href="<?php echo CRoute::getURI(); ?>#joms-icon-search"/>
            </svg>
        </button>
        <div style="position:relative;height:2px;">
            <div style="position:absolute;right:0;left:0;top:0">
                <ul class="joms-dropdown joms-js--search-result">
                    <li class="joms-js--noremove joms-js--loading">
                        <img src="<?php echo JURI::root(true); ?>/components/com_community/assets/ajax-loader.gif" alt="loader">
                    </li>
                    <li class="joms-js--noremove joms-js--viewall">
                        <div>
                            <a href="javascript:" data-lang="<?php echo JText::_('COM_COMMUNITY_VIEW_ALL_N_RESULTS'); ?>"></a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="joms-form__group">
        <?php if (isset($postresult) && $postresult && COwnerHelper::isCommunityAdmin()) { ?>
            <a href="javascript:"
               onclick="joms_search_save();"><?php echo JText::_('COM_COMMUNITY_MEMBERLIST_SAVE_SEARCH'); ?></a>
            <script>
                joms_search_history = <?php echo empty($filterJson) ? "''" : $filterJson ?>;
                joms_search_save = function () {
                    joms.api.searchSave({
                        keys: '<?php echo $keyList ?>',
                        json: joms_search_history,
                        operator: joms.jQuery('[name=operator]:checked').val(),
                        avatar_only: joms.jQuery('[name=avatar]')[0].checked
                    });
                };
            </script>
        <?php } ?>
    </div>

</form>
<form method="post" action="<?php echo CRoute::_('index.php?option=com_community&view=search'); ?>" style="height:1px;padding:0;margin:0">
    <input type="hidden" name="q" value="">
</form>

<script>
    (function() {
        var $mod, $input, $ddbtn, $dd, xhr;

        function init() {
            $mod = window.jQuery('.joms-module--membersearch'),
            $input = $mod.find('input[type=text][name=q]');
            $input.on('keyup', search );
            $ddbtn = $mod.find('.joms-js--has-dropdown');
            $dd = $mod.find('.joms-dropdown');
            $ddbtn.on('click', toggleDd );
        }

        function search() {
            var keyword = $input.val() || '';
            if ( !keyword.replace(/^\s+|\s+$/g, '') ) {
                return;
            }

            if ( xhr ) {
                xhr.abort();
            }

            var $dropdown = $mod.find('.joms-js--search-result'),
                $loading = $dropdown.find('.joms-js--loading'),
                $viewall = $dropdown.find('.joms-js--viewall');

            $dropdown.find('li:not(.joms-js--noremove)').remove();
            $viewall.hide();
            $loading.show();

            xhr = joms.ajax({
                func: 'search,ajaxSearch',
                data: [ keyword ],
                callback: function( json ) {
                    var $form, $btn, html, i, max;

                    $loading.hide();

                    if ( json.error ) {
                        html = '<li class="joms-js--error">' + json.error + '</li>';
                        $loading.before( html );
                        return;
                    }

                    if ( json.length ) {
                        html = '';
                        max = Math.min( 3, json.length );
                        for ( i = 0; i < max; i++ ) {
                            html += '<li><div class="joms-popover__avatar"><div class="joms-avatar">';
                            html += '<img src="' + json[i].thumb + '"></div></div>';
                            html += '<div class="joms-popover__content">';
                            html += '<h5><a href="' + json[i].url + '">' + json[i].name + '</a></h5>';
                            html += '</div></li>';
                        }

                        $form = $mod.find('input[type=hidden][name=q]').closest('form');
                        $form.find('input').val( keyword );
                        $viewall.off('click', 'a').on('click', 'a', function() {
                            $form[0].submit();
                        });

                        $btn = $viewall.find('a');
                        $btn.html( $btn.data('lang').replace( '%1$s', json.length ) );

                        $loading.before( html );
                        $viewall.show();
                        $dropdown.show();
                    }
                }
            });
        }

        function toggleDd() {
            $dd.removeClass('joms-dropdown-r joms-dropdown-t');

            setTimeout(function() {
                var winWidth = window.innerWidth,
                    winHeight = window.innerHeight,
                    offset, width, height,
                    className = [];

                if ( $dd.is(':visible') ) {
                    offset = $dd.offset();
                    width = $dd.width();
                    height = $dd.height();
                    if ( offset.left + width > winWidth ) {
                        className.push('joms-dropdown-r');
                    }
                    if ( offset.top + height > winHeight ) {
                        className.push('joms-dropdown-t');
                    }
                    if ( className.length ) {
                        $dd.addClass( className.join(' ') );
                    }
                }
            });
        }

        var timer = setInterval(function() {
            if ( window.jQuery ) {
                clearInterval( timer );
                init();
            }
        }, 1000 );
    })();
</script>

</div>
