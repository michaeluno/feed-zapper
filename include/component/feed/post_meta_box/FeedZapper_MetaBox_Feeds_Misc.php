<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Displays a meta box and form fields in the plugin's custom post type post editing pages.
 */
class FeedZapper_MetaBox_Feeds_Misc extends FeedZapper_AdminPageFramework_MetaBox {

    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {

        /**
         * Adds setting fields in the meta box.
         */
        $this->addSettingFields(
            array(
                'field_id'          => '__fz_cache_duration',
                'type'              => 'size',
                'title'             => __( 'Update Interval', 'feed-zapper' ),
                'description'       => __( 'Determines how often the feed is checked to renew.', 'feed-zapper' ),
                'units'             => array(
                    60       => __( 'minute(s)', 'feed-zapper' ),
                    3600     => __( 'hour(s)', 'feed-zapper' ),
                    86400    => __( 'day(s)', 'feed-zapper' ),
                    604800   => __( 'week(s)', 'feed-zapper' ),
                ),
                'attributes'        => array(
                    'size'      => array(
                        'step' => 1
                    ),
                ),
                'default'           => array(
                    'size'      => 1,
                    'unit'      => 3600
                ),
                // For the Add New page, keep it minimum
                // @deprecated It is hard to insert default cache duration value into the post
                // if the meta box field is absent as the callback is not called.
                // Also, it is hard to determine whether it is a new post or edit.
                // So even from other meta boxes, at the moment, there is no simple way to do it.
                //   'if'                => 'post.php' === $this->oProp->sPageNow
            )
        );
    }

    public function validate( $aInputs, $aOldInputs, $oMetaBox ) {

        $_aErrors   = array();
        try {

            $_iSize = ( integer ) $aInputs[ '__fz_cache_duration' ][ 'size' ];
            $_iUnit = ( integer ) $aInputs[ '__fz_cache_duration' ][ 'unit' ];
            $aInputs[ '_fz_cache_duration' ] = $_iSize * $_iUnit;  // this will be the one to use

            if ( $aInputs[ '_fz_cache_duration' ] !== $this->oUtil->getElement( $aOldInputs, '_fz_cache_duration' ) ) {
                $this->___renewCache(
                    $this->oUtil->getElement( $_POST, '_fz_feed_url' ), // The url is set from a different meta box,
                    $aInputs[ '_fz_cache_duration' ]
                );
            }

        } catch ( Exception $oException ) {
            $this->setFieldErrors( $_aErrors );
            $this->setSettingNotice( __( 'There was an error in your input in meta box form fields', 'feed-zapper' ) );
            return $aOldInputs;
        }

        return $aInputs;

    }
        /**
         * @param $sURL
         */
        private function ___renewCache( $sURL, $iCacheDuration ) {
            if ( ! filter_var( $sURL, FILTER_VALIDATE_URL ) ) {
                return;
            }
            $_aArguments  = array(
                $sURL,
                $iCacheDuration,
                array(),    // HTTP arguments
                'feed'      // the type must be `feed`
            );
            FeedZapper_PluginUtility::scheduleSingleWPCronTask(
                'feed_zapper_action_http_cache_renewal',
                $_aArguments,
                time() - 1 // now
            );
            FeedZapper_PluginUtility::scheduleSingleWPCronTask(
                'feed_zapper_action_create_feed_posts',
                array( $sURL )
            );
            FeedZapper_PluginUtility::accessWPCron();
        }

}