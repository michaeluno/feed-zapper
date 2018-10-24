<?php
/**
 * Feed Zapper
 *
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno; Licensed under <LICENSE_TYPE>
 *
 */

/**
 * Adds the 'General' tab to the 'Settings' page of the loader plugin.
 *
 * @since    0.0.1
 * @extends  FeedZapper_AdminPage__InPageTab_Base
 */
class FeedZapper_AdminPage_Global__InPageTab_General extends FeedZapper_AdminPage__InPageTab_Base {

    /**
     * @return      array
     */
    protected function _getArguments( $oFactory ) {
        return array(
            'tab_slug'  => 'general',
            'title'     => __( 'General', 'feed-zapper' ),
        );
    }
    
    /**
     * Triggered when the tab is loaded.
     */
    protected function _load( $oFactory ) {

        // Form sections
        new FeedZapper_AdminPage_Global__FormSection_Feeds( $oFactory, $this->_sPageSlug, $this->_sTabSlug );
        new FeedZapper_AdminPage_Global__FormSection_Permissions( $oFactory, $this->_sPageSlug, $this->_sTabSlug );

    }

    /**
     * @param $oFactory
     */
    protected function _do( $oFactory ) {
        echo "<div class='right-submit-button'>"
                . get_submit_button()
            . "</div>";
    }

}
