<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Loads the zapper component.
 *
 * @package      FeedZapper
 * @since    0.0.1
 */
class FeedZapper_Zapper_Loader extends FeedZapper_Loader_Base {

    protected function _load() {
        $this->___loadPostTypes();

    }
        private function ___loadPostTypes() {
            new FeedZapper_PostType_Zappers(
                FeedZapper_Registry::$aPostTypes[ 'zapper' ],
                array(),    // post type arguments - set in setUp()
                $this->_sFilePath, // caller file path
                FeedZapper_Registry::TEXT_DOMAIN
            );
        }
    protected function _loadInAdmin() {
        new FeedZapper_MetaBox_Submit(
            null,   // meta box ID - can be null.
            __( 'Submit', 'feed-zapper' ), // title
            array( FeedZapper_Registry::$aPostTypes[ 'zapper' ] ),                               // post type slugs: post, page, etc.
            'side',                                        // context
            'low',                                          // priority
            'read'  // capability
        );
    }

}