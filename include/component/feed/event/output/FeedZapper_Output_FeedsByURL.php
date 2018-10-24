<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Handles feed outputs.
 *
 * @since    0.0.1
 */
class FeedZapper_Output_FeedsByURL extends FeedZapper_Output_Base {

    /**
     * Serves as a substring of filter hook name, 'feed_zapper_filter_feed_simplepie_item'
     * @var string
     */
    protected $_sItemType = 'simplepie';

    protected $_sOutputFilterHook = 'feed_zapper_filter_feed_output_by_url';

    /**
     * Class specific arguments.
     * @var array
     */
    protected $_aArguments = array(
        'url'             => '',    // (required, string|array)
        'cache_duration'  => 3600,  // (integer) cache duration in seconds. Only available when the `url` argument is set.
    );

    protected function _construct() {
        new FeedZapper_Output_FeedItemFilter_URL( $this->_sItemType );
    }

    protected function _get( array $aArguments ) {

        $asURL = $aArguments[ 'url' ];
        $iCacheDuration = $aArguments[ 'cache_duration' ];

        // For templates.
        $bHasMore    = false;
        $iFoundCount = 0;
        $aRawItems   = array();
        $aCategories = array();
        $aItems      = array();

        try {

            if ( empty( $asURL ) ) {
                throw new Exception( __( 'A URL parameter is missing.', 'feed-zapper' ) );
            }

            $_oFeedFetcher = new FeedZapper_FeedFetcher( $asURL, $iCacheDuration );
            $_sError = $_oFeedFetcher->getError();
            if ( $_sError ) {
                throw new Exception( $_sError );
            }

            $iFoundCount = $_oFeedFetcher->getFeedObject()->get_item_quantity();
            $aRawItems = $_oFeedFetcher->getItems();

            // For some reasons, the main feed object needs to be bypassed to use get_...() methods.
            // Otherwise, $oFeed->get_...() methods return null.
            $oFeed = $_oFeedFetcher->getFeedObject();
            $oFeedAsItem = $_oFeedFetcher->getFeedObjectAsItem();

            // Feed <channel> tag's categories
            $aCategories = $this->___getCategories( $oFeedAsItem ? $oFeedAsItem : $oFeed);

            $aItems = $this->_getItems( $aRawItems, $aArguments );

            if ( empty( $aItems ) ) {
                throw new Exception( __( 'No item found.', 'feed-zapper' ) );
            }

        } catch ( Exception $_oException ) {
            return $aArguments[ 'show_errors' ]
                ? "<div class='error feed-error'><p>" . $_oException->getMessage() . "</p></div>"
                : '';
        }

        // Template variables
        // $aItems - formatted items array
        // $aRawItems - an array holding `SimplePie_Item` objects
        // $iFoundCount - the number indicating the found items
        // $oFeed - the SimplePie feed object
        // $oFeedAsItem - bypassed feed object. For some reasons, $oFeed methods do not work well.
        // $this - this class object
        // $aCategories - channel categories

        // Capture the output buffer
        ob_start();
        include(
            $_sPath = apply_filters(
                FeedZapper_Registry::HOOK_SLUG . '_filter_template_path',
                $this->_getTemplatePath( $aArguments ),
                $aArguments
            )
        );
        $_sContent = ob_get_contents();
        ob_end_clean();
        return $_sContent;

    }
        /**
         * @param $oFeed
         *
         * @return array
         * @todo    this is a duplicate of FeedZapper_Parser_FeedItem::___getCategories(). Find a way to refactor it.
         */
        private function ___getCategories( $oFeed ) {
            $_aCategories = array();
            if ( empty( $oFeed ) ) {
                return $_aCategories;
            }
            $_aCategoryObjs = $oFeed->get_categories();
            if ( ! empty( $_aCategoryObjs ) ) {
                foreach( $_aCategoryObjs as $_oCategory ) {
                    $_aCategories[] = $_oCategory->get_label();
                }
            }
            return $_aCategories;
        }


}