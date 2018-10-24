<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Provides methods to fetch and parse feeds.
 *
 * This is just a wrapper class for SimplePie.
 * This class does not deal with formatting feed items.
 *
 * @package      FeedZapper
 * @since    0.0.1
 */
class FeedZapper_FeedFetcher {

    private $___aURLs = array();

    /**
     * @var SimplePie
     */
    public $oSimplePie;

    public $aErrors = array();

    public $aRawItems = array();


    /**
     * Sets up properties and performs feed fetching.
     *
     * @param $asURL
     */
    public function __construct( $asURL, $iCacheDuration=3600 ) {

        $this->___aURLs = self::getURLsSanitized( $asURL );
        $this->___fetchFeed( $this->___aURLs, $iCacheDuration );

    }
        /**
         * Fetches the feed.
         *
         * This includes an HTTP request to the source feed URLs.
         *
         * @param array $aURLs
         * @see     Example usage   https://gist.github.com/franz-josef-kaiser/5730932
         * @remark      not using `get_item_quantity()` to limit the count because black-listed items may reduce the overall number of items.
         */
        private function ___fetchFeed( array $aURLs, $iCacheDuration ) {

            $this->oSimplePie = new FeedZapper_SimplePieForFeeds;
            $this->oSimplePie->set_sortorder( 'date' );
            $this->oSimplePie->set_feed_url( $aURLs );
            $this->oSimplePie->set_cache_duration( $iCacheDuration );
            $this->oSimplePie->init();
            $this->aErrors   = $this->oSimplePie->error();
            if ( ! empty( $this->aErrors ) ) {
                $_sMessage = "SimplePie Error: " . $this->getError() . "<br />"
                    . "<p>" . print_r( $aURLs, true ) . "</p>";
                FeedZapper_PluginUtility::addLog( $_sMessage, 'SimplePie Error' );
            }
            $this->aRawItems = empty( $this->aErrors )
                ? $this->oSimplePie->get_items()
                : array();
            remove_filter( 'http_request_args', array( $this, 'replyToModifyHTTPRequestHeader' ), 10 );
        }

    public function getFeedObject() {
        return $this->oSimplePie;
    }

    /**
     * Returns the `SimplePie_Item` feed object for a workaround to use `get_{...}()` methods.
     *
     * For some reasons, the `SimplePie` object's `get_{...}()` methods return null
     * but the methods of the item object's parent feed object (which is supposed to refer to the root node) successfully returns values.
     *
     * @return null|SimplePie
     */
    public function getFeedObjectAsItem() {
        $_oItem = $this->oSimplePie->get_item();
        if ( ! $_oItem ) {
            return null;
        }
        $_oFeed = $_oItem->get_feed();
        if ( ! $_oFeed ) {
            return null;
        }
        return $_oFeed;
    }

    /**
     * @return string
     */
    public function getError() {
        if ( empty( $this->aErrors ) ) {
            return '';
        }
        return is_array( $this->aErrors )
            ? implode( '', $this->aErrors )
            : ( string ) $this->aErrors;
    }

    /**
     * @since   0.0.1
     * @return  array
     */
    public function getItems() {
        return $this->aRawItems;
    }

    // Utilities

    /**
     * @return      array
     * @since       0.0.1
     */
    static public function getURLsSanitized( $asURLs ) {

        $_aURLs = is_array( $asURLs ) ? $asURLs : array( $asURLs );

        $_aSanitized = array();
        foreach( $_aURLs as $_sURL ) {

            if ( ! filter_var( $_sURL, FILTER_VALIDATE_URL ) ) {
                continue;
            }
            $_aSanitized[] = $_sURL;

        }
        return $_aSanitized;

    }
}