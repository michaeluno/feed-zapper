<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 * Provides utility methods that uses plugin specific elements.
 *
 * @package     FeedZapper
 * @since    0.0.1       
 */
class FeedZapper_PluginUtility extends FeedZapper_WPUtility {

    /**
     * @param   $sMessage
     * @return  void
     */
    static public function addLog( $sMessage, $sTitle='' ) {
        do_action( 'feed_zapper_action_add_log', $sMessage, $sTitle );
    }

    /**
     * Retrieves a feed ID from a given URL.
     *
     * This is mainly used to check duplicated feeds created by an user.
     *
     * @remark  The word `feed` here refers to a plugin custom post type that stores the associated feed URL in a post meta.)
     * @param $sURL
     * @param integer $iPostIDToExclude
     * @param integer $iAuthorID
     *
     * @return integer
     * @since   0.0.1
     */
    static public function getFeedIDByURL( $sURL, $iPostIDToExclude=0, $iAuthorID=0 ) {

        $_aArguments = array(
            'fields'         => 'ids',
            'post_type'      => FeedZapper_Registry::$aPostTypes[ 'feed' ],
            'meta_query'     => array(
                array(
                    'key'   => '_fz_feed_url',
                    'value' => $sURL,
                )
            ),
            'post_status'    => 'publish',

        );
        if ( $iPostIDToExclude ) {
            $_aArguments[ 'post__not_in' ] = array( $iPostIDToExclude );
        }
        if ( $iAuthorID ) {
            $_aArguments[ 'author' ] = $iAuthorID;
        }

        $_oResult = new WP_Query( $_aArguments );
        return empty( $_oResult->posts )
            ? 0
            : ( integer ) $_oResult->posts[ 0 ];    // supposed to have only one
    }


    /**
     * Returns information of the page that displays user feed timeline.
     *
     * Used when updating the option and activating the plugin.
     *
     * @return array    The found/created feed page information array. Structure
     * ```
     * array(
     *      'id' => (integer) post id
     *      'text' => (string) post title
     * )
     * ```
     * @since   0.0.1
     */
    static public function getFeedPage() {
        $_oPage = get_page_by_path( 'feeds' );
        if ( isset( $_oPage->post_status ) && 'publish' === $_oPage->post_status ) {
            return array( 'id' => $_oPage->ID, 'text' => $_oPage->post_title );
        }
        $_sPageTitle = __( 'Feeds', 'feed-zapper' );
        $_oiResult   = wp_insert_post(
            array(
                'post_content' => '<!-- '
                                  . sprintf( __( 'Created by %1$s.', 'feed-zapper' ), FeedZapper_Registry::NAME )
                                  . ' -->',
                'post_author'  => get_current_user_id(), // $GLOBALS[ 'user_ID' ],
                'post_title'   => $_sPageTitle,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'tax_input'    => array(),
            )
        );
        return is_integer( $_oiResult )
            ? array( 'id' => $_oiResult, 'text' => $_sPageTitle )
            : array( 'id' => 0, 'text' => '' );
    }




    /**
     * Reschedules the Feed Renew event.
     *
     * The feed renew event lists up expired feeds and creates actual feed post from the retrieved items.
     *
     * @return boolean
     * @since   0.0.1
     */
    static public function rescheduleFeedChecks() {
        wp_clear_scheduled_hook( FeedZapper_Registry::$aScheduledActionHooks[ 'feed_renew' ], array() );
        return self::scheduleFeedChecks();
    }

    /**
     * Schedules the Feed Renew event.
     *
     * The feed renew event lists up expired feeds and creates actual feed post from the retrieved items.
     *
     * @remark  The passed action arguments must be an empty array. If passed arguments are different, the next schedule check fails.
     * @since   0.0.1
     * @return  boolean     return false for failure. True if scheduled. True also if already scheduled.
     */
    static public function scheduleFeedChecks() {

        new FeedZapper_WPCronCustomInterval;

        $_aArguments = array(); // the argument must be an empty array. This is used when unscheduling the event.
        if ( wp_next_scheduled( FeedZapper_Registry::$aScheduledActionHooks[ 'feed_renew' ], $_aArguments ) ) {
            return true;
        }
        $_bvResult = wp_schedule_event(
            time(), // time stamp
            FeedZapper_Registry::$aWPCronIntervals[ 'feed_renew' ], // interval slug
            FeedZapper_Registry::$aScheduledActionHooks[ 'feed_renew' ], //
            $_aArguments
        );
        return false !== $_bvResult;

    }

}