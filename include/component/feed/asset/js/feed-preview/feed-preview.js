/**
 * Plugin Template
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2015 [COPYRIGHT_HOLDER]
 * 
 */
(function($){
    $( document ).ready( function() {

        // console.log( 'translation items' );
        // console.log( feedPreview );

        if ( 'add' === feedPreview.mode ) {
            $('input[type="submit"][name="_submit"]').prop('disabled', true);
        }

        // @deprecated localhost is considered invalid
        var __isValidURL = function(url) {
            return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
        }
        var _isValidURL = function(string){
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        };

        // For post editing page, the url is already filled.
        if ( 'edit' === feedPreview.mode ) {
            _previewFeedInMetaBox( $( 'input[name="_fz_feed_url"]' ) );
        }

        $( '#feed-preview-button' ).click( function() {
            _previewFeedInMetaBox( $( 'input[name="_fz_feed_url"]' ) );
            return false;
        } );
        $( 'input[name="_fz_feed_url"]' ).donetyping( function(){
            _previewFeedInMetaBox( $( this ) );
          // $('#example-output').text('Event last fired @ ' + (new Date().toUTCString()));
        }, 2000 );

        function _previewFeedInMetaBox( oElem ) {

            var _sURL = oElem.val();
            if ( ! _isValidURL( _sURL ) ) {
                return true;
            }

            // alert( 'OK: ' + $( this ).val() );
            // $( this ).after('<div class="load-spinner"><img src="' + feedPreview.spinner_url + '" /></div>');
            $( '#feed-preview-error' ).addClass( 'hidden' );    // make sure the previous error message is not displayed
            var _oSpinner = $( '#feed-preview-spinner' );
            _oSpinner.addClass( 'is-active' );
            var _oPreviewButton = $( '#feed-preview-button' );
            _oPreviewButton.prop( 'disabled', true );

            var _aData = {
                action: 'feed_zapper_action_feed_preview',   // WordPress action hook name
                post_nonce: $( '#_wpnonce' ).val(),   // set by WordPress in post.php
                post_id: $( 'input#post_ID' ).val(),
                feed_url: _sURL,
            };
            jQuery.ajax( {
                type : "post",
                // dataType : "json",
                url : feedPreview.AJAXURL,
                // Data set to $_POSt and $_REQUEST
                data : _aData,
                success: function(response) {

                    $( '#feed-preview-placeholder' ).empty().append( response );

                    // For errors,
                    if ( $( response ).find( '.feed-error' ).length ) {
                        return;
                    }

                    // Set the title and enable the submit button
                    if ( 'add' === feedPreview.mode ) {
                        var _sTitle = $( '#feed-preview-title' ).text();
                        $( 'input[name="post_title"]' ).val( _sTitle );
                    }

                    // Enable the submit button
                    $( 'input[type="submit"][name="_submit"]' ).prop( 'disabled', false );

                },
                error: function( response ) {
                },
                complete: function(  jqXHR, textStatus ) {

                    _oSpinner.remove();
                    var _oPreviewButton = $( '#feed-preview-button' );
                    _oPreviewButton.prop( 'disabled', false );
                }

            });

        }

    });
    
}(jQuery));