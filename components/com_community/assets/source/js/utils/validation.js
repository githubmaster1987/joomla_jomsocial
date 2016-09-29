(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.validation = factory( root, $ );

})( window, joms.jQuery, function( window, $ ) {

var STATUS_VALID = 'valid',
    STATUS_INVALID = 'invalid';

function getErrorContainer( el ) {
    var wrapper, error;

    el = $( el );
    wrapper = el.parent('.joms-select--wrapper');
    error = wrapper.length ? wrapper.next('p.joms-help') : el.next('p.joms-help');

    if ( !error.length ) {
        error = $('<p class="joms-help" style="color:red">');
        error.hide().insertAfter( wrapper.length ? wrapper : el );
    }

    return error;
}

function addRequiredSign() {
    $('.joms-form__group').find('[required]')
        .add( $('.joms-form__group').find('[data-required]') )
        .each(function() {
            var el = $( this ),
                par = el.closest('.joms-form__group'),
                label = par.children().first();

            if ( !label.find('.joms-required').length ) {
                label.append(' <span class="joms-required">*</span>');
            }
        });
}

function addTextareaMaxChars() {
    var textarea = $('.joms-form__group').find('textarea[data-maxchars]');

    textarea.css('display', 'inline');
    textarea.wrap('<div style="position:relative"></div>');
    textarea.parent().append('<div class="joms-js--textarea-counter" style="position:absolute;bottom:2px;right:5px"></div>');
    textarea.each(function() {
        var $el = $( this );
        $el.siblings('.joms-js--textarea-counter').html( $el.data('maxchars') );
    });

    textarea.off('input').on( 'input', function() {
        var $el = $( this ),
            $counter = $el.siblings('.joms-js--textarea-counter'),
            maxChars = $el.data('maxchars') || 0,
            val;

        if ( maxChars ) {
            val = $el.val();
            if ( val.length > maxChars ) {
                val = val.substr( 0, maxChars );
                $el.val( val );
            }
            $counter.html( maxChars - val.length );
        }
    });
}

function addValidationTrigger() {
    $('.joms-form__group').find('[required]')
        .add( $('.joms-form__group').find('[data-required]') )
        .off('blur').on( 'blur', function( e, callback ) {
            var el      = $( this ),
                error   = getErrorContainer( el ),
                name    = el.attr('name'),
                type    = ( el.attr('type') || '' ).toLowerCase(),
                tagName = el[0].tagName.toLowerCase(),
                label   = $.trim( el.closest('.joms-form__group').children().first().text().replace( /\*/g, '' ) ),
                val     = $.trim( el.val() ),
                validation;

            if ( type === 'text' || type === 'password' || tagName === 'select'  || tagName === 'textarea') {
                if ( typeof callback !== 'function' ) {
                    callback = function() {
                        // trigger validation if confirmation field is not empty
                        var verify = el.data('verify');
                        if ( verify ) {
                            verify = $( verify );
                            verify.length && $.trim( verify.val() ) && verify.triggerHandler('blur');
                        }
                    };
                }

                if ( !val ) {
                    setMessage( el, [ name, label, 'COM_COMMUNITY_REGISTER_INVALID_VALUE' ]);
                    callback( STATUS_INVALID );
                } else {
                    validation = el.data('validation') || '';
                    if ( validation === 'username' ) {
                        validateUsername( el, val, callback, el.data('current') || '' );
                    } else if ( validation === 'email' ) {
                        validateEmail( el, val, callback );
                    } else if ( validation.match(/^email:/) ) {
                        validateEmailConfirmation( el, val, callback );
                    } else if ( validation === 'password' ) {
                        validatePassword( el, val, callback );
                     } else if ( validation.match(/^password:/) ) {
                        validatePasswordConfirmation( el, val, callback );
                    } else {
                        error.fadeOut();
                        callback( STATUS_VALID );
                    }
                }
            }
        });
}

function validateUsername( el, username, callback, current ) {
    joms.ajax({
        func: 'register,ajaxCheckUserName',
        data: [ username, current ],
        callback: function( json ) {
            var error = getErrorContainer( el );
            if ( json.error ) {
                error.html( json.error );
                error.show();
                callback( STATUS_INVALID );
            } else {
                error.fadeOut();
                callback( STATUS_VALID );
            }
        }
    });
}

function validateEmail( el, email, callback ) {
    var reEmail = /^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,8})$/i;

    if ( !reEmail.test( email ) ) {
        setMessage( el, [ '', '', 'COM_COMMUNITY_INVALID_EMAIL' ]);
        callback( STATUS_INVALID );
        return;
    }

    joms.ajax({
        func: 'register,ajaxCheckEmail',
        data: [ email ],
        callback: function( json ) {
            var error = getErrorContainer( el );
            if ( json.error ) {
                error.html( json.error );
                error.show();
                callback( STATUS_INVALID );
            } else {
                error.fadeOut();
                callback( STATUS_VALID );
            }
        }
    });
}

function validateEmailConfirmation( el, value, callback ) {
    var data, ref, error;

    data  = el.data('validation').split(':');
    ref   = $( data[1] );
    error = getErrorContainer( el );

    if ( !ref.length ) {
        error.fadeOut();
        callback( STATUS_VALID );
        return;
    }

    if ( value !== ref.val() ) {
        setMessage( el, [ '', '', 'COM_COMMUNITY_REGISTER_EMAIL_NOT_SAME' ]);
        callback( STATUS_INVALID );
        return;
    }

    error.fadeOut();
    callback( STATUS_VALID );
}

function validatePassword( el, value, callback ) {
    joms.ajax({
        func: 'register,ajaxCheckPass',
        data: [ value ],
        callback: function( json ) {
            var error = getErrorContainer( el );
            if ( json.error ) {
                error.html( json.error.replace(/\n/g, '<br/>') );
                error.show();
                callback( STATUS_INVALID );
            } else {
                error.fadeOut();
                callback( STATUS_VALID );
            }
        }
    });
}

function validatePasswordConfirmation( el, value, callback ) {
    var data, ref, error;

    data  = el.data('validation').split(':');
    ref   = $( data[1] );
    error = getErrorContainer( el );

    if ( !ref.length ) {
        error.fadeOut();
        callback( STATUS_VALID );
        return;
    }

    if ( value !== ref.val() ) {
        setMessage( el, [ '', '', 'COM_COMMUNITY_REGISTER_PASSWORD_NOT_SAME' ]);
        callback( STATUS_INVALID );
        return;
    }

    error.fadeOut();
    callback( STATUS_VALID );
}

function validate( $form, callback ) {
    var errors = 0,
        counter = 0,
        $fields, fieldsCount;

    $form = $( $form );
    $fields = $form.find('[required]').add( $form.find('[data-required]') );
    fieldsCount = $fields.length;

    if ( !$fields.length ) {
        callback( errors );
    }

    $fields.each(function() {
        var $el = $( this );
        $el.triggerHandler( 'blur', function( result ) {
            if ( result === STATUS_INVALID ) {
                errors++;
            }
            if ( ++counter >= fieldsCount ) {
                callback( errors );
            }
        });
    });

    return false;
}

function setMessage( el, data ) {
    joms.ajax({
        func: 'register,ajaxSetMessage',
        data: data,
        callback: function( json ) {
            var error = getErrorContainer( el );
            error.html( json.message );
            error.show();
        }
    });
}

function start() {
    addRequiredSign();
    addTextareaMaxChars();
    addValidationTrigger();
}

function stop() {

}

// Exports.
return {
    start: start,
    stop: stop,
    validate: validate
};

});
