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
 * Stores plugin activity log entries.
 */
class FeedZapper_PostType_Log extends FeedZapper_AdminPageFramework_PostType {

    /**
     * Use this method to set up the post type.
     *
     * Alternately, you may use the set_up_{instantiated class name} method, which also is called at the end of the constructor.
     */
    public function setUp() {

        $this->setArguments(
            // argument - for the array structure, see http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
            array(
                'labels'            => $this->___getLabels(),
                'public'            => true,
                'taxonomies'        => array( '' ),
                'has_archive'       => true,
                'hierarchical'      => false,
//                'publicly_queryable'  => true,

                // UI
                'menu_position'     => 110,
                'supports'          => false, // e.g. array( 'title', 'editor', 'comments', 'thumbnail', 'excerpt' ),
                'show_admin_column' => true, // [3.5+ core] this is for custom taxonomies to automatically add the column in the listing table.
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
                'submenu_order_manage' => 90,   // default 5
                'submenu_order_addnew' => 91,   // default 10

                'show_in_menu'            => 'FeedZapper_AdminPage',    // the plugin root admin page

                // Capabilities - log should be only visible to administrators
                'capabilities' => array(
//                    'create_posts'  => '_disabled', // removes the Add New button
//                    'edit_post' => '_disabled',
//                    'edit_posts' => 'manage_options',   // required to view the listing table
//                    'edit_others_posts' => '_disabled',
//                    'publish_posts' => 'manage_options',    // checked when wp_insert_post() is performed
//                    'read_post' => 'manage_options',
//                    'read_private_posts' => 'manage_options',
//                    'delete_post' => 'manage_options',
//                    'delete_posts' => 'manage_options',   // not sure if this is needed
                ),
            )
        );

    }

        /**
         * @return      array
         */
        private function ___getLabels() {
            return $this->oProp->bIsAdmin
                ? array(
                    'name'               => __( 'Log', 'feed-zapper' ),
                    'all_items'          => __( 'Logs', 'feed-zapper' ),
                    'menu_name'          => __( 'Logs', 'feed-zapper' ),
                    'singular_name'      => __( 'Log', 'feed-zapper' ),
                    'add_new'            => __( 'Log', 'feed-zapper' ),
                    'add_new_item'       => __( 'Add New', 'feed-zapper' ),
                    'edit'               => __( 'New', 'feed-zapper' ),
                    'edit_item'          => __( 'New Log', 'feed-zapper' ),
                    'new_item'           => __( 'New Log', 'feed-zapper' ),
                    'view'               => __( 'View', 'feed-zapper' ),
                    'view_item'          => __( 'View Log', 'feed-zapper' ),
                    'search_items'       => __( 'Search Logs', 'feed-zapper' ),
                    'not_found'          => __( 'No log found', 'feed-zapper' ),
                    'not_found_in_trash' => __( 'No log found in Trash', 'feed-zapper' ),
                    'parent'             => __( 'Parent Log', 'feed-zapper' ),

                    // (framework specific)
//                    'plugin_action_link' => __( 'Logs', 'feed-zapper' ), // framework specific key. [3.7.3+]
                )
            : array();

        }


}