(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.dropdown = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.util.dropdown;
    });

})( window, joms.jQuery, function( window, $, undefined ) {

var

// Event list.
evtClick = 'click.dropdown',
evtHide = 'collapse.dropdown',

// Selectors.
slrButton = '[data-ui-object=joms-dropdown-button]',
slrDropdown = '.joms-dropdown,.joms-popover',

// Element cache.
lastbtn,
lastdd,
popup,
elem,
doc;

function hide() {
    lastdd && lastdd.hide();
    lastbtn && btnRemoveClass( lastbtn );
}

function toggle( e ) {
    var btn, dd;

    e.stopPropagation();
    e.preventDefault();

    btn = $( e.currentTarget );
    dd = btn.siblings( slrDropdown );

    if ( !dd.length ) {
        return;
    }

    if ( dd.is(':visible') ) {
        dd.hide();
        btnRemoveClass( btn );
        return;
    }

    if ( joms.screenSize() === 'large' ) {
        hide();
        dd.show();
        btnAddClass( btn );
        lastbtn = btn;
        lastdd = dd;
        executeAdditionalFn( dd.attr('class') || '' );
        return;
    }

    joms.util.popup.prepare(function( mfp ) {
        popup = mfp;
        popup.items[0] = {
            type: 'inline',
            src: buildHtml( dd )
        };

        popup.updateItemHTML();
        executeAdditionalFn( dd.attr('class') || '' );

        elem = popup.contentContainer;
        elem.on( 'click', 'li > a', function() {
            popup.close();
        });
    });
}

function btnAddClass( btn ) {
    var par = btn.parent();
    if ( par.hasClass('.joms-focus__button--options--desktop') ) {
        par.addClass('active');
    } else {
        btn.addClass('active');
    }
}

function btnRemoveClass( btn ) {
    var par = btn.parent();
    if ( par.hasClass('.joms-focus__button--options--desktop') ) {
        par.removeClass('active');
    } else {
        btn.removeClass('active');
    }
}

function buildHtml( dd ) {
    return '<div class="joms-popup joms-popup--dropdown">' + dd[0].outerHTML + '</div>';
}

function executeAdditionalFn( className ) {
    if ( className.match('joms-popover--toolbar-general') ) {
        joms.api.notificationGeneral();
        return;
    }
    if ( className.match('joms-popover--toolbar-friendrequest') ) {
        joms.api.notificationFriend();
        return;
    }
    if ( className.match('joms-popover--toolbar-pm') ) {
        joms.api.notificationPm();
        return;
    }
}

// Change privacy dropdown.
var selectPrivacy = joms._.debounce(function( e ) {
    var className = e.currentTarget.className || '',
        ul, li, btn, hidden, span, svg;

    if ( className.indexOf('joms-dropdown--privacy') < 0 ) {
        return;
    }

    ul  = $( e.currentTarget );
    li  = $( e.target ).closest('li');

    if ( li.length ) {
        btn    = $('.joms-button--privacy').filter('[data-name="' + ul.data('name') + '"]');
        hidden = btn.children('[data-ui-object=joms-dropdown-value]');
        span   = btn.children('span');
        svg    = btn.find('use');

        hidden.val( li.data('value') );
        span.html( li.children('span').html() );
        svg.attr( 'xlink:href', window.location.href.replace(/#.*$/, '') + '#' + li.data('classname') );
    }

    hide();
    popup && popup.close();

}, 100 );

function initialize() {
    uninitialize();

    doc || (doc = $( document.body ));
    doc.on( evtClick, hide );
    doc.on( evtClick, slrButton, toggle );
    doc.on( evtHide, slrButton, hide );
    doc.on( evtClick, slrDropdown, function( e ) {
        if ( !$( e.target ).data('propagate') ) {
            e.stopPropagation();
        }
        selectPrivacy( e );
    });
}

function uninitialize() {
    if ( doc ) {
        doc.off( evtClick );
        doc.off( evtClick, slrButton );
        doc.off( evtHide, slrButton );
        doc.off( evtClick, slrDropdown );
    }
}

// Exports.
return {
    start: initialize,
    stop: uninitialize
};

});
