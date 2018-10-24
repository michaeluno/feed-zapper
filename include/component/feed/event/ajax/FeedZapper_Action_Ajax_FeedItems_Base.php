<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * A base class for Ajax request handler classes of feed items.
 */
abstract class FeedZapper_Action_Ajax_FeedItems_Base extends FeedZapper_Event_Action_Base {

//    protected $_sActionHookName     = 'wp_ajax_feed_zapper_action_uninterested_feed_item'; // wp_ajax_ + action hook name // for logged-in users
//    protected $_iCallbackParameters = 1;

    protected $_sSubjectPostKey     = ''; // the key set to $_POST, sent from Ajax
    protected $_sActionTermPrefix   = ''; // the taxonomy term prefix

    public function doAction() {

        check_ajax_referer(
            'feed_zapper_carousel_template_nonce', // the nonce key passed to the `wp_create_nonce()` - `add-post` is done by WordPress
            'fz_nonce' // the $_REQUEST key storing the nonce.
        );

        $_bSuccess  = true;
        $_asMessage = '';
        try {

            $_iUserID = get_current_user_id();
            if ( ! $_iUserID ) {
                throw new Exception( __( 'Could not get a user ID.', 'feed-zapper' ) );
            }
            $_aSubject   = $this->getElementAsArray( $_POST, $this->_sSubjectPostKey );
            $_aosMessage = $this->_getUserDataHandled( $_iUserID, $_aSubject );
            if ( is_wp_error( $_aosMessage ) ) {
                throw new Exception( $_aosMessage->get_error_message() );
            }
            $_asMessage  = $_aosMessage;

        } catch ( Exception $_oException ) {

            $_bSuccess = false;
            $_asMessage = $_oException->getMessage();

        }
        exit(
            json_encode(
                array(
                    'success' => $_bSuccess,
                    // the front-end js script parse these and remove from the session array from the key one by one
                    'result'  => $_asMessage,
                )
            )
        );

    }

    /**
     * @param   integer         $iUserID
     * @param   array           $aSubject
     * @return  array|WP_Error  The handled data.
     */
    protected function _getUserDataHandled( $iUserID, array $aSubject ) {
        if ( ! $this->_sActionTermPrefix ) {
            return array();
        }
        return $this->_getPostsMarked( $this->_sActionTermPrefix . $iUserID, $aSubject );
    }
    
    protected function _getPostsMarked( $sTerm, $aPosts ) {
        $_aTerms = array( $sTerm );
        $_sTaxonomySlug = FeedZapper_Registry::$aTaxonomies[ 'feed_action' ];
        $_oUtil  = new FeedZapper_PluginUtility;
        $_aMarked = array();
        foreach( $aPosts as $_iTime => $_iPostID ) {
            $_mResult = wp_set_post_terms( $_iPostID, $_aTerms, $_sTaxonomySlug );
            if ( is_wp_error( $_mResult ) ) {
                $_aParams = array( 'post_id' => $_iPostID, 'terms' => $_aTerms, 'taxonomy' => $_sTaxonomySlug );
                $_oUtil->addLog(
                    $_mResult->get_error_message() . '<br />'
                    . FeedZapper_Debug::get( $_aParams ),
                    __( 'Failed to insert terms.' )
                );
            }
            if ( is_array( $_mResult ) ) {
                $_aMarked[ $_iTime ] = $_iPostID;
            }
        }
        return $_aMarked;        
    }
    
}