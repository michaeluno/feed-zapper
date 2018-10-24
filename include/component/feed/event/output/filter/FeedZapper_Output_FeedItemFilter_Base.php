<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * A base for feed item filter classes.
 *
 * @since    0.0.1
 */
abstract class FeedZapper_Output_FeedItemFilter_Base extends FeedZapper_PluginUtility {

    public function __construct( $sType ) {
        add_filter( "feed_zapper_filter_feed_{$sType}_item", array( $this, 'replyToDropItem' ), 99, 3 );
        add_filter( "feed_zapper_filter_feed_{$sType}_item_by_user_" . get_current_user_id(), array( $this, 'replyToDropItemByUser' ), 99, 3 );
    }

    /**
     * Filters out items.
     * @param array $aItem
     * @param $oItem
     * @param array $aArguments
     * @return array
     */
    public function replyToDropItem( array $aItem, $oItem, array $aArguments ) {
        return $aItem;
    }

    /**
     * Filters out items by user settings.
     * @param array $aItem
     * @param $oItem
     * @param array $aArguments
     * @return array
     */
    public function replyToDropItemByUser( array $aItem, $oItem, array $aArguments ) {
        return $aItem;
    }

}