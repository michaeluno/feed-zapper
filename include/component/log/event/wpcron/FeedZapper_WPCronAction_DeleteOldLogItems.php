<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Keeps latest 100 log items but cleans up the rest.
 * @package     FeedZapper
 * @since       0.0.1
 */
class FeedZapper_WPCronAction_DeleteOldLogItems extends FeedZapper_Event_Action_Base {

    protected $_sActionHookName     = 'feed_zapper_action_feed_renew'; // FeedZapper_Registry::$aScheduledActionHooks[ 'feed_renew ]
    protected $_iCallbackParameters = 0;

    /**
     * @callback        action      feed_zapper_action_feed_renew
     */
    public function doAction() {

        $_aPostIDs  = $this->___getOldPostsToDelete();
        foreach( $_aPostIDs as $_iPostID ) {
            wp_delete_post( $_iPostID, true );
        }

    }


        /**
         * @param   integer $iKeep  The number of log items to keep.
         * @return  array   an array holding post IDs to delete.
         */
        private function ___getOldPostsToDelete( $iKeep=100 ) {

            $_aArguments = array(
                'post_type'         => array(
                    FeedZapper_Registry::$aPostTypes[ 'log' ],
                ),
                'posts_per_page'    => -1,    // -1 for all
                'orderby'           => 'date ID',        // another option: 'ID',
                'order'             => 'ASC', // DESC: the newest comes first, 'ASC' : the oldest comes first
                'fields'            => 'ids',    // return only post IDs by default.
            );

            $_oResults    = new WP_Query( $_aArguments );
            $_aPosts      = $_oResults->posts;
            return array_slice( $_aPosts, $iKeep );   // keep first 100 items

        }

}