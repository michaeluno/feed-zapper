<?php
/**
 * Feed Zapper
 *
 * http://en.michaeluno.jp/feed-zapper/
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Provides common methods used among the template component.
 *
 * @package     FeedZapper
 * @since       0.0.1
 */
class FeedZapper_Template_Utility extends FeedZapper_PluginUtility {

    /**
     * Calculates the absolute path from the given relative path to the WordPress installed directory.
     *
     * @since       0.0.1
     * @return      string
     * @remark      APSPATH has a trailing slash.
     */
    static public function getAbsolutePathFromRelative( $sRelativePath ) {
        return ABSPATH . self::getPathSanitized( $sRelativePath );
    }

    /**
     * @since   0.0.1
     * @param   $sPath
     * @return  string
     */
    static public function getPathSanitized( $sPath ) {

        // removes the heading ./ or .\
        $sPath  = preg_replace( "/^\.[\/\\\]/", '', $sPath, 1 );

        // removes the leading slash and backslashes.
        $sPath  = ltrim( $sPath, '/\\' );

        // Use all forward slashes
        $sPath = str_replace( '\\', '/', $sPath );

        return $sPath;

    }

    /**
     * Checks multiple file existence.
     *
     * @since       0.0.1
     * @return      boolean
     */
    static public function doFilesExist( $asFilePaths ) {
        foreach( self::getAsArray( $asFilePaths ) as $_sFilePath ) {
            if ( ! file_exists( $_sFilePath ) ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Includes the given file.
     *
     * As it is said that include_once() is slow, let's check whether it is included by ourselves
     * and use include().
     *
     * @since       0.0.1
     * @return      boolean     true on success; false on failure.
     */
    static public function includeOnce( $sFilePath ) {

        if ( self::hasBeenCalled( $sFilePath ) ) {
            return false;
        }
        if ( ! file_exists( $sFilePath ) ) {
            return false;
        }
        return include( $sFilePath );

    }

}