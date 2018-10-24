<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Feed Zapper Custom Post Type
 *
 * Stores zappers that the user follows.
 */
class FeedZapper_PostType_Zappers extends FeedZapper_AdminPageFramework_PostType {

    /**
     * Use this method to set up the post type.
     *
     * ALternatevely, you may use the set_up_{instantiated class name} method, which also is called at the end of the constructor.
     */
    public function setUp() {

        $this->setArguments(
            // argument - for the array structure, see http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
            array(
                'labels'            => $this->___getLabels(),
                'public'            => true,
                'menu_position'     => 110,
                'supports'          => false, // e.g. array( 'title', 'editor', 'comments', 'thumbnail', 'excerpt' ),
                'taxonomies'        => array( '' ),
                'has_archive'       => true,
                'show_admin_column' => true, // [3.5+ core] this is for custom taxonomies to automatically add the column in the listing table.
                'hierarchical'      => false,
                'menu_icon'         => $this->oProp->bIsAdmin
                    ? (
                        version_compare( $GLOBALS[ 'wp_version' ], '3.8', '>=' )
                            ? 'dashicons-admin-site'
                            : plugins_url( 'asset/image/wp-logo_16x16.png', FeedZapper_Registry::$sFilePath )
                    )
                    : null, // do not call the function in the front-end.

                // (framework specific) this sets the screen icon for the post type for WordPress v3.7.1 or below.
                // a file path can be passed instead of a url, plugins_url( 'asset/image/wp-logo_32x32.png', APFDEMO_FILE )
                'screen_icon' => $this->oProp->bIsAdmin
                    ? (
                        version_compare( $GLOBALS[ 'wp_version' ], '3.8', '>=' )
                            ? 'dashicons-admin-site'
                            : plugins_url( 'asset/image/wp-logo_32x32.png', FeedZapper_Registry::$sFilePath )
                    )
                    : null, // do not call the function in the front-end.

                // (framework specific) [3.5.10+] default: true
                'show_submenu_add_new'  => true,

                // (framework specific) [3.7.4+]
                'submenu_order_manage' => 30,   // default 5
                'submenu_order_addnew' => 31,   // default 10

                'show_in_menu'            => 'FeedZapper_AdminPage',    // the plugin root admin page
            )
        );

        if ( is_admin() ) {
            add_action( 'do_meta_boxes', array( $this, 'replyToHidePublishMetaBox' ) );
        }

    }
        public function replyToHidePublishMetaBox() {
            remove_meta_box( 'submitdiv', $this->oProp->sPostType, 'side' );
        }

        /**
         * @return      array
         */
        private function ___getLabels() {
            return $this->oProp->bIsAdmin
                ? array(
                    'name'               => __( 'Following Zappers', 'feed-zapper' ),
                    'all_items'          => __( 'Zappers', 'feed-zapper' ),
                    'menu_name'          => __( 'Zappers', 'feed-zapper' ),
                    'singular_name'      => __( 'Zapper', 'feed-zapper' ),
                    'add_new'            => __( 'Follow', 'feed-zapper' ),
                    'add_new_item'       => __( 'Follow a New Zapper', 'feed-zapper' ),
                    'edit'               => __( 'Edit', 'feed-zapper' ),
                    'edit_item'          => __( 'Edit Zapper', 'feed-zapper' ),
                    'new_item'           => __( 'New Zapper', 'feed-zapper' ),
                    'view'               => __( 'View', 'feed-zapper' ),
                    'view_item'          => __( 'View Zapper', 'feed-zapper' ),
                    'search_items'       => __( 'Search Zappers', 'feed-zapper' ),
                    'not_found'          => __( 'No Following Zapper found', 'feed-zapper' ),
                    'not_found_in_trash' => __( 'No Following Zapper found in Trash', 'feed-zapper' ),
                    'parent'             => __( 'Parent Zapper', 'feed-zapper' ),

                    // (framework specific)
                    'plugin_action_link' => __( 'Zappers', 'feed-zapper' ), // framework specific key. [3.7.3+]
                )
            : array();

        }


}