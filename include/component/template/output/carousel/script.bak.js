/**
 * FeedZ Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 [COPYRIGHT_HOLDER]
 *
 */
(function($){
    $(document).ready(function(){
        var _oFeeds = $( '.feed-zapper-all-feeds' );
        var _sNonce  = _oFeeds.parent().children( '.nonce' ).first().attr( 'data-nonce' );
        _oFeeds.owlCarousel(
            {
                loop:true,
                margin:10,
                nav:true,
                items: 1,
                mouseDrag: false,
                touchDrag: true,
                onInitialized: function( event ) {
                    if ( ! event.item.count ) {
                        return;
                    }
                    // console.log("current: ", event.relatedTarget.current())
                    // console.log("current: ", event.item.index) //same
                    // console.log("total: ", event.item.count)   //total
                    var element = event.target;         // DOM element, in this example .owl-carousel
                    // retrieve term ids of visible owl items
                    // @todo support multiple columns. For that found term ids will be multiple as multiple visible items(columns).
                    // when multiple columns are displayed found term ids will be multiple.
                    var _iTermID = $( element ).find( '.owl-item.active' ).find( '.feeds-title' ).attr( 'data-term_id' );
                    var _sTermName = $( element ).find( '.owl-item.active' ).find( '.feeds-title' ).text();
console.log( 'term id: ' + _iTermID );
console.log( 'term name: ' + _sTermName );
                    var aQuery = {
                        tax_query: [
                            {
                                taxonomy: fzCarousel.taxonomySlug,
                                field: 'term_id',
                                terms: [ _iTermID ]
                            }
                        ]
                    };
                    _loadFeedItems( aQuery, $( this ), event, _sNonce  );
                }
            }
        );
        /**
         * Called when the slider finishes loading.
         */
        _oFeeds.on( 'translated.owl.carousel', function( event ) {
            if ( ! event.item.count ) {
                return;
            }

            var element = event.target;         // DOM element, in this example .owl-carousel

            var _iLatestTime = $( element ).find( '.owl-item.active' ).find( '.feed-zapper-feed-item' ).attr( 'data-time' );
console.log( 'latest: ' + _iLatestTime );
            // Retrieve term ids of visible owl items
            // @todo support multiple columns. For that found term ids will be multiple as multiple visible items(columns).
            // when multiple columns are displayed found term ids will be multiple.
            var _iTermID = $( element ).find( '.owl-item.active' ).find( '.feeds-title' ).attr( 'data-term_id' );
            var _sTermName = $( element ).find( '.owl-item.active' ).find( '.feeds-title' ).text();
console.log( 'term id: ' + _iTermID );
console.log( 'term name: ' + _sTermName );
            var aQuery = {
                tax_query: [
                    {
                        taxonomy: fzCarousel.taxonomySlug,
                        field: 'term_id',
                        terms: [ _iTermID ]
                    }
                ]
            };
            if ( _iLatestTime ) {
                aQuery[ 'date_query' ] = [
                    {
                        column: 'post_modified',
                        after: _iLatestTime,
                    }
                ];
            }
            _loadFeedItems( aQuery, $( this ), event, _sNonce  );
        });
        /**
         *
         * @param oElem
         * @param event
         * @private
         */
        function _loadFeedItems( aQuery, oElem, event, sNonce ) {

            // Provided by the core
            // @see https://owlcarousel2.github.io/OwlCarousel2/docs/api-events.html
            var element = event.target;         // DOM element, in this example .owl-carousel
            var name = event.type;           // Name of the event, in this example dragged
            var namespace = event.namespace;      // Namespace of the event, in this example owl.carousel
            var items = event.item.count;     // Number of items
            var item = event.item.index;     // Position of the current item
            // Provided by the navigation plugin
            var pages = event.page.count;     // Number of pages
            var page = event.page.index;     // Position of the current page
            var size = event.page.size;      // Number of items per page

            $('#feed-post-error').addClass('hidden');    // make sure the previous error message is not displayed
            var _oSpinner = $('#feed-post-spinner');
            _oSpinner.addClass('is-active');

            var _aData = {
                action: 'feed_zapper_action_get_feed_items',   // WordPress action hook name which follows after `wp_ajax_`
                fz_nonce: sNonce,   // the nonce value set in template.php
                wp_query: aQuery
            };

            jQuery.ajax({
                type: "post",
                // dataType : "json",
                url: fzCarousel.AJAXURL,
                // Data set to $_POSt and $_REQUEST
                data: _aData,
                success: function (response) {

                    $( element ).find( '.owl-item.active' )
                        .find( '.feed-zapper-feed-container' )
                        .prepend( response );

                    // For errors,
                    if ( $(response).find( '.feed-error' ).length ) {
                        return;
                    }
                },
                error: function (response) {
                },
                complete: function (jqXHR, textStatus) {
console.log( 'ajax loaded.' );
                    _oSpinner.remove();
                }

            });

        }

    });
}(jQuery));