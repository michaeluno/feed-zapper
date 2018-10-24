<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno; Licensed under <LICENSE_TYPE>
 * 
 */

/**
 * Adds the `Settings` page to store plugin global settings which apply to all the plugin users.
 *
 * This page can be only accessible by administrators.
 *
 * @since    0.0.1
 */
class FeedZapper_AdminPage_User__Page_Setting extends FeedZapper_AdminPage__Page_Base {

    /**
     * @param  $oFactory
     * @return array
     */
    protected function _getArguments( $oFactory ) {
        return array(
            'page_slug'     => FeedZapper_Registry::$aAdminPages[ 'user_setting' ],
            'title'         => __( 'My Settings', 'feed-zapper' ),
            // 'screen_icon'   => FeedZapper_Registry::getPluginURL( "asset/image/screen_icon_32x32.png" ),
            'order'         => 40,
        );
    }

    /**
     * A user constructor.
     * 
     * @since    0.0.1
     * @return   void
     */
    protected function _construct( $oFactory ) {
        
        // Tabs
//        new FeedZapper_AdminPage_Global__InPageTab_Item( $oFactory, $this->_sPageSlug );
//        new FeedZapper_AdminPage_Global__InPageTab_General( $oFactory, $this->_sPageSlug );
//        new FeedZapper_AdminPage_Global__InPageTab_Data( $oFactory, $this->_sPageSlug );

    }   

}
