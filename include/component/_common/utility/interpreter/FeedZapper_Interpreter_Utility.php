<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Provides common methods shared among the interpreter component.
 **
 * @since       0.0.1
 */
class FeedZapper_Interpreter_Utility extends FeedZapper_PluginUtility {

    /**
     * Converts a given string into a specified character set.
     * @since       0.0.1
     * @return      string      The converted string.
     * @see         http://php.net/manual/en/mbstring.supported-encodings.php
     * @param       string          $sText                      The subject text string.
     * @param       string          $sCharSetTo                 The character set to convert to.
     * @param       string|boolean  $bsCharSetFrom              The character set to convert from. If a character set is not specified, it will be auto-detected.
     * @param       boolean         $bConvertToHTMLEntities     Whether or not the string should be converted to HTML entities.
     */
    static public function convertCharacterEncoding( $sText, $sCharSetTo='', $bsCharSetFrom=true, $bConvertToHTMLEntities=false ) {

        if ( ! function_exists( 'mb_detect_encoding' ) ) {
            return $sText;
        }
        if ( ! is_string( $sText ) ) {
            return $sText;
        }

        $sCharSetTo = $sCharSetTo
            ? $sCharSetTo
            : get_bloginfo( 'charset' );

        $_bsDetectedEncoding = $bsCharSetFrom && is_string( $bsCharSetFrom )
            ? $bsCharSetFrom
            : self::getDetectedCharacterSet(
                $sText,
                $bsCharSetFrom
            );
        $sText = false !== $_bsDetectedEncoding
            ? mb_convert_encoding(
                $sText,
                $sCharSetTo, // encode to
                $_bsDetectedEncoding // from
            )
            : mb_convert_encoding(
                $sText,
                $sCharSetTo // encode to
                // auto-detect
            );

        if ( $bConvertToHTMLEntities ) {
            $sText  = mb_convert_encoding(
                $sText,
                'HTML-ENTITIES', // to
                $sCharSetTo  // from
            );
        }

        return $sText;

    }


    /**
     *
     * @return      boolean|string      False when not found. Otherwise, the found encoding character set.
     */
    static public function getDetectedCharacterSet( $sText, $sCandidateCharSet='' ) {

        $_aEncodingDetectOrder = array(
            get_bloginfo( 'charset' ),
            "auto",
        );
        if ( is_string( $sCandidateCharSet ) && $sCandidateCharSet ) {
            array_unshift( $_aEncodingDetectOrder, $sCandidateCharSet );
        }

        // Returns false or the found encoding character set
        return mb_detect_encoding(
            $sText, // subject string
            $_aEncodingDetectOrder, // candidates
            true // strict detection - true/false
        );

    }

}