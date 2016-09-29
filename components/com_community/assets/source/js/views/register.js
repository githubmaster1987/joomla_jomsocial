/*
define([ 'core' ], function() {

var MIN_PASS_LENGTH = 6;

var idName    = 'input-name',
    idFname   = 'input-firstname',
    idLname   = 'input-lastname',
    idUname   = 'input-username',
    idEmail   = 'input-email',
    idPass    = 'input-password',
    idVpass   = 'input-verify-password',
    idAuth    = 'hidden-authenticate',
    idAuthKey = 'hidden-authkey',
    idSubmit  = 'button-submit';

function RegisterView() {}

RegisterView.prototype.start = function( elem ) {
    this.detachHandler();
    this.elem = joms.jQuery( elem );

    this.authKey = '';

    // Cache elements.
    this.$name    = this.elem.find( '[data-ui-object=' + idName + ']' );
    this.$fname   = this.elem.find( '[data-ui-object=' + idFname + ']' );
    this.$lname   = this.elem.find( '[data-ui-object=' + idLname + ']' );
    this.$uname   = this.elem.find( '[data-ui-object=' + idUname + ']' );
    this.$email   = this.elem.find( '[data-ui-object=' + idEmail + ']' );
    this.$pass    = this.elem.find( '[data-ui-object=' + idPass + ']' );
    this.$vpass   = this.elem.find( '[data-ui-object=' + idVpass + ']' );
    this.$auth    = this.elem.find( '[data-ui-object=' + idAuth + ']' );
    this.$authkey = this.elem.find( '[data-ui-object=' + idAuthKey + ']' );
    this.$submit  = this.elem.find( '[data-ui-object=' + idSubmit + ']' );

    this.lang = this.elem.find('[data-ui-object=languages]').data();
    this.errors = {};
    this.attachHandler();
};

RegisterView.prototype.stop = function() {
    this.detachHandler();
};

RegisterView.prototype.attachHandler = function() {
    if ( !this.elem ) return;
    this.elem.on( 'blur.register', 'input[type=text]', joms._.bind( this.checkInput, this ));
    this.elem.on( 'blur.register', 'input[type=password]', joms._.bind( this.checkInput, this ));
    this.elem.on( 'keyup.register', 'input[data-ui-object=' + idPass + ']', joms._.bind( this.checkPassStrength, this ));
    this.elem.on( 'submit.register', joms._.bind( this.submit, this ));
};

RegisterView.prototype.detachHandler = function() {
    if ( this.elem ) this.elem.off();
};

RegisterView.prototype.checkInput = function( e ) {
    var elem = joms.jQuery( e.currentTarget ),
        id = elem.data('ui-object');

    if ( id === idName ) {
        this.checkName();
    } else if ( id === idFname ) {
        this.checkFname();
    } else if ( id === idLname ) {
        this.checkLname();
    } else if ( id === idUname ) {
        this.checkUname();
    } else if ( id === idEmail ) {
        this.checkEmail();
    } else if ( id === idPass ) {
        this.checkPass();
    } else if ( id === idVpass ) {
        this.checkVpass();
    }
};

// General field checking.
RegisterView.prototype.precheck = function( elem, value ) {
    var error = false,
        field;

    if ( !elem.__pholder ) {
        elem.__pholder = elem.attr('placeholder');
    }

    if ( !value ) {
        field = elem.__pholder || this.lang.field;
        error = field + ' ' + this.lang.msgInvalid;
    }

    return error;
};

// Name field checking.
RegisterView.prototype.checkName = function() {
    var elem  = this.$name,
        value = elem.val(),
        error = this.precheck( elem, value );

    if ( !error && value.length < 3 ) {
        error = this.lang.msgNameTooShort;
    }

    this.toggleError( elem, idName, error );
    this.updateSubmitButton();
};

// First-name field checking.
RegisterView.prototype.checkFname = function() {
    var elem  = this.$fname,
        value = elem.val(),
        error = this.precheck( elem, value );

    this.toggleError( elem, idFname, error );
    this.updateSubmitButton();
};

// Last-name field checking.
RegisterView.prototype.checkLname = function() {
    var elem  = this.$lname,
        value = elem.val(),
        error = this.precheck( elem, value );

    this.toggleError( elem, idLname, error );
    this.updateSubmitButton();
};

// Username field checking.
RegisterView.prototype.checkUname = function() {
    var elem  = this.$uname,
        value = elem.val(),
        error = this.precheck( elem, value ),
        that  = this;

    if ( error ) {
        this.toggleError( elem, idUname, error );
        this.updateSubmitButton();
        return;
    }

    joms.ajax({
        func: 'register,ajaxCheckUserName',
        data: [ value ],
        callback: function( json ) {
            json.error && ( error = json.error );
            that.toggleError( elem, idUname, error );
            that.updateSubmitButton();
        }
    });
};

// Email field checking.
RegisterView.prototype.checkEmail = function() {
    var elem   = this.$email,
        value  = elem.val(),
        error  = this.precheck( elem, value ),
        rEmail = /^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i,
        that   = this;

    if ( !error && !value.match( rEmail ) ) {
        error = this.lang.msgEmailInvalid;
    }

    if ( error ) {
        this.toggleError( elem, idEmail, error );
        this.updateSubmitButton();
        return;
    }

    joms.ajax({
        func: 'register,ajaxCheckEmail',
        data: [ value ],
        callback: function( json ) {
            json.error && ( error = json.error );
            that.toggleError( elem, idEmail, error );
            that.updateSubmitButton();
        }
    });
};

// Password field checking.
RegisterView.prototype.checkPass = function() {
    var elem  = this.$pass,
        value = elem.val(),
        error = this.precheck( elem, value );

    if ( !error && value.length < MIN_PASS_LENGTH ) {
        error = this.lang.msgPassTooShort;
    }

    this.toggleError( elem, idPass, error );
    this.updateSubmitButton();
};

// Verify-password field checking.
RegisterView.prototype.checkVpass = function() {
    var elem  = this.$vpass,
        value = elem.val(),
        error = this.precheck( elem, value );

    if ( !error && value !== this.$pass.val() ) {
        error = this.lang.msgPassNotSame;
    }

    this.toggleError( elem, idVpass, error );
    this.updateSubmitButton();
};

// http://stackoverflow.com/questions/948172/password-strength-meter
RegisterView.prototype.scorePassword = function( pass ) {
    var score = 0;
    if (!pass)
        return score;

    // award every unique letter until 5 repetitions
    var letters = new Object();
    for (var i=0; i<pass.length; i++) {
        letters[pass[i]] = (letters[pass[i]] || 0) + 1;
        score += 5.0 / letters[pass[i]];
    }

    // bonus points for mixing it up
    var variations = {
        digits: /\d/.test(pass),
        lower: /[a-z]/.test(pass),
        upper: /[A-Z]/.test(pass),
        nonWords: /\W/.test(pass),
    },

    variationCount = 0;
    for (var check in variations) {
        variationCount += (variations[check] == true) ? 1 : 0;
    }
    score += (variationCount - 1) * 10;

    return parseInt(score);
};

// Password strength meter.
RegisterView.prototype.checkPassStrength = function() {
    var wrapper = this.$pass.closest('.joms-input--wrapper'),
        pwdstr  = wrapper.children('.joms-input--pwdstr'),
        score   = this.scorePassword( this.$pass.val() ),
        html    = '',
        i;

    if ( score > 70 ) {
        score = 5;
    } else if ( score > 50 ) {
        score = 4;
    } else if ( score > 40 ) {
        score = 3;
    } else if ( score > 25 ) {
        score = 2;
    } else if ( score > 0 ) {
        score = 1;
    } else {
        score = 0;
    }

    pwdstr.length || (pwdstr = joms.jQuery('<div class="joms-input--pwdstr">').appendTo( wrapper ));
    for ( i = 1; i <= score; i++ ) {
        html += '<div class="joms-input--pwdstr__item joms-input--pwdstr__' + score + '"></div>';
    }
    pwdstr.html( html );
};

// Toggle error based on error string value.
RegisterView.prototype.toggleError = function( elem, id, error ) {
    var wrapper = elem.closest('.joms-input--wrapper'),
        label = wrapper.children('.joms-input--errormsg');

    if ( error ) {
        this.errors[ id ] = error;
        elem.addClass('is-error');
        label.length || (label = joms.jQuery('<div class="joms-input--errormsg">').appendTo( wrapper ));
        label.html( error );
    } else {
        delete this.errors[ id ];
        elem.removeClass('is-error');
        label.length && label.remove();
    }
};

RegisterView.prototype.updateSubmitButton = function() {
    var disable = joms._.size( this.errors );
    this.$submit[ disable ? 'attr' : 'removeAttr' ]( 'disabled', 'disabled' );
};

RegisterView.prototype.submit = function( e ) {
    var disable = joms._.size( this.errors ),
        that = this;

    if ( disable ) return false;
    if ( this.authKey ) return true;

    joms.ajax({
        func: 'register,ajaxGenerateAuthKey',
        data: [],
        callback: function( json ) {
            if ( !json.authKey ) {
                that.authKey = '';
                that.$authkey.val('');
                that.$auth.val( 0 );
            } else {
                that.authKey = json.authKey;
                that.$authkey.val( json.authKey );
                that.$auth.val( 1 );
                that.$submit.click();
            }
        }
    });

    return false;
};

// Export as `joms.view.stream`.
joms.view || (joms.view = {});
joms.view.register = new RegisterView();

});
*/
