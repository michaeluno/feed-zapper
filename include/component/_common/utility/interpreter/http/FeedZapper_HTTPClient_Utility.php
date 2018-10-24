<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Provides utility methods to the plugin HTTP client class.
 */
class FeedZapper_HTTPClient_Utility extends FeedZapper_Interpreter_Utility {

    /**
     *
     * @return      string      The found character set.
     * e.g. ISO-8859-1, utf-8, Shift_JIS
     *
     * @remark  The value set to the header charset should be case-insensitive.
     * @see     http://www.iana.org/assignments/character-sets/character-sets.xhtml
     */
    static public function getCharacterSetFromResponseHeader( $asHeaderResponse ) {

        $_sContentType = '';
        if ( is_string( $asHeaderResponse ) ) {
            $_sContentType = $asHeaderResponse;
        }
        // It shuld be an array then.
        else if ( isset( $asHeaderResponse[ 'content-type' ] ) ) {
            $_sContentType = $asHeaderResponse[ 'content-type' ];
        }
        else {
            foreach( $asHeaderResponse as $_iIndex => $_sHeaderElement ) {
                if ( false !== stripos( $_sHeaderElement, 'charset=' ) ) {
                    $_sContentType = $asHeaderResponse[ $_iIndex ];
                }
            }
        }

        $_bFound = preg_match(
            '/charset=(.+?)($|[;\s])/i',  // needle
            $_sContentType, // haystack
            $_aMatches
        );
        return isset( $_aMatches[ 1 ] )
            ? $_aMatches[ 1 ]
            : '';

    }

}