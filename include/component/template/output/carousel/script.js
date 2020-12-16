/**
 * FeedZ Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 [COPYRIGHT_HOLDER]
 *
 */
(function($){
    $(document).ready(function(){

        // Local Variables used among different routines
        var _oFeeds        = $( '.feed-zapper-all-feeds' );
        var _sNonce        = _oFeeds.parent().children( '.nonce' ).first().attr( 'data-nonce' );

console.log( 'jquery version: ' + $().jquery );
var version = $.ui ? $.ui.version || "pre 1.6" : 'jQuery-UI not detected';
console.log( 'jquery ui version: ' + version );

        /**
         * Mute Item Action
         */
        $.contextMenu({
            selector: '.feed-item-action-mute',
            /**
             * this callback is executed every time the menu is to be shown
             * its results are destroyed every time the menu is hidden
             * @param trigger
             * @param e the original contextmenu event, containing e.pageX and e.pageY (amongst other data)
             * @returns {{callback: callback, items: {edit: {name: string, icon: string}, cut: {name: string, icon: string}, copy: {name: string, icon: string}, paste: {name: string, icon: string}, delete: {name: string, icon: string}, sep1: string, quit: {name: string, icon: (function(*, *, *): string)}}}}
             */
            build: function( trigger, e) {

                var _oItem = $( e.target ).closest( '.feed-zapper-feed-item' );
                var _sHost = _oItem.attr( 'data-host' );
                var _sSelectedText = _getSelectionText();
                var _oMenuItems    = {
                    "by_site": {
                        "name": "Mute by Web Site",
                        "items": {
                            pattern_site_url: {
                                type: 'text',
                                value: _sHost,
                                events: {
                                    keyup: function(e) {
                                        // add some fancy key handling here?
                                        // window.console && console.log('key: '+ e.keyCode);
                                    }
                                }
                            },
                            "separator1": "---------",
                            "duration1-1" : {
                                name: fzCarousel.labels.one_day,
                                type: 'radio',
                                radio: 'duration',
                                value: 86400
                            },
                            "duration1-2" : {
                                name: fzCarousel.labels.one_week,
                                type: 'radio',
                                radio: 'duration',
                                value: 604800
                            },
                            "duration1-3" : {
                                name: fzCarousel.labels.one_month,
                                type: 'radio',
                                radio: 'duration',
                                value: 1814000
                            },
                            "duration1-4" : {
                                name: fzCarousel.labels.forever,
                                type: 'radio',
                                radio: 'duration',
                                value: -1,
                            },
                            "separator2": "---------",
                            site: { // by ~
                                name: fzCarousel.labels.mute,
                                // callback: $.noop <-- if this set, the `callback` event gets overidden
                            },
                        }
                    },
                    "by_keyword": {
                        "name": "Mute by Keywords",
                        "items": {
                            pattern_keywords: {
                                type: 'text',
                                value: _sSelectedText,
                                events: {
                                    keyup: function(e) {
                                        // add some fancy key handling here?
                                        // window.console && console.log('key: '+ e.keyCode);
                                    }
                                }
                            },
                            "separator1": "---------",
                            "duration2-1" : {
                                name: fzCarousel.labels.one_day,
                                type: 'radio',
                                radio: 'duration2',
                                value: 86400
                            },
                            "duration2-2" : {
                                name: fzCarousel.labels.one_week,
                                type: 'radio',
                                radio: 'duration2',
                                value: 604800
                            },
                            "duration2-3" : {
                                name: fzCarousel.labels.one_month,
                                type: 'radio',
                                radio: 'duration2',
                                value: 1814000
                            },
                            "duration2-4" : {
                                name: fzCarousel.labels.forever,
                                type: 'radio',
                                radio: 'duration2',
                                value: -1,
                            },
                            "separator2": "---------",
                            // "area-label": {"name": "In"},
                            "in_title": {
                                name: fzCarousel.labels.in_title,
                                type: 'checkbox',
                                selected: true
                            },
                            "in_content": {
                                name: fzCarousel.labels.in_content,
                                type: 'checkbox',
                                selected: true
                            },
                            sep4: "---------",
                            keyword: { // by ~
                                name: fzCarousel.labels.mute,
                                // callback: $.noop
                            }
                        },
                        disabled: _sSelectedText ? false : true,
                    },
                    // "edit": {name: "Edit", icon: "edit"},
                    // "sep1": "---------",
                    // "quit": {name: "Quit", icon: function($element, key, item){ return 'context-menu-icon context-menu-icon-quit'; }}
                };

                var _sSelectedMenuItem = '';
                return {
                    callback: function(key, options) {
                        // var m = "clicked: " + key;
                        // window.console && console.log(m) || alert(m);
                        console.log( 'callback: menu item selected: ' + key );
                        _sSelectedMenuItem = key; // update the value to be referenced from the `hide` callback
                    },
                    animation: {duration: 250, show: 'fadeIn', hide: 'fadeOut'},
                    className: 'feed-zapper-item-action-contextmenu feed-zapper-item-action-contextmenu__highlight',
                    items: _oMenuItems,
                    events: {
                        activated: function( options ) {
                            // Fix styles
                            options.$menu.find( 'input[type=radio]' ).each( function( index, value ){
                                var _sMenuItemRadioID = 'context-menu-radio-' + index;
                                $( this ).attr( 'id', _sMenuItemRadioID );
                                $( this ).parent().attr( 'for', _sMenuItemRadioID ); // label tag misses the for attribute
                            } );

                            // @deprecated This causes jittery effects
                            // opt.$menu.find( 'li' ).addClass( 'align-left' );
                            // opt.$menu.find( 'span:contains("' + fzCarousel.labels.mute + '")').parent().addClass( 'align-center' );
                        },
                        show: function( options ) {
                            // import states from data store
                            var _oInputData = {
                                duration: 86400,    // mute by site
                                duration2: 86400,   // mute by keyword
                                pattern_keywords: _sSelectedText,
                                pattern_site_url: _sHost,
                                in_title: true,
                                in_content: true,
                            };
                            $.contextMenu.setInputValues( options, _oInputData );
                        },
                        hide: function( options ) {
                            if ( ! _sSelectedMenuItem ) {
                                return;
                            }
                            var _aInputs = $.contextMenu.getInputValues( options, this.data() );   // menu inputs
                            _aInputs[ 'by' ] = _sSelectedMenuItem;
                            setTimeout(
                                function(){
                                    _handleItemActionMute( _aInputs, _sNonce );
                                },
                                10  // ajax call within the `show` event callback causes an error
                            );
                            _sSelectedMenuItem = '';    // reset
                        }
                    }
                };
            }
        });
            function _handleItemActionMute( aInputs, sNonce ) {

                var _iTimeout = 0;
                var _aMutes   = {};
                var _oInputs  = {
                    in: [],
                    pattern: '',
                };
                switch( aInputs[ 'by' ] ) {
                    case 'site':
                        _oInputs[ 'in' ] = [ 'permalink' ];
                        _oInputs[ 'pattern' ] = aInputs[ 'pattern_site_url' ];
                        // aInputs[ 'pattern' ] = aInputs[ 'pattern_site_url' ];
                        // delete aInputs[ 'in_title' ];
                        // delete aInputs[ 'in_content' ];
                        break;
                    case 'keyword':
                        if ( aInputs[ 'in_content' ] ) {
                            _oInputs[ 'in' ].push( 'content', 'description' );
                        }
                        if ( aInputs[ 'in_title' ] ) {
                            _oInputs[ 'in' ].push( 'title' );
                        }
                        _oInputs[ 'pattern' ] = aInputs[ 'pattern_keywords' ];
                        aInputs[ 'duration' ] = aInputs[ 'duration2' ];
                        // aInputs[ 'pattern' ]  = aInputs[ 'pattern_keywords' ];
                        break;
                    default:
                        $.notify(
                            fzCarousel.labels.something_went_wrong,
                            {
                                position: 'bottom right',
                                className: 'error',
                            }
                        );
                        return;
                }
                // delete aInputs[ 'duration2' ];
                // delete aInputs[ 'pattern_site_url' ];
                // delete aInputs[ 'pattern_keywords' ];
                console.log( 'mute action sending' );

                var _iTimeout = -1 === aInputs[ 'duration' ]
                    ? ( + new Date ) * -1
                    : ( + new Date ) + aInputs[ 'duration' ];
                _aMutes[ _iTimeout ] = _oInputs;
console.log( _aMutes );
// @todo set local data _aMutes for backup
// Also send stored mute items as well
                jQuery.ajax( {
                    type: "post",
                    dataType: 'json',
                    url: fzCarousel.AJAXURL,
                    // Data set to $_POSt and $_REQUEST
                    data: {
                        action: 'feed_zapper_action_mute_feed_item',   // WordPress action hook name which follows after `wp_ajax_`
                        fz_nonce: sNonce,   // the nonce value set in template.php
                        mute_feed_item: _aMutes,
                    },
                    success: function ( response ) {
                        if ( response.success ) {
             //               _setResponseLocalDataByKey( response, 'fz_mute_by_' + fzCarousel.userID );
                            $.notify(
                                {
                                    title: "Muted the item.",
                                },
                                {
                                    position: 'bottom right',
                                    className: 'success',
                                    style: 'foo',
                                    autoHideDelay: 8000,
                                }
                            );
console.log( response );
                        } else {
                            $.notify(
                                "Could not mute the item. Something went wrong.",
                                {
                                    position: 'bottom right',
                                    className: 'error',
                                }
                            );
                        }
                    }
                } ); // ajax

            }
            function _getSelectionText() {
                var text = "";
                var activeEl = document.activeElement;
                var activeElTagName = activeEl ? activeEl.tagName.toLowerCase() : null;
                if (
                  (activeElTagName == "textarea") || (activeElTagName == "input" &&
                  /^(?:text|search|password|tel|url)$/i.test(activeEl.type)) &&
                  (typeof activeEl.selectionStart == "number")
                ) {
                    text = activeEl.value.slice(activeEl.selectionStart, activeEl.selectionEnd);
                } else if (window.getSelection) {
                    text = window.getSelection().toString();
                }
                return text;
            }


        $.contextMenu({
            selector: '.feed-item-action-menu',
            callback: function(key, options) {
                var m = "clicked: " + key;
                window.console && console.log(m) || alert(m);
            },
            className: 'feed-zapper-item-action-contextmenu feed-zapper-item-action-contextmenu__highlight',
            items: {
                "edit": {name: "Edit", icon: "edit"},
                "cut": {name: "Cut", icon: "cut"},
                "copy": {name: "Copy", icon: "copy"},
                "paste": {name: "Paste", icon: "paste"},
                "delete": {name: "Delete", icon: "delete"},
                // "sep1": "---------",
                // "quit": {name: "Quit", icon: function($element, key, item){ return 'context-menu-icon context-menu-icon-quit'; }}
            }
        });

        // Local Variables
        var _iSlideCount   = $( '.feeds' ).length; // the number of channels (tags for now)

        var _iInitialSlide = _getLocalData( 'fz_last_channel_' + fzCarousel.userID );
        _iInitialSlide = 'number' === typeof _iInitialSlide ? _iInitialSlide : 0;
        _iInitialSlide = _iSlideCount < _iInitialSlide ? 0 : _iInitialSlide;  // means somehow the previous channel is gone

        // Slider body
        _oFeeds.on( 'init afterChange', function( event, slick, currentSlide, nextSlide ){
            var _iCurrentSlide = slick.slickCurrentSlide(); // `currentSlide` is null for the `init` event.
            _setLocalData( 'fz_last_channel_' + fzCarousel.userID, _iCurrentSlide );
console.log( 'setting channel: ' + _iCurrentSlide );
            _slickLoad( this, event, slick, _iCurrentSlide, nextSlide );
        });
        _oFeeds.slick( _getSlickSettings( _iInitialSlide ) );

        // Slick Slider Navigation
        $( '.feed-zapper-all-feeds-slider-nav' ).slick( _getSlickSettingsNavigator() );

        function _getSlickSettings( _iInitialSlide ) {
            return {
                dots: true,
                infinite: true,
                 // speed: 300,
                slidesToShow: 1,
                adaptiveHeight: true,
                asNavFor: '.feed-zapper-all-feeds-slider-nav',
                draggable: false,
                fade: true,
                arrows: false,
                initialSlide: _iInitialSlide,
           //    lazyLoad: 'ondemand', // 'progressive',
                vertical: false,    // animateHeight() checks this value
            };
        }
        function _getSlickSettingsNavigator() {
            return {
                slidesToShow: 6,
                slidesToScroll: 1,
                asNavFor: '.feed-zapper-all-feeds',
                dots: true,
                centerMode: true,
                focusOnSelect: true,
                arrows: true,
                swipeToSlide: true,
            };
        }
        /**
         *
         * @param element   the slick applied element that serves as the container
         * @param event
         * @param slick
         * @param iCurrentSlide
         * @param iNextSlide
         * @private
         */
        function _slickLoad( element, event, slick, iCurrentSlide, iNextSlide ) {

            var _oCurrent = $( element ).find( '[data-slick-index="'+(iCurrentSlide)+'"]' );
            var _iLatestTime = _oCurrent.find( '.feed-zapper-feed-item' ).attr( 'data-time' );

            // Retrieve term ids of visible owl items
            // @todo support multiple columns. For that, found term ids will be multiple as multiple visible items(columns).
            // when multiple columns are displayed found term ids will be multiple.
            var _iTermID = _oCurrent.find( '.feed-title' ).attr( 'data-term_id' );
            // var _sTermName = _oCurrent.find( '.feed-title' ).text();
            var aQuery = {
                tax_query: [
                    {
                        taxonomy: fzCarousel.taxonomySlug,
                        field: 'term_id',
                        terms: [ _iTermID ]
                    }
                ]
            };
            if ( 0 == _iTermID ) {  // tag: 'All'
                delete aQuery[ 'tax_query' ];
            }
            if ( -1 == _iTermID ) { // tag: 'Read Later'
                delete aQuery[ 'tax_query' ];
                aQuery[ 'tax_query' ] = [
                    {
                        taxonomy: fzCarousel.taxonomySlugs.feed_action,
                        field: 'name',
                        terms: [ 'read_later_by_' + fzCarousel.userID ],
                    }
                ];
            }
            if ( _iLatestTime ) {
                aQuery[ 'date_query' ] = [
                    {
                        column: 'post_modified',
                        after: _iLatestTime,
                    }
                ];
            }
            _loadFeedItems( aQuery, _oCurrent, element, _sNonce, true );
        }
        /**
         *
         * @param oElem
         * @param event
         * @private
         */
        function _loadFeedItems( aQuery, oCurrent, element, sNonce, bLatest ) {

var _iStartedTime = + new Date;

            var _oContainer = oCurrent.find( '.feed-zapper-feed-container' );
            var _oSpinner   = _getSpinnerAdded( element, _oContainer, bLatest );;

            // Prepare data that is going to be sent to the background
            var _aData = {
                action: 'feed_zapper_action_get_feed_items',   // WordPress action hook name which follows after `wp_ajax_`
                fz_nonce: sNonce,   // the nonce value set in template.php
                wp_query: aQuery
            };
            jQuery.ajax( {
                type: "post",
                url: fzCarousel.AJAXURL,
                // Data set to $_POSt and $_REQUEST
                data: _aData,
                success: function ( response ) {
var _iReceivedResponse = + new Date;
// console.log( 'elapsed response received: ' + ( _iReceivedResponse - _iStartedTime ) / 1000 );
                    // Insert the result
                    // for some reasons, to check an element with find(), the response must have an outer container.
                    // @see https://stackoverflow.com/a/8612928
                    var _oTemp     = $( '<div class="wrapper-for-find-method"></div>' );
                    var _oResponse = $( response ); // plural, holding multiple elements (feed post item/error outputs )

                    _oTemp.append( _oResponse );
                    var _oError    = _oTemp.find( '.feed-error' );
                    var _bHasError = _oError.length;
                    _oResponse.detach();
                    _oTemp.remove(); // clean up

                    // Add response items
                    if ( bLatest ) {
                        if ( ! _bHasError ) {
                            _addResponseItems( element, _oResponse, _oContainer, sNonce, 'prepend' );
                        }
                    } else {
                        _addResponseItems( element, _oResponse, _oContainer, sNonce, 'append' );
                    }
                    // 0.2.1
                    if ( 0 === _oContainer.find( '.feed-zapper-feed-item' ).length ) {
                        _addNoMoreButton( _oContainer, true );
                        return;
                    }


                    // End marker - so when the user reaches the bottom, an event fires
                    if ( ! _bHasError ) {
                        _setEndMarker( _oContainer );
                    }
                    if ( _bHasError ) {
                        _addResponseError( _oError, bLatest, _oContainer, oCurrent );
                    }
                    _addBottomButton( _oContainer, oCurrent, element, sNonce, _bHasError, _oError, bLatest );


                }, // end of success:
                error: function ( response ) {},
                complete: function ( jqXHR, textStatus ) {

                    _oSpinner.remove();

                    // Fix height
                    var _oSlickList    = _oContainer.closest( '.slick-list' );
                    var _oSliderActive = _oContainer.closest( '.slick-slide.slick-active' );
                    if ( _oSliderActive.height() > _oSlickList.height() ) {
                        // often the list container height gets too short
                        _oSlickList.height( _oSliderActive.height() );
                    }
                    $( element ).slick( 'animateHeight' );

                    var _iEndTime = + new Date;

// console.log( 'elapsed total: ' + ( _iEndTime - _iStartedTime ) / 1000 );

                }

            });

        } // _loadFeedItems()
            function _addResponseError( _oError, bLatest, _oContainer, oCurrent ) {
                setTimeout( function(){
                    $( _oError ).fadeOut() },
                    1000
                );
                // Insert the No More button
                if ( ! bLatest ) {
console.log( 'now removing load more button in current: ' + oCurrent.find( '.load-more' ).length );

                    oCurrent.find( '.load-more' ).remove();
console.log( 'now removing load more button in container: ' + _oContainer.find( '.load-more' ).length );
                    _oContainer.find( '.load-more' ).remove();
console.log( 'removed the load more button' );
                    var _bAdded = _addNoMoreButton( _oContainer, true );
console.log( 'whether added No More button: ' + _bAdded );
                }
            }
            /**
             * Adds either the `No More` or `Load More` button.
             * @private
             */
            function _addBottomButton( _oContainer, oCurrent, element, sNonce ) {

                var _bAdded = _addNoMoreButton( _oContainer );
                if ( _bAdded ) {
                    return; // no need for the 'Load More' button
                }
                // Add the Load More button
                _addLoadMoreButton( _oContainer, oCurrent, element, sNonce );

            }
                /**
                 * @since 0.2.1
                 */
                function _addCheckedAboveButtons( oContainer, sNonce, element ) {

                    // remove previously added one @deprecated
                    // oResponse.find( '.checked-above' ).remove();
                    var _oCheckedAboveButton = $( '<div class="align-center checked-above"><div class="margin-bottom2"><button class="feed-zapper-button feed-zapper-button4">' + fzCarousel.labels.checkedAbove + '</button></div></div>' );
                    if ( ! oContainer.find( '.feed-zapper-feed-item' ).length ) {
                        return;
                    }
                    oContainer.find( '.feed-zapper-feed-item:nth-child(10n)' )
                        .after( _oCheckedAboveButton );

                    oContainer.find( '.checked-above' ).click( function() {
                        var _oPreviousAll = $( this ).prevAll( '.feed-zapper-feed-item' );
                        var _sDataKey = 'fz_uninterested_' + fzCarousel.userID;
                        var _aUninterested = _getLocalData( _sDataKey );
                        _oPreviousAll.each( function( index, value ) {
                            var _iPostID = $( this ).attr( 'data-post_id' );
                            if ( ! _iPostID ) {
                                return true;    // continue
                            }
                            var _iNow = + new Date;
                            var _iTimeIndex = _iNow + ( index * 0.001 );
                            _aUninterested[ _iTimeIndex ] = _iPostID;
                        });
                        _setLocalData( _sDataKey, _aUninterested );
                        _dismissItems( _aUninterested, sNonce );
                        _oPreviousAll.fadeOut( 500 );
                        $( this ).fadeOut( 500 );
                        $( this ).prevAll( '.checked-above' ).fadeOut( 500 );
                        $( element ).slick( 'animateHeight' );
                        $( [document.documentElement, document.body] ).animate({
                            scrollTop: $( element ).offset().top - 50
                        }, 1000);
                    } );

                }
                /**
                 *
                 * @param _oContainer
                 * @private
                 */
                function _addNoMoreButton( _oContainer, bForce ) {

                    // Check if it reaches the last item.
                    if ( ! bForce && ! _oContainer.find( '.feed-zapper-feed-item.last-item' ).length ) {
                        return false;
                    }
                    // Already the button exists
                    if ( _oContainer.find( '.no-more' ).length ) {
                        return true;
                    }

                    // Insert a No More button
                    var _oNoMoreButton = $( '<div class="align-center no-more"><div class="margin-bottom2"><button disabled class="feed-zapper-button feed-zapper-button4">' + fzCarousel.labels.noMore + '</button></div></div>' );
                    _oContainer.append( _oNoMoreButton );
                    return true; // no need for the Load More button

                }

                function _addLoadMoreButton( _oContainer, oCurrent, element, sNonce ) {
                    if ( _oContainer.find( '.load-more' ).length ) {
                        return;
                    }
                    var _oLoadMoreButton = $( '<div class="align-center load-more"><div class="margin-bottom2"><button class="feed-zapper-button feed-zapper-button4">' + fzCarousel.labels.loadMore + '</button></div></div>' );
                    _oContainer.append( _oLoadMoreButton );
                    _oLoadMoreButton.click( function() {

                        _oLoadMoreButton.remove();
                        var _oLastItem   = oCurrent.find( '.feed-zapper-feed-item' ).last();
                        var _iOldestTime = _oLastItem.attr( 'data-time' );
                        if ( ! _iOldestTime ) {
                            return false;
                        }

                        // Retrieve term ids of visible owl items
                        // @todo support multiple columns. For that found term ids will be multiple as multiple visible items(columns).
                        // when multiple columns are displayed found term ids will be multiple.
                        var _iTermID = oCurrent.find( '.feed-title' ).attr( 'data-term_id' );
                        // var _sTermName = _oCurrent.find( '.feed-title' ).text();
                        var aQuery = {
                            tax_query: [
                                {
                                    taxonomy: fzCarousel.taxonomySlug,
                                    field: 'term_id',
                                    terms: [ _iTermID ]
                                }
                            ],
                            date_query: [
                                {
                                    column: 'post_modified',
                                    before: _iOldestTime,
                                }
                            ]
                        };
                        if ( 0 == _iTermID ) { // tag: 'All'
                            delete aQuery[ 'tax_query' ];
                        }
                        _loadFeedItems( aQuery, oCurrent, element, sNonce, false );

                    } );

                }
            function _setEndMarker( _oContainer ) {
                var _oEndmarker = $( '<img class="end-marker" data-src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" />' );
                _oContainer.append( _oEndmarker );
                _oEndmarker.lazy({
                    beforeLoad: function( element ) {
                        _oContainer.find( '.load-more' ).click();
                    },
                    onFinishedAll: function() {
                        _oEndmarker.remove();
                    }
                });
            }
            function _getSpinnerAdded( element, _oContainer, bLatest ) {
                var _oSpinner   = $( '<div class="align-center"><img src="' + fzCarousel.spinnerURL + '" /></div>' )
                    .css( 'margin-bottom', '2em' )
                    .hide();
                if ( bLatest ) {
                    _oContainer.prepend(
                        _oSpinner.show()
                        // @deprecated 0.2.3 Causes an error in jQuery 3.5
                        // _oSpinner.fadeIn( 1, function() {
                        //     $( element ).slick( 'animateHeight' );
                        // } )
                    );
                } else {
                    _oContainer.append( _oSpinner.fadeIn( 500 ) );
                }
console.log( 'loading ' + ( bLatest ? 'latest' : 'older' ) + ' ...' );
                return _oSpinner;
            }

            /**
             * @remark  fadeIn() and image lazy load make adjustable height inaccurate. So at the moment, they are added normally.
             * @private
             */
            function _addResponseItems( element, oResponse, oContainer, sNonce, sMethodName ) {

                var _oWrap     = $( '<div class="wrapper-response"></div>' );   // the find() method needs the element to be inserted in DOM
                _oWrap.append( oResponse );
                oContainer[ sMethodName ]( _oWrap );  // method name: `append` or `prepend`
                _addCheckedAboveButtons( _oWrap, sNonce, element );
                _oWrap.children( '.feed-zapper-feed-item' ).unwrap();

                // Lazy load images
                oResponse.find( '.feed-zapper-feed-item-image > img' ).lazy( {
                    afterLoad: _debounce( function( e ) {
                        $( element ).slick( 'animateHeight' );
                        console.log( 'animated height' );
                    }, 250 ),
                });

                // @see https://stackoverflow.com/a/30814911
                // @deprecated not working well
                // $( element ).slick( 'unslick' ); /* ONLY remove the classes and handlers added on initialize */
                // oResponse.closest( '.slick-slide.slick-active' ).remove(); /* Remove current slides elements, in case that you want to show new slides. */
                // $( element ).slick( _getSlickSettings() ); /* Initialize the slick again */

                _handleFeedItemActions( oResponse, sNonce );

console.log( 'added response items' );

            }
                /**
                 * Returns a function, that, as long as it continues to be invoked, will not
                 * be triggered. The function will be called after it stops being called for
                 * N milliseconds. If `immediate` is passed, trigger the function on the
                 * leading edge, instead of the trailing.
                 * @param func
                 * @param wait
                 * @param immediate
                 * @returns {Function}
                 * @see https://stackoverflow.com/a/40728112
                 * @see https://davidwalsh.name/javascript-debounce-function
                 */
                function _debounce(func, wait, immediate) {
                    var timeout;
                    return function() {
                        var context = this, args = arguments;
                        var later = function() {
                            timeout = null;
                            if (!immediate) func.apply(context, args);
                        };
                        var callNow = immediate && !timeout;
                        clearTimeout(timeout);
                        timeout = setTimeout(later, wait);
                        if (callNow) func.apply(context, args);
                    };
                };

            function _handleFeedItemActions( oResponse, sNonce ) {

                _handleVisited( oResponse, sNonce );
                _handleDismissed( oResponse, sNonce );
                _handleReadLater( oResponse, sNonce );
                _handleItemActionMenu( oResponse, sNonce );

            }
                function _handleItemActionMenu( oResponse, sNonce ) {

                    oResponse.find( '.feed-item-action-menu' ).on( 'click', function(e) {
                        $( this ).trigger( "contextmenu" );
                    });
                    oResponse.find( '.feed-item-action-mute' ).on( 'mousedown', function(e) {
                        e.preventDefault();
                        var _aPos = $( this ).position();
                        // $( this ).contextMenu( {x: _aPos.left + 300 , y: _aPos.top + 220 } );
                        $( this ).contextMenu();
                        //$( this ).trigger( "contextmenu" );
                        // or $('.context-menu-one').contextMenu({x: 100, y: 100});
                        return false;
                    });

                }
                function _handleReadLater( oResponse, sNonce ) {

                    oResponse.find( '.feed-item-action-read-later' ).on( 'click', function( e ){
                        
                        $( this ).closest( '.feed-zapper-feed-item' ).fadeOut( 500 );
                        var _sItemID = $( this ).closest( '.feed-zapper-feed-item' ).attr( 'data-id' );
                        var _iPostID = $( this ).closest( '.feed-zapper-feed-item' ).attr( 'data-post_id' );
                        if ( ! _iPostID ) {
                            return;
                        }
                        var _sDataKey = 'fz_read_later_' + fzCarousel.userID;
                        var _aReadLater = _getLocalData( _sDataKey );
                        _aReadLater[ + new Date ] = _iPostID;
                        _aReadLater = _truncateObject( _aReadLater, 2000 );
                        _setLocalData( _sDataKey, _aReadLater );
                        
                        jQuery.ajax( {
                            type: "post",
                            dataType: 'json',
                            url: fzCarousel.AJAXURL,
                            // Data set to $_POSt and $_REQUEST
                            data: {
                                action: 'feed_zapper_action_read_later_feed_item',   // WordPress action hook name which follows after `wp_ajax_`
                                fz_nonce: sNonce,   // the nonce value set in template.php
                                read_later_feed_post_ids: _aReadLater
                            },
                            success: function ( response ) {
                                if ( response.success ) {
                                    _setResponseLocalDataByKey( response, 'fz_read_later_' + fzCarousel.userID );
                                } else {
                                    // console.log( 'something went wrong' );
                                }
                            }
                        } ); // ajax
                        
                    } );

                }

                function _handleDismissed( oResponse, sNonce ) {

                    oResponse.find( '.feed-item-action-dismiss' ).on( 'click', function( e ){
console.log( 'dismiss button clicked' );

                        $( this ).closest( '.feed-zapper-feed-item' ).hide();
                        var _sItemID = $( this ).closest( '.feed-zapper-feed-item' ).attr( 'data-id' );
                        var _iPostID = $( this ).closest( '.feed-zapper-feed-item' ).attr( 'data-post_id' );
                        if ( ! _iPostID ) {
console.log( 'post id not found' );
                            return;
                        }
console.log( 'making ajax call. nonce: ' + sNonce + ' post id: ' + _iPostID );
console.log( 'dismissed: ' + _iPostID );
                        var _sDataKey = 'fz_uninterested_' + fzCarousel.userID;
                        var _aUninterested = _getLocalData( _sDataKey );
                        _aUninterested[ + new Date ] = _iPostID;
                        _aUninterested = _truncateObject( _aUninterested, 2000 );
                        _setLocalData( _sDataKey, _aUninterested );

                        _dismissItems( _aUninterested, sNonce )

                    } );
                } // end of _handleDismissed()
                    function _dismissItems( aItems, sNonce ) {
                        jQuery.ajax( {
                            type: "post",
                            dataType: 'json',
                            url: fzCarousel.AJAXURL,
                            // Data set to $_POSt and $_REQUEST
                            data: {
                                action: 'feed_zapper_action_uninterested_feed_item',   // WordPress action hook name which follows after `wp_ajax_`
                                fz_nonce: sNonce,   // the nonce value set in template.php
                                uninterested_feed_post_ids: aItems
                            },
                            success: function ( response ) {
                                if ( response.success ) {
                                    _setResponseLocalDataByKey( response, 'fz_uninterested_' + fzCarousel.userID );
                                } else {
                                    // console.log( 'something went wrong' );
                                }
                            }
                        });
                    }

    function _getLocalData( sKey ) {
        if ( 'undefined' === typeof( Storage ) ) {
            return {};
        }
        var _oData = localStorage.getItem( sKey );
        _noData = JSON.parse( _oData );
        return ! _noData        // when not set, it is `null`
            ? {}
            : _noData;

    }
    function _setLocalData( sKey, oData ) {
        if ( 'undefined' === typeof( Storage ) ) {
            return;
        }
        // Store
        localStorage.setItem( sKey, JSON.stringify( oData ) );
    }

            /**
             *
             * @see Click Tracking https://stackoverflow.com/a/4255130
             * @see Middle Button Click Event https://stackoverflow.com/a/41110766
             */
            function _handleVisited( element, sNonce ) {

                $( element ).find( '.feed-zapper-feed-title > a' ).on( 'click auxclick', function( e ){
    // console.log( 'clicked: ' + this.href );
                    try {
                        if ( '_blank' !== $( link ).attr( 'target' ) ) {
                            e.preventDefault(); // disable the default browser behaviour
                            setTimeout( 'document.location = "' + link.href + '"', 100 );
                        }
                    } catch( err ){}

                    var _sItemID = $( this ).closest( '.feed-zapper-feed-item' ).attr( 'data-id' );
                    var _iPostID = $( this ).closest( '.feed-zapper-feed-item' ).attr( 'data-post_id' );
                    if ( ! _iPostID ) {
    console.log( 'post id not found (_trackClick)' );
                        return true;
                    }

                    // Set a cookie
                    var _aVisited = _getLocalData( 'fz_visited_' + fzCarousel.userID );
    // console.log( _aVisited );
    console.log( 'visited: ' + _iPostID );
                    _aVisited[ + new Date ] = _iPostID;
                    _aVisited = _truncateObject( _aVisited, 2000 );

    console.log( '_aVisited' );
    console.log( _aVisited );


                    _setLocalData( 'fz_visited_' + fzCarousel.userID, _aVisited  );

                    jQuery.ajax({
                        type: "post",
                        dataType: 'json',
                        url: fzCarousel.AJAXURL,
                        // Data set to $_POSt and $_REQUEST
                        data: {
                            action: 'feed_zapper_action_collect_clicked_feed_items',   // WordPress action hook name which follows after `wp_ajax_`
                            fz_nonce: sNonce,   // the nonce value set in template.php
                            visited_feed_post_id: _aVisited
                        },
                        success: function (response) {
console.log( 'success response' );
console.log( typeof response );
console.log( response );
                            if ( response.success ) {
                                _setResponseLocalDataByKey( response, 'fz_visited_' + fzCarousel.userID );
                            } else {
                                // console.log( 'something went wrong' );
                            }
                        }
                    });
                    return true;

                } );


            } // end of _handleVisited()

        function _setResponseLocalDataByKey( response, sDataKey ) {

            var _aData = _getLocalData( sDataKey );
console.log( 'local data' );
console.log( _aData );
console.log( 'response result' );
console.log( response.result );
            for ( var _iTime in response.result ) {
                // skip inherited properties
                if ( ! response.result.hasOwnProperty( _iTime ) ) {
                   continue;
                }
console.log( _iTime, response.result[ _iTime ] );
                delete _aData[ _iTime ];
            }
            _setLocalData( sDataKey, _aData );
console.log( 'local data visited' );
console.log( _aData );
        }        
                /**
                 * @remark  it seems not possible to sort objects by key in JavaScript.
                 * So the object is indexed from the smallest number to the largest.
                 * @param obj
                 * @param iTruncate
                 * @private
                 */
                function _truncateObject( obj, iTruncate ) {
                    var keys = Object.keys( obj ), _i, _k, _iLen = keys.length;

                    var _oNew = {};
                    var _iMax = _iLen > iTruncate ? iTruncate : _iLen;
                    for ( _i = 0; _i < _iMax; _i++ ) {
                        _k = keys[ _iLen - ( 1 + _i ) ];    // insert from the largest key
                        _oNew[ _k ] = obj[ _k ];
                    }
                    return _oNew;
                }

                /**
                 * @see Cookies https://www.w3schools.com/js/js_cookies.asp
                 * @private
                 * @deprecated
                 */
                function _setCookie(cname, cvalue, exdays) {
                    var d = new Date();
                    d.setTime(d.getTime() + (exdays*24*60*60*1000));
                    var expires = "expires="+ d.toUTCString();
        console.log( 'setting cookie' );
        console.log( cvalue );
                    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
                }
                /**
                 * @deprecated
                 * @param cname
                 * @returns {string}
                 * @private
                 */
                function _getCookie(cname) {
                    var name = cname + "=";
                    var decodedCookie = decodeURIComponent(document.cookie);
                    var ca = decodedCookie.split(';');
                    for(var i = 0; i <ca.length; i++) {
                        var c = ca[i];
                        while (c.charAt(0) == ' ') {
                            c = c.substring(1);
                        }
                        if (c.indexOf(name) == 0) {
                            return c.substring(name.length, c.length);
                        }
                    }
                    return "";
                }
                function _toType(obj) {
                  return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase()
                }
                function _isJsonString(str) {
                    try {
                        JSON.parse(str);
                    } catch (e) {
                        return false;
                    }
                    return true;
                }

        /**
         * Notify
         */
        $.notify.addStyle( 'foo', { //add a new style 'foo'
            html:
                "<div>" +
                    "<div class='clearfix'>" +
                        "<div class='title' data-notify-html='title'/>" +
                        "<div class='buttons'>" +
                            "<button class='undo'>Undo</button>" +
                            // "<button class='yes'><span data-notify-text/></button>" +
                        "</div>" +
                    "</div>" +
                "</div>"
        });
        //listen for click events from this style
        $(document).on('click', '.notifyjs-foo-base .undo', function() {
            //programmatically trigger propogating hide event
            alert($(this).text() + " clicked!");
            $(this).trigger('notify-hide');
        });

    }); // document ready

}(jQuery));