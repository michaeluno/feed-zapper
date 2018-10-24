<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 *
 *
 */
abstract class FeedZapper_PostType_PostAction_Base extends FeedZapper_PluginUtility {

    protected $_sActionSlug = '';

    protected $_sPostTypeSlug = '';

    public function __construct( $_sPostTypeSlug ) {

        $this->_sPostTypeSlug = $_sPostTypeSlug;
        add_action(
            'current_screen',
            array( $this, 'replyToAddHooks' )
        );
        add_filter(
            'action_links_' . $this->_sPostTypeSlug,
            array( $this, 'replyToModifyActionLinks' ),
            10,
            2
        );
        add_action(
            'post_action_' . $this->_sActionSlug,
            array( $this, 'replyToDoAction' )
        );
    }
    /**
     * @param           $aActionLinks
     * @param           $oPost
     * @callback        add_filter      action_links_{post type slug}
     * @return          array
     */
    public function replyToModifyActionLinks( $aActionLinks, $oPost ) {
        $_sLink = $this->_getActionLink( $oPost );
        if ( $_sLink ) {
            $aActionLinks[ $this->_sActionSlug ] = $_sLink;
        }
        return $aActionLinks;
    }

    /**
     * @callback    action      current_screen
     */
    public function replyToAddHooks() {
        $_sScreenID = get_current_screen()->id;
        if ( "edit-{$this->_sPostTypeSlug}" !== $_sScreenID ) {
            return;
        }
        add_filter(
            'handle_bulk_actions-' . $_sScreenID,
            array( $this, 'replyToFilterSendbackURL' ),
            10,
            3
        );
        add_filter(
            "bulk_actions-{$_sScreenID}",
            array( $this, 'replyToCustomizeBulkActions' )
        );
    }

    /**
     * @param $aActions
     *
     * @return array
     * @callback    filter      bulk_actions-{screen id}
     */
    public function replyToCustomizeBulkActions( $aActionLabels ) {
        $aActionLabels[ $this->_sActionSlug ] = $this->_getActionLabel();
        return $aActionLabels;
    }

    /**
     * @param $sSendbackURL
     * @param $sDoAction
     * @param $aPostIDs
     *
     * @return mixed
     * @callback    filter      handle_bulk_actions-{screen id}
     */
    public function replyToFilterSendbackURL( $sSendbackURL, $sDoAction, $aPostIDs ) {
        if ( $sDoAction !== $this->_sActionSlug ) {
            return $sSendbackURL;
        }
        $aPostIDs = is_array( $aPostIDs )
            ? $aPostIDs
            : array( $aPostIDs );
        $this->_doAction( $aPostIDs );
        return $sSendbackURL;
    }

    /**
     * Called by clicking on an individual action link. (redirected to post.php)
     * @param $aiPostIDs
     * @callback    action  post_action_{action slug}
     */
    public function replyToDoAction( $iPostID ) {
        $_aPostIDs = is_array( $iPostID )
            ? $iPostID
            : array( $iPostID );
        $this->_doAction( $_aPostIDs );

        // After this call, the execution flow is dead-end
        $_sSendbackURL = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'ids' ), wp_get_referer() );
        wp_redirect( $_sSendbackURL );
        exit();
    }

    protected function _doAction( array $aPostIDs ) {
        // perform an action
    }

    /**
     * @return string
     */
    protected function _getActionLabel() {
        return '';
    }

    /**
     * @return string
     */
    protected function _getActionLink( $oPost ) {
        return '';
    }

}