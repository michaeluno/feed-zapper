<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Formats feed post items.
 *
 * @since    0.0.1
 */
class FeedZapper_Parser_FeedItem_Post extends FeedZapper_Parser_FeedItem_Base {

    /**
     * This serves as a substring of the `feed_zapper_filter_feed_post_item` filter.
     * @var string
     */
    protected $_sItemType = 'post';

    /**
     * @param array $aItem
     * @param WP_Post $oPost
     * @param array $aArguments
     *
     * @return array
     */
    public function replyToGetItem( array $aItem, $oPost, array $aArguments ) {
        $_aTerms = wp_get_post_terms( $oPost->ID, FeedZapper_Registry::$aTaxonomies[ 'feed_tag' ] );

        $_aItem  = array(
            'id'            => $oPost->guid,
            '_post_id'      => $oPost->ID,
            'title'         => $oPost->post_title,
            'date'          => $oPost->post_modified,  // ( required, string ) readable published date without offsets. The site set time format is used.
            'timestamp'     => strtotime( $oPost->post_modified ),  // ( required, string ) published date in the timestamp format
            '_raw_date'     => '',  // ( string ) ( debug ) raw date string shown in the `pubDate` element.
            // @todo
            'authors'       => array(),  // ( array ) authors of the feed item. Nested elements, `email`, `link`, `name`
            'permalink'     => get_post_meta( $oPost->ID, '_fz_post_permalink', true ),  // ( required, string ) URL of the feed item
            'description'   => $oPost->post_content,  // ( required, string ) excerpt of the feed item
            'content'       => $oPost->post_content,  // ( required, string ) the content of the feed item
            'images'        => $this->getAsArray( get_post_meta( $oPost->ID, '_fz_post_images', true ) ),
            'thumbnail'     => get_post_meta( $oPost->ID, '_fz_feed_thumbnail', true ),
            'source'        => get_post_meta( $oPost->ID, '_fz_post_source', true ),  // ( string ) the source site URL
            'source_feed'   => get_post_meta( $oPost->ID, '_fz_feed_url', true ),  // ( string ) the source feed (RSS/ATOM) URL
            // @todo
            'categories'    => wp_list_pluck( $_aTerms, 'name' ), // ( array ) the item categories
        ) + $this->_aItem;
        return $_aItem;

    }
}