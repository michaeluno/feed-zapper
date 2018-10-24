<?php
/**
 * Created by PhpStorm.
 * User: Internet
 * Date: 10/3/2018
 * Time: 6:32 AM
 */

abstract class FeedZapper_Parser_FeedItem_Base extends FeedZapper_PluginUtility {

    /**
     * Represents the structure of the feed item array.
     * @var array
     */
    protected $_aItem = array(
        'id'            => '',  // ( required, string ) serves as a post GUID
        '_post_id'      => 0,   // (integer) feed post item id. not available when feeds are retrieved from URL using SimplePie. Used by templates to set/get visited items.
        'title'         => '',  // ( required, string ) feed item title
        'date'          => '',  // ( required, string ) readable published date without offsets
        'timestamp'     => '',  // ( required, string ) published date in the timestamp format
        '_raw_date'     => '',  // ( string ) ( debug ) raw date string shown in the `pubDate` element.
        'authors'       => array(),  // ( array ) authors of the feed item. Nested elements, `email`, `link`, `name`
        'permalink'     => '',  // ( required, string ) URL of the feed item
        'description'   => '',  // ( required, string ) excerpt of the feed item
        'content'       => '',  // ( required, string ) the content of the feed item
        'images'        => array(), // ( required, array ) holding image URLs in the description and content
        'thumbnail'     => '',  // ( required, string ) the thumbnail URL
        'source'        => '',  // ( string ) the source site URL
        'source_feed'   => '',  // ( string ) the source feed (RSS/ATOM) URL
        'categories'    => array(), // ( array ) the item categories
    );

    protected $_sItemType = '';

    protected $_sSiteTimeFormat = '';

    public function __construct() {

        // The hook below is registered only once.
        if ( $this->hasBeenCalled( get_class( $this ) ) ) {
            return;
        }

        $this->_sSiteTimeFormat = get_option( 'date_format' ) . ' g:i a';

        add_filter(
            "feed_zapper_filter_feed_{$this->_sItemType}_item",
            array( $this, 'replyToGetItem' ),
            1,  // top priority
            3   // number of parameters
        );

    }

    public function replyToGetItem( array $aItem, $oItem, array $aArguments ) {
        return $aItem;
    }
}