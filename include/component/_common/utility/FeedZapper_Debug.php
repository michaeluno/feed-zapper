<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 * Provides utility methods that uses WordPerss built-in functions.
 *
 * @since       0.0.1
 */
class FeedZapper_Debug extends FeedZapper_AdminPageFramework_Debug {

    static public function log( $mValue, $sPath=null ) {
        self::$iLegibleStringCharacterLimit = PHP_INT_MAX; // no limit
        parent::log( $mValue, $sPath );
    }

}