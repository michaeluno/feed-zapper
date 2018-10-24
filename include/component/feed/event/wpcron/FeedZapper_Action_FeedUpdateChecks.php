<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Renews subscribing feeds.
 *
 * This action is supposed to be triggered periodically.
 *
 * What it does:
 * - lists up expired feeds and retrieve updated feed items.
 * - triggers an event that creates feed posts from the retrieved items.
 *
 * @package     FeedZapper
 * @since       0.0.1
 *
 */
class FeedZapper_Action_FeedUpdateChecks extends FeedZapper_Event_Action_Base {

    protected $_sActionHookName     = 'feed_zapper_action_feed_renew'; // FeedZapper_Registry::$aScheduledActionHooks[ 'feed_renew ]
    protected $_iCallbackParameters = 0;

    /**
     * ## Update Routine
     * 1. Retrieve URLs of expired caches whose request type is `feed` from the cache database table.
     * 2. Parse the URLs and check if the associated feed still exists with the URL.
     * 3. If yes, delete the cache and perform SimplePie HTTP request.
     * 4. If the cache does not exist, it sets a new one and hook into the action of setting the cache.
     *
     * @todo maybe the step 1 and 2 can be combined with one query.
     *
     * - the below steps are covered by `FeedZapper_Action_CreateFeedPostsWithFeedRenewal`
     * 5. In the callback, create new posts if there are new ones.
     * 6. Remove the hook just used in order not to affect other components.
     *
     * @callback        action      feed_zapper_action_feed_renew
     */
    public function doAction() {

        $_oTable       = new FeedZapper_DatabaseTable_fz_request_cache;
        $_aExpiredURLs = $this->___getExpiredFeeds();

        // 1. Renew Feeds
        add_action( 'feed_zapper_action_set_http_request_cache', array( $this, 'replyToTriggerPostCreation' ), 10, 6 );
        $_aRenewedURLs = $this->___getRenewed( $_aExpiredURLs, $_oTable );
        remove_action( 'feed_zapper_action_set_http_request_cache', array( $this, 'replyToTriggerPostCreation' ), 10 );

    }
        private function ___getRenewed( array $aURLs, FeedZapper_DatabaseTable_fz_request_cache $oTable ) {

            $_aRenewURLs = array();
            foreach( $aURLs as $_sCacheName => $_sURL ) {
                // Check if a associated feed still exists.
                $_oFeedAssociatedData = new FeedZapper_AssociatedFeedPostData( $_sURL );
                if ( ! $_oFeedAssociatedData->hasFeed() ) {
                    continue;
                }
                // Store the cache duration
                $_aRenewURLs[ $_sURL ] = $_oFeedAssociatedData->getMinimumCacheDuration();
            }

            // Delete all expired whose `type` is `feed` (this way other types of requests will be safe)
            $oTable->getVariable(
                "DELETE FROM `{$oTable->aArguments[ 'table_name' ]}` "
                . "WHERE expiration_time < UTC_TIMESTAMP() "
                . "AND `type`='feed'"
            );

            // Renew them all
            foreach( $_aRenewURLs as $_sURL => $_iCacheDuration ) {
                // Renew the cache.
                new FeedZapper_FeedFetcher( $_sURL, $_iCacheDuration );
            }
            return $_aRenewURLs;

        }

