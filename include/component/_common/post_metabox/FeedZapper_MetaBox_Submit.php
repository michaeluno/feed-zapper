<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */
class FeedZapper_MetaBox_Submit extends FeedZapper_AdminPageFramework_MetaBox {

    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {

        /**
         * Adds setting fields in the meta box.
         */
        $this->addSettingFields(
            array(
                'field_id'          => '_submit',
                'type'              => 'submit',
                'save'              => false,
                'label'             => isset( $_GET[ 'action' ] ) && 'edit' === $_GET[ 'action' ]
                    ? __( 'Update', 'feed-zapper' )
                    : __( 'Add', 'feed-zapper' ),
                'attributes'        => array(
                    'field'    => array(
                        'style' => 'text-align:center; width: 100%;'
                    ),
                ),
            )
        );

        // Hook to wp_insert_post_data
        add_filter( 'wp_insert_post_data', array( $this, 'replyToForcePublished' ), 10, 2 );
//       @deprecated add_filter( 'wp_insert_post_empty_content', array( $this, 'replyToDisablePending' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'replyToDisablePostAutoSave' ) );

    }

    public function replyToDisablePostAutoSave() {
        if ( ! in_array( get_post_type(), $this->oProp->aPostTypes ) ) {
            return;
        }
        wp_dequeue_script( 'autosave' );
    }

    /**
     * Sets the post status to published
     *
     * @see https://wordpress.stackexchange.com/a/147187
     * @callback    filter      wp_insert_post_data
     */
    public function replyToForcePublished( $aData, $aPost ) {

        if ( ! in_array( $aData[ 'post_type' ], $this->oProp->aPostTypes ) ) {
            return $aData;
        }

        if ( in_array( $aData[ 'post_status' ], array( 'trash', 'auto-draft', ) ) ) {
            return $aData;
        }

        $aData[ 'post_status' ] = 'publish';
        return $aData;

    }

    /**
     * @callback    filter      wp_insert_post_empty_content
     * @deprecated  does not work. If `draft` is set, it works but the entire fields become empty with an admin notice, "Post has been updated."
     */
    public function replyToDisablePending( $bMaybeEmpty, $aPostData ) {
//return $bMaybeEmpty;
//FeedZapper_Debug::log( $aPostData );
        if ( ! in_array( $aPostData[ 'post_type' ], $this->oProp->aPostTypes ) ) {
            return $bMaybeEmpty;
        }

        if ( in_array( $aPostData[ 'post_status' ], array( 'pending', ) ) ) {
            return true;
        }

        return $bMaybeEmpty;
    }


}