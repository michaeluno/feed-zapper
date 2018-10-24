<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Filters feed items fetched by feed URL.
 *
 * @since    0.0.1
 */
class FeedZapper_Output_FeedItemFilter_URL extends FeedZapper_Output_FeedItemFilter_Base {

    /**
     * Filters out items.
     * @param array $aItem
     * @param $oItem
     * @param array $aArguments
     * @return array
     */
    public function replyToDropItem( array $aItem, $oItem, array $aArguments ) {

        if ( empty( $aItem ) ) {
            return $aItem;
        }

        if ( 'This RSS feed URL is deprecated' === $aItem[ 'title' ] ) {
            return array();
        }

        return $aItem;

    }


}