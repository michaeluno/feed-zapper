<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Modifies post query arguments done in the feed component when generating feed item outputs.
 *
 * @since    0.0.1
 */
class FeedZapper_Output_FeedQueryFilter_Post extends FeedZapper_PluginUtility {

    protected $_iUserID = 0;

    public function __construct() {

        $this->_iUserID = get_current_user_id();
        if ( ! $this->_iUserID ) {
            return;
        }
        add_filter( "feed_zapper_filter_feed_item_post_query_by_user_{$this->_iUserID}", array( $this, 'replyToAddNotIn' ), 10, 1 );

    }

    public function replyToAddNotIn( array $aQuery ) {

        // @todo Should be configured by user
        $_aMetaQuery   = $this->getElementAsArray( $aQuery, 'meta_query' );
        $_aMetaQuery[] = array(
            'relation' => 'AND',
            array(
                'compare' => 'NOT LIKE',
                'key'     => '_fz_post_permalink',
                'value'   => 'anond.hatelabo.jp',
            ),
        );
        $aQuery[ 'meta_query' ] = $_aMetaQuery;


        return $aQuery;

    }

}