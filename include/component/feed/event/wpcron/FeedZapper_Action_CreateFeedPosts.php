<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Creates feed item posts from the given URL.
 *
 * @package     FeedZapper
 * @since       0.0.1
 *
 */
class FeedZapper_Action_CreateFeedPosts extends FeedZapper_Event_Action_Base {

    protected $_sActionHookName     = 'feed_zapper_action_create_feed_posts';
    protected $_iCallbackParameters = 1;

    /**
     * 1. Find all owners who have a feed of the given URL.
     * 2. Fetch feed items from the given URL.
     * 3. Store them as a post by giving the owner tag.
     *
     * @remark          For PHP warnings, use `func_get_args()` to retrieve parameters.
     * @callback        action      feed_zapper_action_create_feed_posts
     */
    public function doAction( /* $sURL */ ) {

        $_aParameters            = func_get_args() + array( '' );
        $_sURL                   = $_aParameters[ 0 ];
        $_oFeedAssociatedData    = new FeedZapper_AssociatedFeedPostData( $_sURL );
        if ( ! $_oFeedAssociatedData->hasFeed() ) {
            return;
        }

        // @deprecated not needed despite it creates lots of entries
//        $this->addLog(
//            "<p>" . print_r( $_aParameters, true ) . "</p>",
//            'Creating Feed Items'
//        );

        // Schedule this task for a case reaching out the PHP maximum execution time. In case, it does, the newly created even will complete this task.
        $_iNow = time();    // need this later to unschedule it.
        $_aEventArguments = array( $_sURL );
        $this->scheduleSingleWPCronTask(
            $this->_sActionHookName,
            $_aEventArguments,
            $_iNow
        );

        $_aOwners                = $this->___getStringCasted( $_oFeedAssociatedData->getAuthorIDs() );
        $_aChannels              = $_oFeedAssociatedData->getChannelNames();
        $_aLanguages             = $_oFeedAssociatedData->getLanguageNames();
        $_oFeedFetcher           = new FeedZapper_FeedFetcher( $_sURL, $_oFeedAssociatedData->getMinimumCacheDuration() );
        $_aRawItems              = $_oFeedFetcher->getItems();
        $_oFeed                  = $_oFeedFetcher->getFeedObjectAsItem();
        $_aLanguages[]           = $_oFeed ? $_oFeed->get_language() : '';

        foreach( $_aRawItems as $_oFeedItem ) {
            $this->___insertFeedPost(
                $_oFeedItem,
                $_aOwners, // owners (each element is a numeric string representing the feed author ID)
                $_aChannels,   // channel term names
                $this->getSchemeRemovedFromURL( $_sURL ),   // source
                $_aLanguages  // language names
                // $this->___getFeedCategories( $_oFeed ) // @deprecated feed-publisher-defined categories can be spamming
            );
        }

        wp_unschedule_event( $_iNow, $this->_sActionHookName, $_aEventArguments );
        $_aPostIDs = $_oFeedAssociatedData->getPostIDs();
        $this->___setPostExpirationTime( $_aPostIDs );
        $this->___setPostModifiedDate( $_aPostIDs );

    }
        /**
         * Creates an taxonomy input array by converting given IDs into a string.
         *
         * When assigning a term with `wp_insert_post()`, an array holding the terms is passed to the `tax_input` argument.
         * It accepts term names for its array element value. If the array element values are _integer_,
         * the function takes them as the term object ID. In order for it to consider them as term names, they must be string.
         *
         * @param array $aOwnerIDs
         * @return array
         */
        private function ___getStringCasted( array $aOwnerIDs ) {
            $_aTermNames = array();
            foreach( $aOwnerIDs as $_nAuthorID ) {
                $_aTermNames[] = ( string ) $_nAuthorID;
            }
            return $_aTermNames;
        }
        /**
         * @param   SimplePie_Item $oItem
         * @param   array   $aAuthorIDs         Author IDs associated with the feed (source URL). Each value MUST be a string, not integer. Integers will be considered term IDs. Here they need to be the term name.
         * @param   array   $aChannelNames      Tag IDs associated with the feed (source URL).
         * @param   string  $sLanguage          The detected language of the feed. e.g. en-US, jp
         * @param   array   $aAdditionalTerms   an array holding additional term names in a form of words
         * @return  integer
         */
        private function ___insertFeedPost( SimplePie_Item $oItem, array $aAuthorIDs, array $aChannelNames, $sSource, array $aLanguages, array $aAdditionalTerms=array() ) {
            
            $_aItem     = apply_filters( 'feed_zapper_filter_feed_simplepie_item', array(), $oItem, array() );

            // may be dropped by a global black list filter
            if ( empty( $_aItem ) ) {
                return 0;
            }

            $_sContent  = $_aItem[ 'content' ] ? $_aItem[ 'content' ] : $_aItem[ 'description' ];

            // Prevent duplicates
            if ( $this->getPostIDFromGUID( $_aItem[ 'id' ] ) ) {
                return 0;
            }

            $_aTagNames = array_unique( array_merge( $aChannelNames, $_aItem[ 'categories' ], $aAdditionalTerms ) );
            // Basic post column data
            $_aPostData = array(
                'guid'          => $_aItem[ 'id' ],
                'post_title'    => $_aItem[ 'title' ],
                'post_content'  => $_sContent,
                'tax_input' => array(
                    FeedZapper_Registry::$aTaxonomies[ 'feed_tag' ]      => $_aTagNames,
                    FeedZapper_Registry::$aTaxonomies[ 'feed_owner' ]    => $aAuthorIDs,
                    FeedZapper_Registry::$aTaxonomies[ 'feed_language' ] => $aLanguages,
                ),

                // the site post creation time -> post published date
//                'post_date'             => date( 'Y-m-d H:i:s', $_aItem[ 'timestamp' ] ),
//                'post_date_gmt'         => date( 'Y-m-d H:i:s', $_aItem[ 'timestamp' ] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ),
                // the feed item pubDate -> post modified date
                /**
                 * The feed item's pubDate value is stored in the `post_modified` column.
                 * The reason it is not stored in the `post_date` column is because
                 * sometimes the time becomes future due to GMT offset and future posts are scheduled with the `publish_future_post` WP Cron single action event by WordPress built-in functionality.
                 * When the site scale becomes large, such scheduled actions can fill the `cron` options and may exceed the `max_allowed_packet` size.
                 * If that happens, scheduling starts failing and WP Cron gets stuck.
                 */
                'post_modified'         => date( 'Y-m-d H:i:s', $_aItem[ 'timestamp' ] ),   // current_time( 'mysql' ),
                'post_modified_gmt'     => date( 'Y-m-d H:i:s', $_aItem[ 'timestamp' ] + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ), // current_time( 'mysql', true ),
            );

            // Post meta data

            // Add the image that serves as a featured image if only a thumbnail exists
            if ( $_aItem[ 'thumbnail' ] ) {
                $_aPostData[ '_thumbnail_id' ]      = '_fz_post_thumbnail';
                $_aPostData[ '_fz_post_thumbnail' ] = $_aItem[ 'thumbnail' ];
            }
            $_aPostData[ '_fz_post_permalink' ] = $_aItem[ 'permalink' ];
            $_aPostData[ '_fz_post_images' ]    = $_aItem[ 'images' ];
            $_aPostData[ '_fz_post_source' ]    = $_aItem[ 'source' ];
            $_aPostData[ '_fz_feed_url' ]       = $_aItem[ 'source_feed' ];

            // Now create a post
            $_ioResult = $this->insertPost(
                $_aPostData,
                FeedZapper_Registry::$aPostTypes[ 'item' ]
            );

            if ( is_wp_error( $_ioResult ) ) {
                $_sError  = $_ioResult->get_error_message() . '<br />';
                $_aParams = func_get_args();
                unset( $_aParams[ 0 ] );
                $this->addLog(
                    $_sError . FeedZapper_Debug::get( array( 'parameters' => $_aParams, 'post data' => $_aPostData ), true ),
                    'Post Creation Failed (Feed Item)'
                );
                return 0;
            }
            return $_ioResult;

        }

