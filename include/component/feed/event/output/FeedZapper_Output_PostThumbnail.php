<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Sets feed post thumbnail as a featured image.
 *
 * @since    0.0.1
 */
class FeedZapper_Output_PostThumbnail extends FeedZapper_PluginUtility {

    public function __construct() {

        if ( $this->hasBeenCalled( __METHOD__ ) ) {
            return;
        }

        add_filter( 'post_thumbnail_html', array( $this, 'replyToShowFeedItemThumbnailAsFeaturedImage' ), PHP_INT_MAX, 2 );

    }

    public function replyToShowFeedItemThumbnailAsFeaturedImage( $sHTML, $iPostID ) {

        if ( FeedZapper_Registry::$aPostTypes[ 'item' ] !== get_post_type( $iPostID ) ) {
            return $sHTML;
        }

        $_sURL =  get_post_meta( $iPostID, '_fz_post_thumbnail', true );
        if ( empty( $_sURL ) || ! $this->isURLImage( $_sURL ) ) {
            return $sHTML;
        }

        $_sPostTitle = get_post_field( 'post_title', $iPostID ) . ' ' .  __( 'thumbnail', 'feed-zapper' );
        return sprintf(
            '<img src="%1$s" alt="%2$s" />',
            esc_url( $_sURL ),
            esc_attr( $_sPostTitle )
        );

    }


    // Utilities

    /**
     * @param $sURL
     *
     * @return bool
     * @see https://wordpress.stackexchange.com/questions/158491/is-it-possible-set-a-featured-image-with-external-image-url
     */
    static public function isURLImage( $sURL ) {
        if ( ! filter_var( $sURL, FILTER_VALIDATE_URL ) ) {
            return FALSE;
        }
        $ext = array( 'jpeg', 'jpg', 'gif', 'png' );
        $info = (array) pathinfo( parse_url( $sURL, PHP_URL_PATH ) );
        return isset( $info['extension'] )
            && in_array( strtolower( $info['extension'] ), $ext, true );
    }

}