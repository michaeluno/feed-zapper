<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Called when the user sends a mute item.
 */
class FeedZapper_Action_Ajax_FeedItems_Mute extends FeedZapper_Action_Ajax_FeedItems_Base {

    protected $_sActionHookName     = 'wp_ajax_feed_zapper_action_mute_feed_item'; // wp_ajax_ + action hook name // for logged-in users
    protected $_iCallbackParameters = 1;
    protected $_sSubjectPostKey     = 'mute_feed_item'; // $_POST[ this_one ]
// @unused protected $_sActionTermPrefix   = 'uninterested_by_'; // + user id

    /**
     * @param   integer $iUserID
     * @param   array   $aSubject
     * @return  array|WP_Error   The handled data.
     */
    protected function _getUserDataHandled( $iUserID, array $aMuteItems ) {

        $_aMuteItems = $this->getAsArray( get_user_meta( $iUserID, '_fz_mute_items', true ) );
        $_aMuteItems = $_aMuteItems + $aMuteItems;
        krsort( $_aMuteItems );

        // Drop expired items
        $_iNow = time();
        foreach( $_aMuteItems as $_iTimeout => $_aMute ) {
            if ( $_iTimeout < 0 ) {
                continue;   // permanent items
            }
            if ( $_iTimeout < $_iNow ) {
                unset( $_aMuteItems[ $_iTimeout ] );
            }
        }
FeedZapper_Debug::log( $_aMuteItems );

        update_user_meta( $iUserID, '_fz_mute_items', $_aMuteItems );

        return $aMuteItems;

    }

}