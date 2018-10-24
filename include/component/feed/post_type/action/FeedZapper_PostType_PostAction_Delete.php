<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Enables the post action of `delete`.
 */
class FeedZapper_PostType_PostAction_Delete extends FeedZapper_PostType_PostAction_Base {

    protected $_sActionSlug = 'fz_delete';

    /**
     * @return string
     */
    protected function _getActionLabel() {
        return __( 'Delete' );
    }

    /**
     * @return string
     * @see get_delete_post_link()
     */
    protected function _getActionLink( $oPost ) {
        return sprintf(
            '<a href="%s" class="submitdelete" aria-label="%1$s">%2$s</a>',
            get_delete_post_link( $oPost->ID, '', true ),
            /* translators: %s: post title */
            esc_attr( __( 'Delete' ) ),
            __( 'Delete' )
        );
    }

    public function _doAction( array $aPostIDs ) {
        foreach( $aPostIDs as $_iPostID ) {
            wp_delete_post( $_iPostID, true );
        }
    }

    public function replyToModifyActionLinks( $aActionLinks, $oPost ) {
        unset( $aActionLinks[ 'trash' ] );
        return parent::replyToModifyActionLinks( $aActionLinks, $oPost );
    }

    public function replyToCustomizeBulkActions( $aActionLabels ) {
        unset( $aActionLabels[ 'trash' ] );
        return parent::replyToCustomizeBulkActions( $aActionLabels );
    }

}