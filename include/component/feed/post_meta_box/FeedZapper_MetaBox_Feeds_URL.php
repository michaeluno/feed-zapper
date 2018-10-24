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
class FeedZapper_MetaBox_Feeds_URL extends FeedZapper_AdminPageFramework_MetaBox {

    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {

        /**
         * Adds setting fields in the meta box.
         */
        $this->addSettingFields(
            array(
                'field_id'          => 'post_title',
                'type'              => 'hidden',
                'value'             => '',

                // for the `post-new.php` page, the WordPress built-in title field is disabled for better user experience (quickness to create a feed).
                // It appears on the post edit screen.
                'if'                => 'post-new.php' === $this->oProp->sPageNow
            ),
            array(
                'field_id'          => '_fz_feed_url',
                'title'             => __( 'URL', 'feed-zapper' ),
                'type'              => 'text',
                'after_fieldset'    => '<span class="spinner" id="feed-preview-spinner"></span>',
            )
        );

    }

    public function validate( $aInputs, $aOldInputs, $oMetaBox ) {

        $_aErrors   = array();
        $_sURL      = trim( $aInputs[ '_fz_feed_url' ] );
        try {
            if ( ! filter_var( $_sURL, FILTER_VALIDATE_URL ) ) {
                $_aErrors[ '_fz_feed_url' ] = __( 'The given text is not a valid URL.', 'feed-zapper' ) . ': ' . $aInputs[ '_fz_feed_url' ];
                throw new Exception;
            }

            if ( $this->___hasDuplicate( $_sURL ) ) {
                $_aErrors[ '_fz_feed_url' ] = __( 'A feed with the same URL already exists.', 'feed-zapper' );
                throw new Exception;
            }
        } catch ( Exception $oException ) {
            $this->setFieldErrors( $_aErrors );
            $this->setSettingNotice( __( 'There was an error in your input in meta box form fields', 'feed-zapper' ) );
            return $aOldInputs;
        }

        if ( $_sURL !== $this->oUtil->getElement( $aOldInputs, array( '_fz_feed_url' ) ) ) {
            $this->___scheduleFeedItemConversion( $_sURL );
        }

        add_filter( 'redirect_post_location', array( $this, 'replyToRedirectToListing' ), 999, 2 );
        return $aInputs;

    }
        /**
         * @param $sURL
         * @return  boolean
         */
        private function ___hasDuplicate( $sURL ) {

            $_iCurrentPostID = 0;

            // For the Edit page or the Add New page returned with a field error,
            if ( 'post.php' === $this->oProp->sPageNow ) {
                $_iCurrentPostID = isset( $_POST[ 'ID' ] ) ? ( integer ) $_POST[ 'ID' ] : 0;
            }

            $_iSameFeedPost = FeedZapper_PluginUtility::getFeedIDByURL( $sURL, $_iCurrentPostID, get_current_user_id() );
            if ( $_iSameFeedPost ) {
                return true;
            }
            return false;
        }

        /**
         * Schedules an event that converts feed items into actual posts of the plugin custom post type.
         */
        private function ___scheduleFeedItemConversion( $_sURL ) {
            $_oUtil = new FeedZapper_PluginUtility;
            $_oUtil->scheduleSingleWPCronTask(
                'feed_zapper_action_create_feed_posts',
                array( $_sURL )
            );
            $_oUtil->accessWPCron();
        }


    /**
     * @param $sURL
     * @param $iPostID
     *
     * @return string
     * @callback    filter      redirect_post_location
     */
    public function replyToRedirectToListing( $sURL, $iPostID ) {

        $_sPostType = FeedZapper_Registry::$aPostTypes[ 'feed' ];
        if ( get_post_type() !== $_sPostType ) {
            return $sURL;
        }

        $_sListingURL = add_query_arg(
            array(
                'post_type' => $_sPostType
            ),
            admin_url( 'edit.php' )
        );

        if ( isset( $_POST[ 'post_type' ] ) && $_POST[ 'post_type' ] === $_sPostType ) {
            return $_sListingURL;
        }

        return $sURL;

    }

}