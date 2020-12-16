<?php


class FeedZapper_Template_Carousel_Actions extends FeedZapper_Template_Carousel_Utility {
    /**
     * Sets up properties and hooks.
     */
    public function __construct() {
        add_action( 'feed_zapper_action_save_user_channels', array( $this, 'replyToSaveUserChannels' ) );
    }
    public function replyToSaveUserChannels( $iUserID ) {
        $_aTags         = $this->getUserChannelsFromDatabase( $iUserID );
        $_sTransientKey = 'fz_tags_' . $iUserID;
        self::setTransientAsOption( $_sTransientKey, $_aTags, 86400 );
    }
}