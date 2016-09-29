(function( root, $, factory ) {

    joms.util || (joms.util = {});
    joms.util.tagging = factory( root, $ );

    // Also register as jQuery plugin.
    $.fn.jomsTagging = function( extraFetch ) {
        return this.each(function() {
            joms.util.tagging( this, extraFetch );
        });
    };

})( window, joms.jQuery, function( window, $ ) {

var

// Virtual keys.
VK_ENTER   = 13,
VK_ESC     = 27,
VK_KEYUP   = 38,
VK_KEYDOWN = 40,

// Namespace.
namespace = 'joms-tagging',

// CSS selectors.
cssTextarea           = '.joms-textarea',
cssWrapper            = cssTextarea + '__wrapper',
cssBeautifier         = cssTextarea + '__beautifier',
cssHidden             = cssTextarea + '__hidden',
cssDropdown           = cssTextarea + '__tag-ct',
cssDropdownItem       = cssTextarea + '__tag-item',
cssDropdownItemActive = cssDropdownItem + '--active',

// Regular expressions.
rTags           = /@\[\[(\d+):contact:([^\]]+)\]\]/g,
rTag            = /@\[\[(\d+):contact:([^\]]+)\]\]/,
rHashTag        = /(^|#|\s)(#[^#\s]+)/g,
rHashTagReplace = '$1<b>$2</b>',
rEol            = /\n/g,
rEolReplace     ='<br>';

function Tagging( textarea, extraFetch ) {
    this.textarea = textarea;
    this.fetcher = extraFetch || false;
    this.$textarea = $( textarea );
    this.$textarea.data( 'initialValue', textarea.value );
    this.$textarea.data( namespace, this );
    this.$textarea.on( 'focus.' + namespace, $.proxy( this.initialize, this ) );

    return this;
}

Tagging.prototype.initialize = function() {
    var value, tags, match, start, i;

    this.dropdownIsOpened     = false;
    this.dropdownIsClicked    = false;
    this.dropdownSelectedItem = false;

    this.domPrepare();
    // this.inputPrepare();

    this.tagsAdded = [];
    value = '';

    if ( this.$textarea.data('initialValue') ) {
        value = this.textarea.value;
        tags = value.match( rTags );
        this.textarea.value = value.replace( rTags, '$2' );
        if ( tags && tags.length ) {
            for ( i = 0; i < tags.length; i++ ) {
                match = tags[i].match( rTag );
                start = value.indexOf( tags[i] );
                value = value.replace( tags[i], match[2] );
                this.tagsAdded.push({
                    id     : match[1],
                    name   : match[2],
                    start  : start,
                    length : match[2].length
                });
            }
        }
    }

    this.beautifierUpdate( value, this.tagsAdded );
    this.hiddenUpdate( value, this.tagsAdded );
    this.inputAutogrow();

    this.$textarea
        .off( 'focus.'   + namespace ).on( 'focus.'   + namespace, $.proxy( this.inputOnKeydown, this ) )
        .off( 'click.'   + namespace ).on( 'click.'   + namespace, $.proxy( this.inputOnKeydown, this ) )
        .off( 'keydown.' + namespace ).on( 'keydown.' + namespace, $.proxy( this.inputOnKeydown, this ) )
        .off( 'keyup.'   + namespace ).on( 'keyup.'   + namespace, $.proxy( this.inputOnKeyup, this ) )
        .off( 'input.'   + namespace ).on( 'input.'   + namespace, $.proxy( this.inputOnInput, this ) )
        .off( 'blur.'    + namespace ).on( 'blur.'    + namespace, $.proxy( this.inputOnBlur, this ) );

    this.$dropdown
        .off(  'mouseenter.' + namespace ).on( 'mouseenter.' + namespace, cssDropdownItem, $.proxy( this.dropdownOnMouseEnter, this ) )
        .off(  'mousedown.'  + namespace ).on( 'mousedown.'  + namespace, cssDropdownItem, $.proxy( this.dropdownOnMouseDown, this ) )
        .off(  'mouseup.'    + namespace ).on( 'mouseup.'    + namespace, cssDropdownItem, $.proxy( this.dropdownOnMouseUp, this ) );

    this.textarea.joms_beautifier = this.$beautifier;
    this.textarea.joms_hidden = this.$hidden;


    var that = this;
    this.textarea.joms_reset = function() {
        that.inputReset();
    };

};

Tagging.prototype.domPrepare = function() {
    this.$wrapper = this.$textarea.parent( cssWrapper );
    if ( !this.$wrapper.length ) {
        this.$textarea.wrap( '<div class="' + cssWrapper.substr(1) + '"></div>' );
        this.$wrapper = this.$textarea.parent();
    }

    this.$beautifier = this.$wrapper.children( cssBeautifier );
    if ( !this.$beautifier.length ) {
        this.$beautifier = $( '<div class="' + cssTextarea.substr(1) + ' ' + cssBeautifier.substr(1) + '"></div>' );
        this.$beautifier.prependTo( this.$wrapper );
    }

    this.$hidden = this.$wrapper.children( cssHidden );
    if ( !this.$hidden.length ) {
        this.$hidden = $( '<input type="hidden" class="' + cssHidden.substr(1) + '">' );
        this.$hidden.appendTo( this.$wrapper );
    }

    this.$dropdown = this.$wrapper.children( cssDropdown );
    if ( !this.$dropdown.length ) {
        this.$dropdown = $( '<div class="' + cssDropdown.substr(1) + '"></div>' );
        this.$dropdown.appendTo( this.$wrapper );
    }
};

Tagging.prototype.inputPrepare = function() {

};

// @todo
Tagging.prototype.inputReset = function() {
    if ( this.tagsAdded ) {
        this.tagsAdded = [];
    }

    // console.log('inputReset');
    // this.tagsAdded = [];
    // this.$hidden.val();
    // this.$textarea.val();
    // this.$beautifier.html( text );
    // this.$textarea.trigger( 'reset.' + namespace );
};

Tagging.prototype.inputAutogrow = function() {
    var prevHeight = +this.$textarea.data( namespace + '-prevHeight' ),
        height;

    this.$wrapper.css({ height: prevHeight });
    this.$textarea.css({ height: '' });

    height = this.textarea.scrollHeight + 2;
    this.$textarea.css({ height: height });
    if ( height !== +prevHeight ) {
        this.$textarea.data( namespace + '-prevHeight', height );
    }

    this.$wrapper.css({ height: '' });
};

Tagging.prototype.inputOnKeydown = function( e ) {
    // Catch dropdown navigation buttons.
    if ( this.dropdownIsOpened ) {
        if ([ VK_ENTER, VK_ESC, VK_KEYUP, VK_KEYDOWN ].indexOf( e.keyCode ) >= 0 ) {
            return false;
        }
    }

    // Reset input to initial state if Esc button is pressed.
    if ( e.keyCode === VK_ESC ) {
        this.inputReset();
        return false;
    }

    this.prevSelStart = this.textarea.selectionStart;
    this.prevSelEnd = this.textarea.selectionEnd;
};

Tagging.prototype.inputOnKeyup = function( e ) {
    if ( this.dropdownIsOpened ) {
        if ( e.keyCode === VK_KEYUP || e.keyCode === VK_KEYDOWN ) {
            this.dropdownChangeItem( e.keyCode );
            return false;
        }

        if ( e.keyCode === VK_ENTER ) {
            this.dropdownSelectItem();
            return false;
        }

        if ( e.keyCode === VK_ESC ) {
            this.dropdownHide();
            return false;
        }
    }
};

Tagging.prototype.inputOnInput = function() {
    var value = this.textarea.value,
        delta, tag, length, name, tmp, index, rMatch, rReplace, shift, i, j;

    // Shift tags position.
    if ( this.tagsAdded ) {

        // if text is selected (selectionStart !== selectionEnd)
        if ( this.prevSelStart !== this.prevSelEnd ) {
            for ( i = 0; i < this.tagsAdded.length; i++ ) {
                tag = this.tagsAdded[i];
                length = tag.start + tag.length;
                if (
                    // Intersection.
                    ( this.prevSelStart > tag.start && this.prevSelStart < length ) ||
                    ( this.prevSelEnd > tag.start && this.prevSelEnd < length ) ||
                    // Enclose.
                    ( tag.start >= this.prevSelStart && length <= this.prevSelEnd )
                ) {
                    this.tagsAdded.splice( i--, 1 );
                }
            }
        }

        delta = this.textarea.selectionStart - this.prevSelStart - ( this.prevSelEnd - this.prevSelStart );

        for ( i = 0; i < this.tagsAdded.length; i++ ) {
            tag = this.tagsAdded[i];

            // Tag's start is in right of or exactly at cursor position.
            if ( tag.start >= this.prevSelStart ) {
                tag.start += delta;
            } else {
                length = tag.start + tag.length;

                // Tag's end is in left of cursor position.
                if ( length < this.prevSelStart ) {
                    // do nothing

                // Cursor position is inside a tag.
                } else if ( length > this.prevSelStart ) {
                    // Not backspace.
                    if ( delta > 0 ) {
                        this.tagsAdded.splice( i--, 1 );
                    // Backspace.
                    } else if ( delta < 0 ) {
                        name = value.substring( tag.start, this.prevSelStart + delta );
                        index = name.split(' ').length - 1;
                        name = tag.name.split(' ');
                        name.splice( index, 1 );
                        name = name.join(' ');

                        tmp = tag.name.split(' ');
                        tmp = tmp.slice( 0, index );
                        tmp = tmp.join(' ');

                        rMatch = new RegExp( '^([\\s\\S]{' + tag.start + '})([\\s\\S]{' + ( tag.length + delta ) + '})' );
                        rReplace = '$1' + name;
                        this.textarea.value = this.textarea.value.replace( rMatch, rReplace );
                        this.textarea.setSelectionRange(tag.start + tmp.length, tag.start + tmp.length);

                        value = this.textarea.value;
                        shift = tag.length - name.length;
                        tag.name = name;
                        tag.length = name.length;

                        for ( j = i + 1; j < this.tagsAdded.length; j++ ) {
                            this.tagsAdded[j].start -= shift;
                        }

                        if ( !name.length ) {
                            this.tagsAdded.splice( i--, 1 );
                        }

                        i = this.tagsAdded.length;

                    }

                // Tag's end is exactly at cursor position... and a backspace is pressed.
                } else if ( delta < 0 ) {
                    name = tag.name.split(' ');
                    name.pop();
                    name = name.join(' ');

                    rMatch = new RegExp( '^([\\s\\S]{' + tag.start + '})([\\s\\S]{' + ( tag.length + delta ) + '})' );
                    rReplace = '$1' + name;
                    this.textarea.value = this.textarea.value.replace( rMatch, rReplace );
                    this.textarea.setSelectionRange(tag.start + name.length, tag.start + name.length);

                    value = this.textarea.value;
                    shift = tag.length - name.length;
                    tag.name = name;
                    tag.length = name.length;

                    for ( j = i + 1; j < this.tagsAdded.length; j++ ) {
                        this.tagsAdded[j].start -= shift;
                    }

                    if ( !name.length ) {
                        this.tagsAdded.splice( i--, 1 );
                    }

                    i = this.tagsAdded.length;
                }
            }
        }
    }

    this.inputAutogrow();
    this.beautifierUpdate( value, this.tagsAdded || [] );
    this.hiddenUpdate( value, this.tagsAdded || [] );
    this.dropdownToggle();
};

Tagging.prototype.inputOnBlur = function() {
    this.dropdownIsClicked || this.dropdownHide();
};

Tagging.prototype.beautifierUpdate = joms._.debounce(function( value, tags ) {
    var rMatch, rReplace, start, tag, i;

    if ( tags.length ) {
        rMatch = '^';
        rReplace = '';
        start = 0;

        for ( i = 0; i < tags.length; i++ ) {
            tag = tags[i];
            rMatch += '([\\s\\S]{' + ( tag.start - start ) + '})([\\s\\S]{' + tag.length + '})';
            rReplace += '$' + ( i * 2 + 1 ) + '[b]' + tag.name + '[/b]';
            start = tag.start + tag.length;
        }

        rMatch = new RegExp( rMatch );
        value = value.replace( rMatch, rReplace );
    }

    value = value.replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
    value = value.replace( /\[(\/?b)\]/g, '<$1>' );
    value = value.replace( rHashTag, rHashTagReplace );
    value = value.replace( rEol, rEolReplace );

    this.$beautifier.html( value );

}, joms.mobile ? 100 : 1 );

Tagging.prototype.hiddenUpdate = joms._.debounce(function( value, tags ) {
    var rMatch, rReplace, start, tag, i;

    if ( tags.length ) {
        rMatch = '^';
        rReplace = '';
        start = 0;

        for ( i = 0; i < tags.length; i++ ) {
            tag = tags[i];
            rMatch += '([\\s\\S]{' + ( tag.start - start ) + '})([\\s\\S]{' + tag.length + '})';
            rReplace += '$' + ( i * 2 + 1 ) + '@[[' + tag.id + ':contact:' + tag.name + ']]';
            start = tag.start + tag.length;
        }

        rMatch = new RegExp( rMatch );
        value = value.replace( rMatch, rReplace );
    }

    this.$hidden.val( value );

}, joms.mobile ? 500 : 50 );

Tagging.prototype.dropdownToggle = joms._.debounce(function() {
    var cpos   = this.textarea.selectionStart,
        substr = this.textarea.value.substr( 0, cpos ),
        index  = substr.lastIndexOf('@');

    if ( index < 0 || ++index >= cpos ) {
        this.dropdownHide();
        return;
    }

    substr = substr.substring( index, cpos );

    this.dropdownFetch( substr, joms._.bind( this.dropdownUpdate, this ) );

}, joms.mobile ? 1000 : 200 );

Tagging.prototype.dropdownFetch = function( keyword, callback, friends ) {
    var source  = ( window.joms_friends || [] ).concat( friends || [] ),
        added   = this.tagsAdded || [],
        matches = [],
        uniques = [],
        item, name, isAdded, that, i, j;

    // Map data-source.
    if ( source && source.length ) {
        keyword = keyword.toLowerCase();
        for ( i = 0; (i < source.length) && (matches.length < 20); i++ ) {
            item = source[i];
            name = ( item.name || '' ).toLowerCase();
            if ( name.indexOf( keyword ) >= 0 ) {
                isAdded = false;
                for ( j = 0; j < added.length; j++ ) {
                    if ( +item.id === +added[j].id ) {
                        isAdded = true;
                        break;
                    }
                }

                if ( !isAdded && uniques.indexOf( +item.id ) < 0 ) {
                    uniques.push( +item.id );
                    matches.push({
                        id: item.id,
                        name: item.name,
                        img: item.avatar
                    });
                }
            }
        }
    }

    matches.sort(function( a, b ) {
        if ( a.name < b.name ) return -1;
        if ( a.name > b.name ) return 1;
        return 0;
    });

    callback( matches );

    if ( typeof this.fetcher === 'function' && !friends ) {
        that = this;
        this.fetcher(function( friends ) {
            friends || (friends = []);
            that.dropdownFetch( keyword, joms._.bind( that.dropdownUpdate, that ), friends );
        });
    }
};

Tagging.prototype.dropdownUpdate = function( matches ) {
    var html, item, cname, i, length;

    if ( !( matches && matches.length ) ) {
        this.dropdownHide();
        return;
    }

    html = '';
    cname = cssDropdownItem.substr(1);
    length = Math.min( 10, matches.length );
    for ( i = 0; i < length; i++ ) {
        item = matches[ i ];
        html += '<a href="javascript:" class=' + cname + ' data-id="' + item.id +  '" data-name="' + item.name + '">';
        html += '<img src="' + item.img + '">' + item.name + '</a>';
    }

    this.dropdownShow( html );
};

Tagging.prototype.dropdownShow = function( html ) {
    this.$dropdown.html( html ).show();
    this.dropdownIsOpened = true;
    this.dropdownSelectedItem = false;
};

Tagging.prototype.dropdownHide = function() {
    this.$dropdown.hide();
    this.dropdownIsOpened = false;
};

Tagging.prototype.dropdownOnMouseEnter = function( e ) {
    this.dropdownChangeItem( e );
};

Tagging.prototype.dropdownOnMouseDown = function() {
    this.dropdownIsClicked = true;
};

Tagging.prototype.dropdownOnMouseUp = function( e ) {
    this.dropdownSelectItem( e );
    this.dropdownIsClicked = false;
    this.dropdownHide();
};

Tagging.prototype.dropdownChangeItem = function( e ) {
    var className = cssDropdownItemActive.substr(1),
        elem, sibs, next;

    if ( typeof e !== 'number' ) {
        elem = this.dropdownSelectedItem = $( e.target );
        sibs = elem.siblings( cssDropdownItemActive );
        elem.addClass( className );
        sibs.removeClass( className );
        return;
    }

    elem = this.$dropdown.children( cssDropdownItemActive );
    if ( !elem.length ) {
        elem = this.dropdownSelectedItem = this.$dropdown.children()[ e === VK_KEYUP ? 'last' : 'first' ]();
        elem.addClass( className );
        return;
    }

    next = elem[ e === VK_KEYUP ? 'prev' : 'next' ]();
    elem.removeClass( className );
    if ( next.length ) {
        this.dropdownSelectedItem = next;
        next.addClass( className );
    } else {
        this.dropdownSelectedItem = false;
    }
};

Tagging.prototype.dropdownSelectItem = function( e ) {
    var el       = e ? $( e.currentTarget ) : this.dropdownSelectedItem,
        id       = el.data('id'),
        name     = el.data('name'),
        cpos     = this.textarea.selectionStart,
        substr   = this.textarea.value.substr( 0, cpos ),
        index    = substr.lastIndexOf('@'),
        re, value;

    this.tagsAdded || (this.tagsAdded = []);
    this.tagsAdded.push({
        id     : id,
        name   : name,
        start  : index,
        length : name.length
    });

    re = new RegExp( '^([\\s\\S]{' + index + '})[\\s\\S]{' + ( cpos - index ) + '}' );
    value = this.textarea.value.replace( re, '$1' + name );
    this.textarea.value = value;
    this.inputAutogrow();

    this.beautifierUpdate( value, this.tagsAdded );
    this.hiddenUpdate( value, this.tagsAdded );
    this.dropdownHide();
};

// Public.
Tagging.prototype.clear = function() {
    this.tagsAdded = [];
    this.$textarea && this.$textarea.val('');
    this.$hidden && this.$hidden.val('');
    this.$beautifier && this.$beautifier.empty();
};

// Exports.
return function( textarea, extraFetch ) {
    var instance = $( textarea ).data( namespace );

    if ( instance ) {
        return instance;
    } else {
        return new Tagging( textarea, extraFetch );
    }
};

});
