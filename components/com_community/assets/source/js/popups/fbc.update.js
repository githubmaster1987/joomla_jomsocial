(function( root, $, factory ) {

    joms.popup || (joms.popup = {});
    joms.popup.fbc || (joms.popup.fbc = {});
    joms.popup.fbc.update = factory( root, $ );

    define([ 'utils/popup' ], function() {
        return joms.popup.fbc.update;
    });

})( window, joms.jQuery, function( window ) {

var popup, elem, lang, isMember;

function render( _popup ) {
    if ( elem ) elem.off();
    popup = _popup;

    if ( !window.joms_use_tfa ) {
        update();
    } else {
        popup.items[0] = {
            type: 'inline',
            src: buildTfaDialog()
        };

        popup.updateItemHTML();

        elem = popup.contentContainer;

        elem.on( 'click', '.joms-js--button-next', function() {
            update( elem.find('[name=secret]').val() );
        });

        elem.on( 'click', '.joms-js--button-skip', function() {
            update();
        });
    }
}

function update( secret ) {
    if ( elem ) elem.off();

    joms.ajax({
        func: 'connect,ajaxUpdate',
        data: [ secret || '' ],
        callback: function( json ) {
            var isLoggedIn = json.jax_token_var;

            if ( isLoggedIn ) {
                json.btnNext = json.btnContinue;
                window.jax_token_var = json.jax_token_var;
            }

            popup.items[0] = {
                type: 'inline',
                src: buildHtml( json )
            };

            popup.updateItemHTML();

            elem = popup.contentContainer;
            lang = json.lang;

            elem.on( 'click', '.joms-js--button-next', isLoggedIn ? importData : next );
            elem.on( 'click', '.joms-js--button-back2', back2 );
            elem.on( 'click', '.joms-js--button-next2', next2 );
            elem.on( 'click', '.joms-js--button-back3', back3 );
        }
    });
}

function next() {
    var tnc, error;

    isMember = +elem.find('[name=membertype]:checked').val() === 2;

    if ( isMember ) {
        connectMember();
    } else {
        tnc = elem.find('#joms-js--fbc-tnc-checkbox');
        if ( !tnc.length ) {
            connectNewUser();
        } else {
            tnc = tnc[0];
            error = elem.find('.joms-js--fbc-tnc-error');
            if ( tnc.checked ) {
                error.hide();
                connectNewUser();
            } else {
                error.show();
            }
        }
    }
}

function back2() {
    elem.find('.joms-js--step2').hide();
    elem.find('.joms-js--step3').hide();
    elem.find('.joms-js--step1').show();
}

function next2() {
    if ( isMember ) {
        validateMember();
    } else {
        validateNewUser();
    }
}

function connectNewUser() {
    joms.ajax({
        func: 'connect,ajaxShowNewUserForm',
        data: [ '' ],
        callback: function( json ) {
            var div;

            elem.find('.joms-js--step1').hide();

            div = elem.find('.joms-js--step2');
            div.find('.joms-popup__content').html( json.html );
            div.find('.joms-js--button-back2').html( json.btnBack );
            div.find('.joms-js--button-next2').html( json.btnCreate );
            div.show();
        }
    });
}

function connectMember() {
    joms.ajax({
        func: 'connect,ajaxShowExistingUserForm',
        data: [ '' ],
        callback: function( json ) {
            var div;

            elem.find('.joms-js--step1').hide();

            div = elem.find('.joms-js--step2');
            div.find('.joms-popup__content').html( json.html );
            div.find('.joms-js--button-back2').html( json.btnBack );
            div.find('.joms-js--button-next2').html( json.btnLogin );
            div.show();
        }
    });
}

function validateNewUser() {
    var div = elem.find('.joms-js--step2'),
        name = div.find('[name=name]').val(),
        user = div.find('[name=username]').val(),
        email = div.find('[name=email]').val(),
        types = div.find('[name=profiletype]'),
        profileType = '',
        type;

    if ( types.length ) {
        type = types.filter(':checked');
        if ( !type.length ) {
            div.hide();
            div = elem.find('.joms-js--step3');
            div.find('.joms-popup__content').html( lang.selectProfileType );
            div.find('.joms-js--button-back3').html( lang.btnBack );
            div.show();
            return;
        }
        profileType = types.filter(':checked').val();
    }

    joms.ajax({
        func: 'connect,ajaxCreateNewAccount',
        data: [ name, user, email, profileType ],
        callback: function( json ) {
            var div;

            if ( json.error ) {
                elem.find('.joms-js--step2').hide();

                div = elem.find('.joms-js--step3');
                div.find('.joms-popup__content').html( json.error );
                div.find('.joms-js--button-back3').html( json.btnBack );
                div.show();
                return;
            }

            elem.off();
            popup.close();
            joms.popup.fbc.update();
        }
    });
}

function validateMember() {
    var div = elem.find('.joms-js--step2'),
        user = div.find('[name=username]').val(),
        pass = div.find('[name=password]').val();

    joms.ajax({
        func: 'connect,ajaxValidateLogin',
        data: [ user, pass ],
        callback: function( json ) {
            var div;

            if ( json.error ) {
                elem.find('.joms-js--step2').hide();

                div = elem.find('.joms-js--step3');
                div.find('.joms-popup__content').html( json.error );
                div.find('.joms-js--button-back3').html( json.btnBack );
                div.show();
                return;
            }

            elem.off();
            popup.close();
            joms.popup.fbc.update();
        }
    });
}

// function checkName( name ) {
//     joms.ajax({
//         func: 'connect,ajaxCheckName',
//         data: [ name ],
//         callback: function( json ) {
//         }
//     });
// }

// function checkUsername( username ) {
//     joms.ajax({
//         func: 'connect,ajaxCheckUsername',
//         data: [ username ],
//         callback: function( json ) {
//         }
//     });
// }

// function checkEmail( email ) {
//     joms.ajax({
//         func: 'connect,ajaxCheckEmail',
//         data: [ email ],
//         callback: function( json ) {
//         }
//     });
// }

function importData() {
    var status = elem.find('[name=importstatus]'),
        avatar = elem.find('[name=importavatar]');

    status = status.length && status[0].checked ? 1 : 0;
    avatar = avatar.length && avatar[0].checked ? 1 : 0;

    joms.ajax({
        func: 'connect,ajaxImportData',
        data: [ status, avatar ],
        callback: function( json ) {
            var div;

            elem.find('.joms-js--step1').hide();

            if ( json.error ) {
                elem.off('click').on( 'click', '.joms-js--button-next2', cancel );

                div = elem.find('.joms-js--step2');
                div.find('.joms-popup__content').html( json.error );
                div.find('.joms-js--button-back2').hide();
                div.find('.joms-js--button-next2').html( json.btnNext );
                div.show();
                return;
            }

            if ( !json.btnUpdate ) {
                cancel();
                window.location = json.redirect;
                return;
            }

            elem.off('click').on( 'click', '.joms-js--button-back2', cancel );
            elem.off('click').on( 'click', '.joms-js--button-next2', function() {
                window.location = json.redirect;
            });

            div = elem.find('.joms-js--step2');
            div.find('.joms-popup__content').html( json.html );
            div.find('.joms-js--button-back2').html( json.btnSkip );
            div.find('.joms-js--button-next2').html( json.btnUpdate );
            div.show();
        }
    });
}

function back3() {
    elem.find('.joms-js--step3').hide();
    if ( isMember ) {
        elem.find('.joms-js--step2').hide();
        elem.find('.joms-js--step1').show();
    } else {
        elem.find('.joms-js--step2').show();
        elem.find('.joms-js--step1').hide();
    }
}

function cancel() {
    elem.off();
    popup.close();
}

function buildHtml( json ) {
    json || (json = {});

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', json.title, '</div>',
        '<div class="joms-js--step1">',
            '<div class="joms-popup__content ', ( json.btnNext ? '' : 'joms-popup__content--single' ), '">', ( json.error || json.html || '' ), '</div>',
            ( json.btnNext ? '<div class="joms-popup__action">' : '' ),
            ( json.btnNext ? '<button class="joms-button--primary joms-button--small joms-js--button-next">' + json.btnNext + '</button>' : '' ),
            ( json.btnNext ? '</div>' : '' ),
        '</div>',
        '<div class="joms-js--step2 joms-popup__hide">',
            '<div class="joms-popup__content"></div>',
            '<div class="joms-popup__action">',
                '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-back2"></button>',
                '<button class="joms-button--primary joms-button--small joms-js--button-next2"></button>',
            '</div>',
        '</div>',
        '<div class="joms-js--step3 joms-popup__hide">',
            '<div class="joms-popup__content joms-popup__content--single"></div>',
            '<div class="joms-popup__action">',
                '<button class="joms-button--neutral joms-button--small joms-js--button-back3"></button>',
            '</div>',
        '</div>',
        '</div>'
    ].join('');
}

function buildTfaDialog() {
    var lang = window.joms_lang || {};

    return [
        '<div class="joms-popup joms-popup--whiteblock">',
        '<div class="joms-popup__title"><button class="mfp-close" type="button" title="Close (Esc)">×</button>', (lang.COM_COMMUNITY_AUTHENTICATION_KEY || 'Authentication key'), '</div>',
        '<div class="joms-popup__content">',
            '<span>', (lang.COM_COMMUNITY_AUTHENTICATION_KEY_LABEL || 'Insert your two-factor authentication key'), '</span>',
            '<input type="text" class="joms-input" name="secret">',
        '</div>',
        '<div class="joms-popup__action">',
            '<button class="joms-button--neutral joms-button--small joms-left joms-js--button-skip">', (lang.COM_COMMUNITY_SKIP_BUTTON || 'Skip'), '</button>',
            '<button class="joms-button--primary joms-button--small joms-js--button-next">', (lang.COM_COMMUNITY_NEXT || 'Next'), '</button>',
        '</div>',
        '</div>'
    ].join('');
}

// Exports.
return function() {
    joms.util.popup.prepare(function( mfp ) {
        render( mfp );
    });
};

});
