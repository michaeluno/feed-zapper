<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Provides methods to pull out associated data with the given feed URL.
 *
 * @remark      this performs a database query when the class is instantiated.
 * @todo think of a more appropriate class name
 * @package      FeedZapper
 * @since    0.0.1
 */
class FeedZapper_AssociatedFeedPostData extends FeedZapper_Feed_Utility {

    /**
     * Stores feeds associated with the given URL.
     */
    private $___aFeeds = array();

    /**
     * FeedZapper_AssociatedFeedPostData constructor.
     *
     * @param string $sURL
     * @since   0.0.1
     */
    public function __construct( $sURL ) {
        $this->___aFeeds = $this->___getAssociatedFeeds( $sURL );
    }

        /**
         * @since   0.0.1
         * @param $sURL
         *
         * @return array
         */
        private function ___getAssociatedFeeds( $sURL ) {

            $_aArguments = array(
                'post_type'     => FeedZapper_Registry::$aPostTypes[ 'feed' ],
                'post_status'   => 'publish',
                'meta_query' => array(
                    array(
                        'key'     => '_fz_feed_url',
                        'value'   => trim( $sURL ),
                        'compare' => '=',
                    )
                )
            );
            $_oWPQuery = new WP_Query( $_aArguments );
            if ( is_wp_error( $_oWPQuery ) ) {
                $this->addLog( $_oWPQuery->get_error_message() );
                return array();
            }
            if ( ! $_oWPQuery->have_posts() ) {
                return array();
            }
            return $_oWPQuery->get_posts();

        }

    /**
     * @return array
     * @deprecated  not used at the moment
     */
//    public function get() {
//        return $this->___aFeeds;
//    }

    /**
     * Finds the smallest cache duration out of all the associated feeds.
     */
    public function getMinimumCacheDuration( $iDefault=3600 ) {
        $_aCacheDurations = array( PHP_INT_MAX  );
        foreach( $this->___aFeeds as $_oPost ) {
            $_iCacheDuration = ( integer ) get_post_meta( $_oPost->ID, '_fz_cache_duration', true );
            if ( $_iCacheDuration ) {
                $_aCacheDurations[] = $_iCacheDuration;
            }
        }
        $_iMin = min( $_aCacheDurations );
        return PHP_INT_MAX  === $_iMin
            ? 3600 // default
            : $_iMin;
    }

    /**
     * @return array
     */
    public function getPostIDs() {
        $_aPostIDs = array();
        foreach( $this->___aFeeds as $_oPost ) {
            $_aPostIDs[] = $_oPost->ID;
        }
        return $_aPostIDs;
    }

    /**
     * Checks if a feed exists with the set URL.
     * @since   0.0.1
     * @return  boolean True if there is a feed associated with the URL; otherwise. false.
     */
    public function hasFeed() {
        return ! empty( $this->___aFeeds );
    }

    /**
     * @return  array   An array holding associated author's author ID.
     */
    public function getAuthorIDs() {
        $_aAuthorIDs = array();
        foreach( $this->___aFeeds as $_oPost ) {
            $_iAuthorID = $_oPost->post_author;
            $_aAuthorIDs[ $_iAuthorID ] = $_iAuthorID;
        }
        return array_filter( $_aAuthorIDs );    // drops non-true values
    }

    /**
     * @return  array   An array holding associated tags IDs.
     * @deprecated  Not used anymore
     */
    public function getTagIDs() {
        return $this->___getAssociatedTermIDs( FeedZapper_Registry::$aTaxonomies[ 'feed_tag' ] );
    }
        /**
         * @param $sTaxonomySlug
         *
         * @return array
         */
        private function ___getAssociatedTermIDs( $sTaxonomySlug ) {
            $_aAssociatedTermIDs = array();
            foreach( $this->___aFeeds as $_oPost ) {
                // @see https://codex.wordpress.org/Function_Reference/wp_get_post_terms
                $_aTermObjects = wp_get_post_terms(
                    $_oPost->ID,
                    $sTaxonomySlug,
                    array()
                );
                if ( is_wp_error( $_aTermObjects ) ) {
                    continue;
                }
                $_aTerms = $this->___getTermIDs( $_aTermObjects );
                $_aAssociatedTermIDs = $_aAssociatedTermIDs + $_aTerms;
            }
            return $_aAssociatedTermIDs;
        }
            /**
             * @param array $aTermObjects
             * @return  array   An associative array holding terms.
             */
            private function ___getTermIDs( array $aTermObjects ) {
                $_aTerms = array();
                foreach( $aTermObjects as $_oTerm ) {
                    $_aTerms[ $_oTerm->slug ] = $_oTerm->term_id;
                }
                return array_filter( $_aTerms ); // drop non-true values.
            }

    /**
     * @return  array   An array holding associated channel names.
     */
    public function getChannelNames() {
        return $this->___getAssociatedTermProperty( FeedZapper_Registry::$aTaxonomies[ 'feed_channel' ], 'name' );
    }
        /**
         * @param $sTaxonomySlug
         *
         * @return array
         */
        private function ___getAssociatedTermProperty( $sTaxonomySlug, $sPropertyName ) {
            $_aAssociatedTermProperties = array();
            foreach( $this->___aFeeds as $_oPost ) {
                // @see https://codex.wordpress.org/Function_Reference/wp_get_post_terms
                $_aTermObjects = wp_get_post_terms(
                    $_oPost->ID,
                    $sTaxonomySlug,
                    array()
                );
                if ( is_wp_error( $_aTermObjects ) ) {
                    continue;
                }
                $_aTerms = $this->___getTermsPropertyBySlug( $_aTermObjects, $sPropertyName );
                $_aAssociatedTermProperties = $_aAssociatedTermProperties + $_aTerms;
            }
            return $_aAssociatedTermProperties;
        }
            /**
             * @param array $aTermObjects
             * @param $sPropertyName
             *
             * @return array
             */
            private function ___getTermsPropertyBySlug( array $aTermObjects, $sPropertyName ) {
                $_aTerms = array();
                foreach( $aTermObjects as $_oTerm ) {
                    if ( ! isset( $_oTerm->{$sPropertyName} ) ) {
                        continue;
                    }
                    $_aTerms[ $_oTerm->term_id ] = $_oTerm->{$sPropertyName};
                }
                return $_aTerms;
            }

    /**
     * @return  array
     */
    public function getLanguageNames() {
        return $this->___getAssociatedTermProperty( FeedZapper_Registry::$aTaxonomies[ 'feed_language' ], 'name' );
//        $_aTermIDs = $this->___getAssociatedTermIDs( FeedZapper_Registry::$aTaxonomies[ 'feed_language' ] );
//        return array_keys( $_aTermIDs );
    }

}