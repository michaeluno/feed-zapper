<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * @todo this should be placed in the template component.
 * But at the moment, active templates are determined with the `wp` action hook.
 * Within the hook, it is too late to register for the ajax hook.
 * Consider a better implementation of ajax support templates.
 */
class FeedZapper_Action_Ajax_GetFeedItems extends FeedZapper_Event_Action_Base {

    protected $_sActionHookName     = 'wp_ajax_feed_zapper_action_get_feed_items'; // wp_ajax_ + action hook name // for logged-in users
    protected $_iCallbackParameters = 1;

    protected $_aTime = array();
    protected function _construct() {
        $this->_aTime[ 'started' ] = microtime( true );
    }

    public function doAction() {
        $this->_aTime[ 'callback' ] = microtime( true );
        check_ajax_referer(
            'feed_zapper_carousel_template_nonce', // the nonce key passed to the `wp_create_nonce()` - `add-post` is done by WordPress
            'fz_nonce' // the $_REQUEST key storing the nonce.
        );
        $this->_aTime[ 'nonce_check' ] = microtime( true );
        $_aWPQuery = $this->getElementAsArray( $_POST, 'wp_query' );    // when an empty object is passed from Ajax, the post key gets unset


        exit( $this->___getAjaxResponse( $_aWPQuery ) );

    }

        private function ___getAjaxResponse( array $aWPQuery ) {

            $_sTemplatesDirPath = apply_filters( 'feed_zapper_filter_templates_directory_path', '' );
            $_sResponse = getFeedZapperFeed(
                    array(
                        'count'         => 40,
                        'query'         => $aWPQuery,
                        'template_path' => $_sTemplatesDirPath . '/post/template.php',
                    ),
                    false
                );
            $_sDebugInfo = $this->___getDebugInfo();
            return $_sDebugInfo . $_sResponse;
        }
            private function ___getDebugInfo() {
return '';
                if ( ! $this->isDebugMode() ) {
                    return '';
                }
                $_iStartToCallback  = $this->_aTime[ 'callback' ] - $this->_aTime[ 'started' ];
                $_iCallbackToNonceCheck = $this->_aTime[ 'nonce_check' ] - $this->_aTime[ 'callback' ];
                $_iNonceCheckToReturn = microtime( true ) - $this->_aTime[ 'nonce_check' ];
                return "<div class='debug-info'>"
                        . "<p>Start to Ajax Callback: {$_iStartToCallback}</p>"
                        . "<p>Callback to Nonce Check: {$_iCallbackToNonceCheck}</p>"
                        . "<p>Nonce Check to Return: {$_iNonceCheckToReturn}</p>"
                        . "<p></p>"
                        . "</div>";
            }
}
