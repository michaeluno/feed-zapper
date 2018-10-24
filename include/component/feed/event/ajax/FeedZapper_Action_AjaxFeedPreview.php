<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Responds to feed preview Ajax requests.
 *
 * @since   0.0.1
 */
class FeedZapper_Action_AjaxFeedPreview extends FeedZapper_Event_Action_Base {

    protected $_sActionHookName     = 'wp_ajax_feed_zapper_action_feed_preview'; // wp_ajax_ + action hook name
    protected $_iCallbackParameters = 1;

    public function doAction() {

        if ( ! isset( $_POST[ 'post_id' ] ) ) {
            exit();
        }
        $_sAction = $_POST[ 'post_id' ]
            ? 'update-post_' . $_POST[ 'post_id' ]
            : 'add-post';
        check_ajax_referer(
            $_sAction, // the nonce key passed to the `wp_create_nonce()` - `add-post` is done by WordPress
            'post_nonce' // the $_REQUEST key storing the nonce.
        );

        exit( $this->___getAjaxResponse() );
    }

    /**
     * @return  string
     */
        private function ___getAjaxResponse() {
            
            try {
                // Check required keys
                if ( ! isset( $_REQUEST[ 'feed_url' ] ) ) {
                    throw new Exception( __( 'The feed URL is not set.', 'feed-zapper' ) );
                }

                // Check duplicates
                $_iSameFeedPost = FeedZapper_PluginUtility::getFeedIDByURL( $_REQUEST[ 'feed_url' ], $_POST[ 'post_id' ], get_current_user_id() );
                if ( $_iSameFeedPost ) {
                    $_sMessage = sprintf(
                        __( 'There is already a <a href="%1$s">feed</a> with the same address.', 'feed-zapper' ),
                        get_permalink( $_iSameFeedPost )
                    );
                    throw new Exception( $_sMessage );
                }

            } catch( Exception $_oException ) {
                return "<div class='feed-preview' >"
                        . "<div class='error feed-error'>"
                            . "<p>" // this class selector combination is used in the output handler class and referred by the preview script
                                . $_oException->getMessage()
                            . "</p>"
                        . "</div>"
                    . "</div>";
            }

            // At this point, it is ready to fetch the feed
            $_sURL = $_REQUEST[ 'feed_url' ];
            $_oFeedAssociatedData = new FeedZapper_AssociatedFeedPostData( $_sURL );
            return "<div class='feed-preview' >"
                   . getFeedZapperFeedByURL(
                       array(
                           'url'            => $_sURL,
                           'template_path'  => FeedZapper_Registry::$sDirPath . '/include/component/template/output/preview/template.php',
                           'count'          => 20,
                           'show_errors'    => true,
                           'cache_duration' => $_oFeedAssociatedData->getMinimumCacheDuration( 3600 ),
                       ),
                       false
                   )
                   . "</div>";

        }

}