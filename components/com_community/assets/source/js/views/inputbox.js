/*
;(function( $ ) {

    var VK_ENTER   = 13,
        VK_KEYUP   = 38,
        VK_KEYDOWN = 40;

    var cssBase        = 'joms-inputbox',
        cssWrapper     = cssBase + '__wrapper',
        cssBox         = cssBase + '__box',
        cssContent     = cssBase + '__content',
        cssBeautifier  = cssBase + '__beautifier',
        cssAux         = cssBase + '__aux',
        cssTextarea    = cssBase + '__textarea',
        cssPlaceholder = cssBase + '__placeholder',
        cssList        = cssBase + '__list',
        cssListItem    = cssList + '--item',
        cssListActive  = cssList + '--active';

    var rHashtags = /(^|#|\s)(#[^#\s]+)/g,
        rTags     = /@\[(\d+):([^\]]+)\]/g,
        rTag      = /@\[(\d+):([^\]]+)\]/;

    var isPlaceholderSupported;

    function Inputbox( elem, options ) {
        var $textarea,
            $beautifier,
            $aux,
            $list,
            $hidden,
            textarea,
            hidden,
            placeholder,
            delay,
            tagAdded,
            lastCursorStartPos,
            lastCursorEndPos,
            lastTextareaValue,
            listIsMouseDown,
            listIsOpened,
            listSelected,
            fetchUrl,
            fetchCache,
            auxData;

        elem = $( elem );
        options = joms.jQuery.extend({}, options || {});

        if ( typeof isPlaceholderSupported === 'undefined' ) {
            isPlaceholderSupported = ( 'placeholder' in document.createElement('textarea') );
        }

        function initialize() {
            var prebuild = !elem[0].tagName.match( /^(input|textarea)$/i ),
                $ct, value;

            $ct         = prebuild ? elem : $( buildHtml() );
            $textarea   = $ct.find( '.' + cssTextarea );
            $beautifier = $ct.find( '.' + cssBeautifier );
            $aux        = $ct.find( '.' + cssAux );
            $list       = $ct.find( '.' + cssList );
            $hidden     = $ct.find( '[type=hidden]' );

            textarea    = $textarea[0];
            hidden      = $hidden[0];
            tagAdded    = [];

            fetchCache  = {};
            fetchUrl    = options.url || '';

            auxData     = {};

            placeholder = ( prebuild ? $textarea : elem ).attr('placeholder') || '';
            if ( !prebuild && isPlaceholderSupported ) {
                $textarea.prop( 'placeholder', placeholder );
            }

            value = elem.val();
            if ( value ) updateFromHidden( value );

            if ( !prebuild ) elem.replaceWith( $ct );
        }

        function fetch( keyword, callback ) {
            var data = [],
                matches = [],
                url, i;

            if ( fetchCache[ keyword ] ) {
                callback( fetchCache[ keyword ] );
                return;
            }

            // Dummy data.
            data.push({ id: 1, name: 'Ariyo Galih' });
            data.push({ id: 2, name: 'Alex Hee' });
            data.push({ id: 3, name: 'Rudy Dvlr' });
            data.push({ id: 4, name: 'Andy Februanto' });
            data.push({ id: 5, name: 'Maya Feny Wijaya' });
            data.push({ id: 6, name: 'Fify Jannatul Firda' });

            for ( i = 0; i < data.length; i++ ) {
                matches.push( data[i] );
            }

            callback( matches );
        }

        function lookup() {
            var cpos   = textarea.selectionStart,
                substr = textarea.value.substr( 0, cpos ),
                index  = substr.lastIndexOf( '@' );

            if ( index < 0 || ++index >= cpos ) {
                listHide();
                return;
            }

            substr = substr.substring( index, cpos );
            fetch( substr, listUpdate );
        }

        function buildHtml() {
            return [
                '<div class=', cssBase, '>',
                '<div class=', cssBox, '><div class=', cssWrapper, '>',
                '<div class=', cssContent, '><div class=', cssBeautifier, '></div><div class=', cssAux, '></div></div>',
                '<textarea class=', cssTextarea, '></textarea></div></div>',
                '<div class=', cssList, '></div>',
                '<input type=hidden>',
                '</div>'
            ].join('');
        }

        function insertTag( tag ) {
            var start = tag.start,
                length = tag.length,
                i = 0;

            for ( ; i < tagAdded.length; i++ ) {
                if ( tagAdded[i].start >= start )
                    break;
            }

            tagAdded.splice( i++, 0, tag );
            for ( ; i < tagAdded.length; i++ ) {
                tagAdded[i].start += length;
            }
        }

        function removeTag( index ) {
            tagAdded.splice( index, 1 );
        }

        function updateFromHidden( value ) {
            var tags, tag, start, data, id, name;

            hidden.value = value;
            textarea.value = value.replace( rTags, '$2' );
            setTimeout( updateBeautifier, 1 );

            tags = value.match( rTags ) || [];
            tagAdded = [];

            for ( ; tags.length; ) {
                tag   = tags.shift();
                start = value.indexOf( tag );
                data  = tag.match( rTag );
                id    = data[1];
                name  = data[2];
                value = value.replace( tag, name );
                insertTag({
                    start: start,
                    length: name.length,
                    data: { id: id, name: name }
                });
            }
        }

        function updateFromTextarea( cursorStartPos, cursorEndPos ) {
            var cursorDeltaPos = cursorStartPos - lastCursorStartPos,
                tag, marker, regexp, value, i;

            for ( i = 0; i < tagAdded.length; i++ ) {
                tag = tagAdded[ i ];
                if ( lastCursorStartPos <= tag.start )
                    tag.start += cursorDeltaPos;
                else if ( lastCursorStartPos <= tag.start + tag.length && cursorDeltaPos < 0 ) {
                    tagAdded.splice( i--, 1 );
                }
            }

            // Split.
            regexp = '';
            marker = 0;
            for ( i = 0; i < tagAdded.length; i++ ) {
                tag = tagAdded[ i ];
                regexp += '(.{' + ( tag.start - marker ) + '})';
                regexp += '(.{' + ( tag.length ) + '})';
                marker = tag.start + tag.length;
            }
            regexp = RegExp( '^' + regexp + '(.*)$' );
            value = textarea.value.match( regexp );
            value.shift();

            for ( i = 0; i < tagAdded.length; i++ ) {
                value[ i * 2 + 1 ] = '@[' + tagAdded[i].data.id +  ':' + tagAdded[i].data.name + ']';
            }

            value = value.join('');
            hidden.value = value;
            setTimeout( updateBeautifier, 1 );
        }

        function updateBeautifier() {
            var html, height;

            html = hidden.value
                .replace( rTags, '<b>$2</b>' )
                .replace( rHashtags, '$1<b>$2</b>' )
                .replace( /\n/g, '<br>' )
                .replace( /<br>$/, '<br>&nbsp;' );

            $beautifier.html( html || placeholder );
            if ( !isPlaceholderSupported ) {
                $beautifier[ html ? 'removeClass' : 'addClass' ]( cssPlaceholder );
            }

            if ( $textarea.height() !== ( height = $beautifier.height() ) ) {
                $textarea.css({ height: height });
            }
        }

        function textareaKeydown( e ) {
            if ( listIsOpened ) {
                if ( e.keyCode === VK_ENTER || e.keyCode === VK_KEYUP || e.keyCode === VK_KEYDOWN ) {
                    return false;
                }
            }

            lastCursorStartPos = textarea.selectionStart;
            lastCursorEndPos   = textarea.selectionEnd;
            lastTextareaValue  = textarea.value;
        }

        function textareaKeyup( e ) {
            if ( listIsOpened ) {
                if ( e.keyCode === VK_KEYUP || e.keyCode === VK_KEYDOWN ) {
                    listChangeItem( e.keyCode );
                    return false;
                }

                if ( e.keyCode === VK_ENTER ) {
                    listSelectItem();
                    return false;
                }
            }

            if ( textarea.selectionStart !== lastCursorStartPos ) {
                clearTimeout( delay );
                delay = setTimeout( lookup, 100 );
            }
        }

        function textareaInput() {
            if ( textarea.value !== lastTextareaValue )
                updateFromTextarea( textarea.selectionStart, textarea.selectionEnd );
        }

        function textareaBlur() {
            listIsMouseDown || listHide();
        }

        function listUpdate( matches ) {
            var html, item, i, length;

            if ( !( matches && matches.length ) ) {
                listHide();
                return;
            }

            html = '';
            length = Math.min( 10, matches.length );
            for ( i = 0; i < length; i++ ) {
                item = matches[ i ];
                html += '<div class=' + cssListItem + ' data-id="' + item.id +  '" data-name="' + item.name + '">';
                html += item.name + '</div>';
            }

            $list.html( html );
            $list.show();
            listIsOpened = true;
            listSelected = false;
        }

        function listChangeItem( e ) {
            var elem, sibs, next;

            if ( typeof e !== 'number' ) {
                elem = listSelected = $( e.target );
                sibs = elem.siblings( '.' + cssListActive );
                elem.addClass( cssListActive );
                sibs.removeClass( cssListActive );
                return;
            }

            elem = $list.children( '.' + cssListActive );
            if ( !elem.length ) {
                elem = listSelected = $list.children()[ e === VK_KEYUP ? 'last' : 'first' ]();
                elem.addClass( cssListActive );
                return;
            }

            next = elem[ e === VK_KEYUP ? 'prev' : 'next' ]();
            elem.removeClass( cssListActive );
            if ( next.length ) {
                listSelected = next;
                next.addClass( cssListActive );
            } else {
                listSelected = false;
            }
        }

        function listSelectItem() {
            var cpos, str, start, length, data, regexp, marker, value;

            if ( listSelected ) {
                cpos  = textarea.selectionStart;
                str   = textarea.value.substr( 0, cpos );
                start = str.lastIndexOf('@');
                data  = listSelected.data();

                textarea.value = textarea.value.replace( RegExp( '^(.{' + start + '})(.{' + (cpos - start) + '})(.*)$' ), '$1'+data.name+'$3' );
                textarea.selectionStart = textarea.selectionEnd = start + data.name.length
                insertTag({
                    start: start,
                    length: data.name.length,
                    data: { id: data.id, name: data.name }
                });

                // Split.
                regexp = '';
                marker = 0;
                for ( i = 0; i < tagAdded.length; i++ ) {
                    tag = tagAdded[ i ];
                    regexp += '(.{' + ( tag.start - marker ) + '})';
                    regexp += '(.{' + ( tag.length ) + '})';
                    marker = tag.start + tag.length;
                }
                regexp = RegExp( '^' + regexp + '(.*)$' );
                value = textarea.value.match( regexp );
                value.shift();

                for ( i = 0; i < tagAdded.length; i++ ) {
                    value[ i * 2 + 1 ] = '@[' + tagAdded[i].data.id +  ':' + tagAdded[i].data.name + ']';
                }

                value = value.join('');
                hidden.value = value;
                updateBeautifier();
            }

            listIsMouseDown = false;
            listHide();
        }

        function listHide() {
            $list.hide();
            listIsOpened = false;
        }

        function listMouseEnter( e ) {
            listChangeItem( e );
        }

        function listMouseDown() {
            listIsMouseDown = true;
        }

        function listMouseUp() {
            listSelectItem();
            listIsMouseDown = false;
            listHide();
        }

        function auxPrint() {
            var data = [];

            if ( auxData.mood ) {
                data.push( auxData.mood );
            }

            if ( !data.length ) {
                $textarea.attr( 'placeholder', placeholder );
                data = '';
            } else {
                $textarea.removeAttr('placeholder');
                data = ' &mdash; ' + data.join(' and ');
            }

            $aux.html( data );
        }

        function initializeEditor() {
            $textarea.on( 'click focus keydown', textareaKeydown );
            $textarea.on( 'keyup', textareaKeyup );
            $textarea.on( 'input', textareaInput );
            $textarea.on( 'blur', textareaBlur );
        }

        function initializeList() {
            $list.on( 'mouseenter', '.' + cssListItem, listMouseEnter );
            $list.on( 'mousedown', '.' + cssListItem, listMouseDown );
            $list.on( 'mouseup', '.' + cssListItem, listMouseUp );
        }

        initialize();
        initializeEditor();
        initializeList();

        // Public methods.
        return {
            value: function() {
                return hidden.value;
            },
            reset: function() {
                auxData = {};
                auxPrint();
                updateFromHidden( hidden.value = '' );
            },
            auxAdd: function( id, html ) {
                auxData[ id ] = html;
                auxPrint();
            },
            auxRemove: function( id ) {
                delete auxData[ id ];
                auxPrint();
            }
        };
    }

    // Attach to jQuery plugin.
    $.fn.mpInputbox = function( options ) {
        return this.each(function() {
            $.data( this, 'inputbox' ) || $.data( this, 'inputbox', new Inputbox( this, options ) );
        });
    };

})( joms.jQuery );
*/
