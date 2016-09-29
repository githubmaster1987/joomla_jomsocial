/*
define([ 'core', 'views/inputbox' ], function() {

// CSS selector shortcuts.
var idUiStreamContainer        = '[data-ui-object=stream-container]',
    idUiPostbox                = '[data-ui-object=postbox]',
    idUiLoading                = '[data-ui-object=postbox-loading]',
    idUiTab                    = '[data-ui-object=postbox-tab]',
    idUiTabItem                = '[data-ui-object=postbox-tabitem]',
    idUiInputbox               = '[data-ui-object=postbox-inputbox]',
    idUiActionMood             = '[data-ui-object=postbox-action-mood]',
    idUiActionLocation         = '[data-ui-object=postbox-action-location]',
    idUiActionPrivacy          = '[data-ui-object=postbox-action-privacy]',
    idUiActionPost             = '[data-ui-object=postbox-action-post]',
    idUiActionUpload           = '[data-ui-object=postbox-action-upload]',
    idUiOptionMoods            = '[data-ui-object=postbox-option-moods]',
    idUiOptionMoodsContent     = '[data-ui-object=postbox-option-moods-content]',
    idUiOptionLocations        = '[data-ui-object=postbox-option-locations]',
    idUiOptionLocationsContent = '[data-ui-object=postbox-option-locations-content]',
    idUiOptionPrivacies        = '[data-ui-object=postbox-option-privacies]',
    idUiOptionPrivaciesContent = '[data-ui-object=postbox-option-privacies-content]',
    idUiMetaParameters         = '[data-ui-object=postbox-meta-parameters]',
    idUiMetaState              = '[data-ui-object=postbox-meta-state]';

// CSS classes.
var cssTabItemActive = 'joms-postbox__tabitem--active';

// View definition.
var PostboxView = joms.Backbone.View.extend({

    events: function() {
        var events = {};
        events[ 'click ' + idUiTabItem ]                       = 'changeTab';
        events[ 'click ' + idUiActionMood ]                    = 'toggleMood';
        events[ 'click ' + idUiActionLocation ]                = 'toggleLocation';
        events[ 'click ' + idUiActionPrivacy ]                 = 'togglePrivacy';
        events[ 'click ' + idUiActionPost ]                    = 'postStatus';
        events[ 'click ' + idUiActionUpload ]                  = 'postStatus';
        events[ 'click ' + idUiOptionMoodsContent + ' a' ]     = 'selectMood';
        events[ 'click ' + idUiOptionLocationsContent + ' a' ] = 'selectLocation';
        events[ 'click ' + idUiOptionPrivaciesContent + ' a' ] = 'selectPrivacy';

        // Global click.
        joms.jQuery( document )
            .off( 'click.joms-postbox' )
            .on( 'click.joms-postbox', joms._.bind( this.globalClick, this ));

        return events;
    },

    initialize: function() {
        var prop;

        this.$loading     = this.$( idUiLoading );
        this.$tabs        = this.$( idUiTab );
        this.$tabitems    = this.$( idUiTabItem );
        this.$inputbox    = this.$( idUiInputbox );
        this.$btnmood     = this.$( idUiActionMood );
        this.$btnlocation = this.$( idUiActionLocation );
        this.$btnprivacy  = this.$( idUiActionPrivacy );
        this.$moods       = this.$( idUiOptionMoods );
        this.$locations   = this.$( idUiOptionLocations );
        this.$privacies   = this.$( idUiOptionPrivacies );

        this.inputbox = this.$inputbox.mpInputbox().data('inputbox');

        this.meta = {};
        this.meta.parameters = this.$( idUiMetaParameters ).data() || {};
        this.meta.state = this.$( idUiMetaState ).data() || {};

        for ( prop in this.meta ) {
            delete this.meta[ prop ].uiObject;
        }

        this.meta.mood = false;
        this.meta.location = false;
        this.meta.privacy = false;
    },

    reset: function() {
        this.inputbox.reset();
        this.resetMood();
        this.resetLocation();
        this.resetPrivacy();
    },

    value: function() {
        var text, parameters;

        text = this.inputbox.value();

        // @todo: dummy post a message
        parameters = joms.jQuery.extend({}, this.meta.parameters, {
            type: 'message',
            privacy: this.meta.privacy || 10
        });

        if ( this.meta.mood ) {
            parameters.mood = this.meta.mood;
        }

        // @debug
        joms.log([ text, parameters ]);

        return [ text, JSON.stringify(parameters) ];
    },

    changeTab: function( e ) {
        var item;

        // toggle tabitem
        item = joms.jQuery( e.target ).closest( idUiTabItem );
        this.$tabitems.not( item ).removeClass( cssTabItemActive );
        item.addClass( cssTabItemActive );

        // @todo: toggle inputbox
    },

    toggleMood: function( e ) {
        this.$moods.toggle();
        return false;
    },

    selectMood: function( e ) {
        var item  = joms.jQuery( e.currentTarget ),
            value = item.data('value') || '',
            desc  = item.data('desc') || '',
            image = item.data('image') || '',
            html  = '';

        this.meta.mood = value;
        if ( image ) html += '<img class="joms-emoticon" src="' + image + '">';
        else html += '<i class="joms-emoticon joms-emo-' + value + '"></i>';
        html += '&nbsp;<span>' + desc + '</span>';

        this.inputbox.auxAdd( 'mood', html );
        this.$moods.hide();
        return false;
    },

    resetMood: function() {
        this.meta.mood = false;
    },

    toggleLocation: function( e ) {
        this.$locations.toggle();
        return false;
    },

    selectLocation: function() {
        return false;
    },

    resetLocation: function() {
        this.meta.location = false;
    },

    togglePrivacy: function( e ) {
        this.$privacies.toggle();
        return false;
    },

    selectPrivacy: function( e ) {
        var item  = joms.jQuery( e.currentTarget ),
            value = item.data('value') || '',
            css   = item.data('css') || '';

        this.meta.privacy = value;
        this.$btnprivacy.attr( 'class', css );
        this.$privacies.hide();
        return false;
    },

    resetPrivacy: function() {
        this.meta.privacy = false;
    },

    postStatus: function () {
        var that;

        if ( this.saving ) return;

        this.saving = true;
        this.$loading.show();
        that = this;

        joms.ajax({
            func: 'system,ajaxStreamAdd',
            data: this.value(),
            callback: function( json ) {
                setTimeout(function() {
                    that.saving = false;
                    that.$loading.hide();
                }, 100 );

                if ( json.error ) {
                    alert( json.error );
                    return;
                }

                that.inputbox.reset();

                if ( !that.$stream ) that.$stream = joms.jQuery( idUiStreamContainer );
                that.$stream.html( json.html );
            }
        });
    },

    globalClick: function() {
        this.$moods.hide();
        this.$locations.hide();
        this.$privacies.hide();
    }

});

// Factory.
joms.view || (joms.view = {});
joms.view.postbox = {
    _model: PostboxView,
    _instance: false,
    start: function( elem ) {
        // this._instance = new this._model({ el: elem });
    }
}

});
*/