    /**
     * @remark  Still have duplicates with the same feed source url.
     * @return  array holding expired feed post IDs
     */
    private function ___getExpiredFeeds() {

        $_sPostTypeSlug = FeedZapper_Registry::$aPostTypes[ 'feed' ];
        $_sPosts        = $GLOBALS[ 'wpdb' ]->posts;
        $_sPostMeta     = $GLOBALS[ 'wpdb' ]->postmeta;
        $_aResult       = $GLOBALS[ 'wpdb' ]->get_results(
            $GLOBALS[ 'wpdb' ]->prepare(
                "SELECT DISTINCT {$_sPostMeta}.meta_value as URL 
                FROM {$_sPosts}  
                LEFT JOIN {$_sPostMeta} ON ( {$_sPosts}.ID = {$_sPostMeta}.post_id )  
                LEFT JOIN {$_sPostMeta} AS mt1 ON ({$_sPosts}.ID = mt1.post_id AND mt1.meta_key = '_fz_feed_expiration_time' )  
                LEFT JOIN {$_sPostMeta} AS mt2 ON ( {$_sPosts}.ID = mt2.post_id ) 
                WHERE 1=1  AND ( 
                {$_sPostMeta}.meta_key = '_fz_feed_url' 
                AND 
                    ( 
                        mt1.post_id IS NULL 
                        OR 
                        ( mt2.meta_key = '_fz_feed_expiration_time' AND CAST(mt2.meta_value AS SIGNED) <= '%d' )
                    )
                ) AND {$_sPosts}.post_type = '{$_sPostTypeSlug}' 
                AND ({$_sPosts}.post_status = 'publish') 
                GROUP BY {$_sPostMeta}.meta_value, {$_sPosts}.ID 
                ORDER BY {$_sPosts}.post_date DESC  
                ",
                array(
                    time()  // now
                )
            ),
            'ARRAY_A'
        );
        $_aResult = wp_list_pluck( $_aResult, 'URL' );
        return $_aResult;

    }

    /**
     * Retrieves expired feed URLs.
     *
     * @sine        0.0.1
     * @return      array THe array structure looks like the following
     * ```
     * array(
     *      name => url,
     *      name_1 => url_1,
     *      name_2 => url_2,
     *      ...
     * )
     * ```
     * @deprecated
     */
    private function ___getExpiredURLs( FeedZapper_DatabaseTable_fz_request_cache $oTable ) {

        /**
         * Find expired items whose `type` is `feed`.
         * @todo    this does not work when the cache is completely deleted by some means.
         * Ii occurs when the code that fetches feeds accidentally contains fatal errors then the routine does not complete.
         */
        $_aResult = $oTable->getRows(
            "SELECT name,request_uri FROM `{$oTable->aArguments[ 'table_name' ]}` "
            . "WHERE expiration_time < UTC_TIMESTAMP()"     // not using NOW() as NOW() is GMT compatible
            . " AND `type`='feed'"
        );
        /**
         * Structure of `$_aResult`:
         * ```
         * Array(
         *   [0] => Array(
         *       [name] => FZ_305e4106d6fed60e551e8c6e7deac552
         *       [request_uri] => (string) https://wired.com/rssfeeder/
         *   )
         *   [1] => Array(
         *       [name] => FZ_d228e2d22229676d4d74f2d2f8aeb020
         *       [request_uri] => (string) https://news.google.com/news?ned=us&topic=h&output=rss
         *   )
         *   [2] => Array(
         *       [name] => FZ_3cb6fc72a72028eb117e8cebdf78a296
         *       [request_uri] => ....
         * ```
         */
        // Return expired URLs
        $_aExpiredURLs = array();
        foreach( $_aResult as $_aRow ) {
            $_aExpiredURLs[ $_aRow[ 'name' ] ] = $_aRow[ 'request_uri' ];
        }
        return $_aExpiredURLs;

    }

    /**
     * @param $sCacheName
     * @param $sURL
     * @param $mData
     * @param $iCacheDuration
     * @param $sCharSet
     * @param $sRequestType
     * @callback    action  feed_zapper_action_set_http_request_cache
     */
    public function replyToTriggerPostCreation( $sCacheName, $sURL, $mData, $iCacheDuration, $sCharSet, $sRequestType ) {

        if ( 'feed' !== $sRequestType ) {
            return;
        }
        if ( ! filter_var( $sURL, FILTER_VALIDATE_URL ) ) {
            return;
        }
        /**
         * @todo if the site is big and so many WP Cron events are scheduled,
         * the size of the cron option value may exceed the max allowed packet of MySQL.
         * if that happens WP Cron gets stuck. So consider a better approach to do this.
         */
        $this->scheduleSingleWPCronTask(
            'feed_zapper_action_create_feed_posts',
            array( $sURL )
        );
        $this->accessWPCron();
// FeedZapper_Debug::log( 'scheduling fetch: ' . $sURL );
    }


}