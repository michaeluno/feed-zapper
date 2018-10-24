<?php
/**
 * Created by PhpStorm.
 * User: Internet
 * Date: 9/26/2018
 * Time: 9:28 PM
 */

class FeedZapper_Log_Utility extends FeedZapper_PluginUtility {

    /**
     * Adds a log entry.
     * @return  integer|WP_Error
     */
    public static function addLog( $sMessage, $sTitle='' ) {
        return self::insertPost(
            array(
                'post_content' => $sMessage,
                'post_title'   => $sTitle,
            ),
            FeedZapper_Registry::$aPostTypes[ 'log' ]
        );
    }



}