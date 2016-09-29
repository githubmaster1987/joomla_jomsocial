define([ 'core', 'utils/map' ], function() {

function Popup() {}

Popup.prototype.prepare = function( callback ) {
    var mfp, that;

    if ( joms.jQuery.magnificPopup ) {
        mfp = this.showPopup();
        callback( mfp );
        return;
    }

    that = this;
    this.loadlib(function() {
        if ( joms.jQuery.magnificPopup ) {
            mfp = that.showPopup();
            callback( mfp );
        }
    });
};

Popup.prototype.showPopup = function() {
    joms.jQuery.magnificPopup.open({
        type: 'inline',
        items: { src: [] },
        tClose: window.joms_lang.COM_COMMUNITY_CLOSE_BUTTON,
        tLoading: window.joms_lang.COM_COMMUNITY_POPUP_LOADING
    });

    var mfp = joms.jQuery.magnificPopup.instance,
        className = 'joms-popup__wrapper';

    if ( joms.mobile ) {
        className += ' joms-popup__mobile';
    }

    mfp.container.addClass( className );
    mfp.updateStatus('loading');

    mfp.container
        .off('click.joms-closepopup', '.joms-js--button-close')
        .on('click.joms-closepopup', '.joms-js--button-close', function() {
            mfp.close();
        });

    return mfp;
};

Popup.prototype.loadlib = function( callback ) {
    callback();
};

// Factory.
joms.util || (joms.util = {});
joms.util.popup = new Popup();

});
