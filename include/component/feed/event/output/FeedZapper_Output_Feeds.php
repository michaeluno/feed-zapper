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
class FeedZapper_Output_Feeds extends FeedZapper_Output_Base {

    /**
     * Serves as a substring of filter hook name, 'feed_zapper_filter_feed_post_item'
     * @var string
     */
    protected $_sItemType = 'post';

    protected $_sOutputFilterHook = 'feed_zapper_filter_feed_output';

    /**
     * Class specific arguments.
     * @remark  override this in each extended class.
     * @var array
     */
    protected $_aArguments = array(
        'query'         => array(), // WP Query arguments
        'skip_query'    => false,   // for Ajax-based template, enable this to skip a database query.
    );

    protected function _construct() {
        new FeedZapper_Output_FeedItemFilter_Post( $this->_sItemType );
    }

    protected function _get( array $aArguments ) {

        $_bDebugMode = $this->isDebugMode();

        try {

            // For templates.
            /// Debug
            $iTimeDBQuery = 0;
            $iTimeItemParsing = 0;

            /// Important Components
            $bHasMore     = false;
            $iFoundCount  = 0;
            $aRawItems    = array();
            $aCategories  = array();
            $aItems       = array();

            if ( $aArguments[ 'skip_query' ] ) {
                throw new Exception();
            }

            if ( $_bDebugMode ) {
                $_iTimeBeforeDBQuery = microtime(true );
            }
            $_oResult = $this->___getFeedPostItems( $aArguments );

            if ( $_bDebugMode ) {
                $iTimeDBQuery = microtime( true ) - $_iTimeBeforeDBQuery;
            }

            if ( ! $_oResult->have_posts() ) {
                throw new Exception( wpautop( 'No items were found' ) ); // without item filters, no posts.
            }

            $bHasMore    = ( boolean ) ( $_oResult->max_num_pages > 1 );
            $iFoundCount = $_oResult->found_posts;
            $aRawItems   = $_oResult->posts;

            // Feed <channel> tag's categories
            $aCategories = array(); // $this->___getCategories( $oFeedAsItem );

            if ( $_bDebugMode ) {
                $_iTimeBeforeParsingItems = microtime(true );
            }
            $aItems = $this->_getItems( $aRawItems, $aArguments );
            if ( $_bDebugMode ) {
                $iTimeItemParsing = microtime( true ) - $_iTimeBeforeParsingItems;
            }

            if ( empty( $aItems ) ) {
                throw new Exception( __( 'No item found.', 'feed-zapper' ) ); // some items may be dropped by filters.
            }

        } catch ( Exception $_oException ) {
            // If no error message, continue including a template
            $_sMessage = $_oException->getMessage();
            if ( $_sMessage ) {
                return $aArguments[ 'show_errors' ]
                    ? "<div class='error feed-error'><p>" . $_sMessage . "</p></div>"
                    : '';
            }
        }

        // Template variables
        // $aArguments - arguments
        // $aItems - formatted items array
        // $aRawItems - an array holding `SimplePie_Item` objects
        // $bHasMore - whether there are more items
        // $iFoundCount - the number indicating the found items
        // $oFeedAsItem - bypassed feed object. For some reasons, $oFeed methods do not work well.
        // $this - this class object
        // $aCategories - channel categories

        // Capture the output buffer
        ob_start();
        include(
            apply_filters(
                'feed_zapper_filter_template_path',
                $this->_getTemplatePath( $aArguments ),
                $aArguments
            )
        );
        $_sContent = ob_get_contents();
        ob_end_clean();
        return $_sContent;

    }

        /**
         * Retrieves feed items of the current user's subscriptions.
         * @param array $aArguments
         * @return WP_Query
         */
        private function ___getFeedPostItems( array $aArguments ) {

            $_iCurrentUserID = get_current_user_id();

            $_aQuery = $this->getElementAsArray( $aArguments, 'query' );
            $_aQuery = $_aQuery + array(
                'post_type'      => FeedZapper_Registry::$aPostTypes[ 'item' ],
                'posts_per_page' => $aArguments[ 'count' ], // @todo the count should be applied to items filtered by black-lists
                'orderby'        => 'modified', // the `modified date` column stores the feed item's publish date (pubDate)
                'post_status'    => 'publish',
                // debug
                    // 'fields' => 'ids'

                // these are for detecting no more items
                // this way, if `$query->max_num_pages` is greater than 1, it means there are more items
                'nopaging' => false,
                'paged'    => 1,

            );

            $_aTaxQuery = $this->getElementAsArray( $_aQuery, 'tax_query' );
            $_aTaxQuery = $_aTaxQuery + array(
                'relation' => 'AND',
            );

            // For Read Later
            if ( ! $this->___hasReadLaterQuery( $_aTaxQuery, $_iCurrentUserID ) ) {
                $_aTaxQuery[] = array(
                    'taxonomy' => FeedZapper_Registry::$aTaxonomies[ 'feed_action' ],
                    'field'    => 'name',
                    'terms'    => 'read_later_by_' . $_iCurrentUserID,
                    'operator' => 'NOT IN',
                );
            }

            $_aTaxQuery[] = array(
                'taxonomy' => FeedZapper_Registry::$aTaxonomies[ 'feed_owner' ],
                'field'    => 'name',
                'terms'    => ( string ) $_iCurrentUserID,
            );
            $_aTaxQuery[] = array(
                'taxonomy' => FeedZapper_Registry::$aTaxonomies[ 'feed_action' ],
                'field'    => 'name',
                'terms'    => 'visited_by_' . $_iCurrentUserID,
                'operator' => 'NOT IN',
            );
            $_aTaxQuery[] = array(
                'taxonomy' => FeedZapper_Registry::$aTaxonomies[ 'feed_action' ],
                'field'    => 'name',
                'terms'    => 'uninterested_by_' . $_iCurrentUserID,
                'operator' => 'NOT IN',
            );

            $_aQuery[ 'tax_query' ] = $_aTaxQuery;
            $_aQuery = apply_filters( "feed_zapper_filter_feed_item_post_query_by_user_{$_iCurrentUserID}", $_aQuery );

            // $result->request gives the SQL query string
            return new WP_Query( $_aQuery );

        }
            /**
             * @param array $aTaxQuery
             *
             * @return bool
             */
            private function ___hasReadLaterQuery( array $aTaxQuery, $iCurrentUserID ) {
                foreach( $aTaxQuery as $_aEachTaxQuery ) {
                    if ( FeedZapper_Registry::$aTaxonomies[ 'feed_action' ] !== $this->getElement( $_aEachTaxQuery, 'taxonomy' ) ) {
                        continue;
                    }
                    $_sFirstTerm = $this->getElement( $_aEachTaxQuery, array( 'terms', 0 ) );
                    if ( 'read_later_by_' . $iCurrentUserID === $_sFirstTerm ) {
                        return true;
                    }
                }
                return false;
            }

}