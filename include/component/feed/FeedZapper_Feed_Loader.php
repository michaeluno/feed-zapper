<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Loads the feed component.
 *
 * @package      FeedZapper
 * @since    0.0.1
 */
class FeedZapper_Feed_Loader extends FeedZapper_Loader_Base {

    protected function _load() {
        $this->___loadPostTypes();
        $this->___listenEvents();
    }


    protected function _loadInAdmin() {

        new FeedZapper_MetaBox_Submit(
            null,   // meta box ID - can be null.
            __( 'Submit', 'feed-zapper' ), // title
            array( FeedZapper_Registry::$aPostTypes[ 'feed' ] ),                               // post type slugs: post, page, etc.
            'side', // context: normal|side|advanced
            'low', // priority: low|high|default|core
            'read'  // capability
        );

        // if ( isset( $GLOBALS[ 'pagenow' ] ) && 'post.php' === $GLOBALS[ 'pagenow' ] ) {
            new FeedZapper_MetaBox_Feeds_Misc(
                null,   // meta box ID - can be null.
                __( 'Misc', 'feed-zapper' ), // title
                array( FeedZapper_Registry::$aPostTypes[ 'feed' ] ),                               // post type slugs: post, page, etc.
                'side', // context: normal|side|advanced
                'low', // priority: low|high|default|core
                'read'  // capability
            );
        // }
        new FeedZapper_MetaBox_Feeds_URL(
            null,   // meta box ID - can be null.
            __( 'Feed URL', 'feed-zapper' ), // title
            array( FeedZapper_Registry::$aPostTypes[ 'feed' ] ), // post type slugs: post, page, etc.
            'normal', // context: normal|side|advanced
            'high', // priority: low|high|default|core
            'read'  // capability
        );
        new FeedZapper_MetaBox_Feeds_Preview(
            null,   // meta box ID - can be null.
            __( 'Preview', 'feed-zapper' ), // title
            array( FeedZapper_Registry::$aPostTypes[ 'feed' ] ), // post type slugs: post, page, etc.
            'normal', // context: normal|side|advanced
            'low', // priority: low|high|default|core
            'read'  // capability
        );

    }

    private function ___loadPostTypes() {
        new FeedZapper_PostType_Feed(
            FeedZapper_Registry::$aPostTypes[ 'feed' ],
            array(),    // post type arguments - set in setUp()
            $this->_sFilePath, // caller file path
            FeedZapper_Registry::TEXT_DOMAIN
        );
        new FeedZapper_PostType_FeedItem(
            FeedZapper_Registry::$aPostTypes[ 'item' ],
            array(),    // post type arguments - set in setUp()
            $this->_sFilePath, // caller file path
            FeedZapper_Registry::TEXT_DOMAIN
        );
    }

    private function ___listenEvents() {

        // Front-end Outputs
        new FeedZapper_Parser_ImageExtractor;
        new FeedZapper_Parser_FeedItem_SimplePie;
        new FeedZapper_Parser_FeedItem_Post;
        new FeedZapper_Output_PostThumbnail;
        new FeedZapper_Output_FeedQueryFilter_Post;

        new FeedZapper_Output_Feeds;
        new FeedZapper_Output_FeedsByURL;

        // Background
        new FeedZapper_Action_AjaxFeedPreview;
        new FeedZapper_Action_Ajax_GetFeedItems;
        new FeedZapper_Action_Ajax_FeedItems_Clicked;
        new FeedZapper_Action_Ajax_FeedItems_Uninterested;
        new FeedZapper_Action_Ajax_FeedItems_ReadLater;
        new FeedZapper_Action_Ajax_FeedItems_Mute;

        // WP Cron
        new FeedZapper_Action_FeedUpdateChecks;
        new FeedZapper_Action_CreateFeedPosts;
        new FeedZapper_Action_DeleteOldUntouchedFeedItems;

    }

}