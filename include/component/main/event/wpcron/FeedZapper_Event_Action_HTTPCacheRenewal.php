<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Renews HTTP request caches in the background.
 *
 * This hooks into the HTTP request event and if the data is expired, this is triggered.
 *
 * @package     FeedZapper
 * @since       0.0.1
 *
 * @action      add             feed_zapper_filter_http_response_cache
 * @action      schedule|add    feed_zapper_action_http_cache_renewal
 */
class FeedZapper_Event_Action_HTTPCacheRenewal extends FeedZapper_Event_Action_Base {

    protected $_sActionHookName     = 'feed_zapper_action_http_cache_renewal';
    protected $_iCallbackParameters = 4;
    
    /**
     * Sets up additional hooks.
     * @since       0.0.1
     */
    protected function _construct() {

        // For SimplePie cache renewal events
        add_filter(
            'feed_zapper_filter_http_response_cache',  // filter hook name
            array( $this, 'replyToModifyCacheRemainedTime' ), // callback
            10, // priority
            4 // number of parameters
        );

    }

    /**
     *
     * @remark          For PHP warnings, use `func_get_args()` to retrieve parameters.
     * @callback        action      feed_zapper_action_http_cache_renewal
     */
    public function doAction( /* $sURL, $iCacheDuration, $aArguments, $sType='wp_remote_get' */ ) {

        $_aParameters   = func_get_args() + array( '', 0, array(), 'wp_remote_get' );
        $sURL           = $_aParameters[ 0 ];
        $iCacheDuration = $_aParameters[ 1 ];
        $aArguments     = $_aParameters[ 2 ];
        $sType          = $_aParameters[ 3 ];
        $_oHTTP         = new FeedZapper_HTTPClient(
            $sURL,
            $iCacheDuration,
            $aArguments,
            $sType
        );
        $_oHTTP->deleteCache();
        $_oHTTP->get();

    }

    /**
     * Tells plugin's HTTP client that the cache is not expired and schedules a renewal event in the background.
     *
     * @callback    filter      feed_zapper_filter_http_response_cache
     * @since       0.0.1
     */
    public function replyToModifyCacheRemainedTime( $aCache, $iCacheDuration, $aArguments, $sType='wp_remote_get' ) {

        // If it is expired,
        if ( 0 >= $aCache[ 'remained_time' ] ) {

            // It is expired. So schedule a task that renews the cache in the background.
            $_bScheduled = $this->___scheduleBackgroundCacheRenewal(
                $aCache[ 'request_uri' ],
                $iCacheDuration,
                $aArguments,
                $sType
            );

            // Tell the plugin it is not expired.
            $aCache[ 'remained_time' ] = time();

        }
        return $aCache;

    }
        /**
         *
         * @return      boolean
         */
        private function ___scheduleBackgroundCacheRenewal( $sURL, $iCacheDuration, $aArguments, $sType ) {

            $_aArguments  = array(
                $sURL,
                $iCacheDuration,
                $aArguments,
                $sType
            );
            $this->accessWPCron();
            return $this->scheduleSingleWPCronTask(
                $this->_sActionHookName,
                $_aArguments,
                time()  // now
            );

        }

}