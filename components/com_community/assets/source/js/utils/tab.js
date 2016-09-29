(function( root, factory ) {

    joms.util || (joms.util = {});
    joms.util.tab = factory( root );

})( window, function() {

function start() {
    startTab();
    startLegacyTab();
}

function startTab() {
    var cssTabBar  = '.joms-tab__bar',
        cssTabItem = '.joms-tab__content',
        doc;

    function toggle( e ) {
        var el = joms.jQuery( e.currentTarget ),
            par = el.parent( cssTabBar ),
            target = el.attr('href'),
            selected, url, clicked;

        if ( el.find('.joms-tab__bar--button').length ) {
            clicked = joms.jQuery( e.target );
            if ( clicked.hasClass('add') || clicked[0].tagName.match(/use|svg/i) ) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }

        if ( target.indexOf('#') !== 0 ) {
            return;
        }

        selected = el.closest( cssTabBar ).siblings( target );
        selected.show().siblings( cssTabItem ).hide();
        el.addClass('active').siblings('a').removeClass('active');

        url = par.data('id');
        if ( url ) {
            url = '#tab:' + url + '/' + el.data('id');
            window.location = url;
        }

        return false;
    }

    function initialize() {
        uninitialize();
        doc || (doc = joms.jQuery( document.body ));
        doc.on( 'click.joms-tab', cssTabBar + ' a', toggle );
    }

    function uninitialize() {
        doc && doc.off('click.joms-tab');
    }

    initialize();
}

function startLegacyTab() {
    joms.jQuery('.cTabsBar').on( 'click', 'li', function( e ) {
        var li = joms.jQuery( e.currentTarget ),
            wrapper = li.closest('.cTabsBar').siblings('.cTabsContentWrap'),
            index, tab;

        if ( !wrapper.length )
            return;

        index = li.prevAll().length;
        tab = wrapper.children('.cTabsContent').eq( index );

        if ( !tab.length )
            return;

        li.addClass('cTabCurrent').siblings('.cTabCurrent').removeClass('cTabCurrent');
        tab.siblings('.cTabsContent').hide();
        tab.show();
    });
}

// Exports.
return {
    start: start
};

});
