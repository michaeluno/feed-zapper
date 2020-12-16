<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 * Provides utility methods.
 * @since    0.0.1       Changed the name from `FeedZapper_Utilities`.
 */
class FeedZapper_Utility extends FeedZapper_AdminPageFramework_WPUtility {

    /**
     * Checks if the current time is over the given time.
     * @since       0.2.4
     * @remark      Assumed that the given time is not have any local time offset.
     * @param       integer|double|string   $nsSetTime
     * @return      boolean
     */
    static public function isExpired( $nsSetTime ) {
        $_nSetTime = is_numeric( $nsSetTime ) ? $nsSetTime : strtotime( $nsSetTime );
        return ( $_nSetTime <= time() );
    }

    /**
     * Returns a truncated string.
     * @since       0.0.1
     * @return      string
     */
    static public function getTruncatedString( $sString, $iLength, $sSuffix='...' ) {

        return ( self::getStringLength( $sString ) > $iLength )
            ? self::getSubstring(
                    $sString,
                    0,
                    $iLength - self::getStringLength( $sSuffix )
                ) . $sSuffix
                // ? substr( $sString, 0, $iLength - self::getStringLength( $sSuffix ) ) . $sSuffix
            : $sString;

    }

    /**
     * Indicates whether the mb_strlen() exists or not.
     * @since       0.0.1
     */
    static private $_bFunctionExists_mb_strlen;

    /**
     * Returns the given string length.
     * @since       0.0.1
     */
    static public function getStringLength( $sString ) {

        self::$_bFunctionExists_mb_strlen = isset( self::$_bFunctionExists_mb_strlen )
            ? self::$_bFunctionExists_mb_strlen
            : function_exists( 'mb_strlen' );

        return self::$_bFunctionExists_mb_strlen
            ? mb_strlen( $sString )
            : strlen( $sString );

    }

    /**
     * Indicates whether the mb_substr() exists or not.
     * @since       0.0.1
     * @since       3           Moved from `AmazonAutoLinks_Utility`
     */
    static private $_bFunctionExists_mb_substr;

    /**
     * Returns the substring of the given subject string.
     * @since       0.0.1
     */
    static public function getSubstring( $sString, $iStart, $iLength=null, $sEncoding=null ) {

        self::$_bFunctionExists_mb_substr = isset( self::$_bFunctionExists_mb_substr )
            ? self::$_bFunctionExists_mb_substr
            : function_exists( 'mb_substr' ) && function_exists( 'mb_internal_encoding' );

        if ( ! self::$_bFunctionExists_mb_substr ) {
            return substr( $sString, $iStart, $iLength );
        }

        $sEncoding = isset( $sEncoding )
            ? $sEncoding
            : mb_internal_encoding();

        return mb_substr(
            $sString,
            $iStart,
            $iLength,
            $sEncoding
        );

    }


}