    /**
     * @param $aAllCapabilities
     * @param $aMetaCapabilities
     * @param $aArguments
     * @param $oUser
     *
     * @return array
     * @remark  in `wp-cron.php` the current user ID becomes 0.
     * For this, dynamically setting custom capabilities based on the permitted user roles in the other callback (in the post type class) does not work.
     * So here granting the capabilities with the versatile hook which is removed immediately after the routine is done.
     * @deprecated
     */
    public function replyToAddCapabilities( $aAllCapabilities, $aMetaCapabilities, $aArguments, $oUser ) {
        foreach( $aMetaCapabilities as $_sCapability ) {
            $aAllCapabilities[ $_sCapability ] = true;
        }
        return $aAllCapabilities;
    }

    /**
     * Sets expiration time to feeds.
     * @param array $aPostIDs
     */
    private function ___setPostExpirationTime( array $aPostIDs ) {
        foreach( $aPostIDs as $_iPostID ) {
            $_iCacheDuration = ( integer ) get_post_meta( $_iPostID, '_fz_cache_duration', true );
            $_iCacheDuration = $_iCacheDuration ? $_iCacheDuration : 3600;
            update_post_meta( $_iPostID, '_fz_feed_expiration_time', time() + $_iCacheDuration );
        }
    }

    /**
     * @param array $aPostIDs
     * @see https://stackoverflow.com/a/50366129
     */
    private function ___setPostModifiedDate( array $aPostIDs ) {

        $_sPostIDs = '';
        foreach( $aPostIDs as $_iID ) {
            $_sPostIDs .= "'{$_iID}', ";
        }
        $_sPostIDs = rtrim( $_sPostIDs, ', ' );

        $_sPostModified    = current_time( 'mysql' );
        $_sPostModifiedGMT = current_time( 'mysql', true );
        $GLOBALS[ 'wpdb' ]->query(
            "UPDATE {$GLOBALS[ 'wpdb' ]->posts} "
            . "SET post_modified = '{$_sPostModified}', post_modified_gmt = '{$_sPostModifiedGMT}' "
            . " WHERE ID in ({$_sPostIDs})"
        );

    }

    // Utility
    /**
     * Removes the scheme (protocol) part of the URL.
     *
     * http://usr:pss@example.com:81/mypath/myfile.html?a=b&b[]=2&b[]=3#myfragment
     * -> usr:pss@example.com:81/mypath/myfile.html?a=b&b[]=2&b[]=3#myfragment
     */
    static public function getSchemeRemovedFromURL( $sURL ) {
        return preg_replace( '#^([^:/]+?)?://#', '', ltrim( $sURL, '/' ) );
    }

    /**
     * @param string $sGUID
     *
     * @return null|string
     * @see https://stackoverflow.com/a/27054880
     */
    static public function getPostIDFromGUID( $sGUID ){
        if ( ! $sGUID ) {
            return 0;
        }
        return ( integer ) $GLOBALS[ 'wpdb' ]->get_var(
            $GLOBALS[ 'wpdb' ]->prepare(
                "SELECT ID FROM {$GLOBALS[ 'wpdb' ]->posts} WHERE guid=%s",
                $sGUID
            )
        );
    }

}