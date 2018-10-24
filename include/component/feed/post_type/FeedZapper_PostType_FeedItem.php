<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Stores retrieved feed items as a post.
 *
 * Not visible to public.
 * @todo    Implement a mechanism that when a user pluses an item, the action is stored in a custom table. And listing plussed posts will be queried by joining the post table and the custom table.
 * @remark  Uses a shared taxonomy with the `fz_feed` post type.
 */
class FeedZapper_PostType_FeedItem extends FeedZapper_AdminPageFramework_PostType {

    /**
     * Use this method to set up the post type.
     *
     * Alternately, you may use the set_up_{instantiated class name} method, which also is called at the end of the constructor.
     */
    public function setUp() {

        new FeedZapper_PostType_PostAction_Delete( $this->oProp->sPostType );

        $_bDebugMode = $this->oUtil->isDebugMode();

        $this->setArguments(
            // argument - for the array structure, see http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
            array(
                'labels'                => $this->___getLabels(),
                'taxonomies'            => array(
//                    '',
//                    FeedZapper_Registry::$aTaxonomies[ 'feed_tag' ],
//                    FeedZapper_Registry::$aTaxonomies[ 'feed_language' ],
//                    FeedZapper_Registry::$aTaxonomies[ 'feed_owner' ],
//                    FeedZapper_Registry::$aTaxonomies[ 'feed_source' ],
                ),
                'hierarchical'          => false,

                // Visibility
                'public'                => true,
                'publicly_queryable'    => true,    // enables `View` action link
                'has_archive'           => true,   //
                'exclude_from_search'   => false,    // this also enables the listing with Tag Cloud
                'can_export'            => false,

                // UI
                'show_admin_column'     => false, // [3.5+ core] this is for custom taxonomies to automatically add the column in the listing table.
                'show_ui'               => $_bDebugMode,
                'show_in_nav_menus'     => $_bDebugMode,
                'show_in_menu'          => $_bDebugMode,
                'menu_position'         => 500,
                // (framework specific) [3.7.4+]
                'submenu_order_manage' => 5,   // default 5
                'submenu_order_addnew' => 10,   // default 10
                // (framework specific) [3.5.10+] default: true
                'show_submenu_add_new'  => false,
                'supports'              => $_bDebugMode
                    ? array( 'title', 'editor', 'thumbnail', ) // 'comments', 'thumbnail', 'excerpt'
                    : false,


            )
        );

        $this->addTaxonomy(
            FeedZapper_Registry::$aTaxonomies[ 'feed_tag' ],
            array(
                'labels'                => array(
                    'name'          => __( 'Feed Tag', 'feed-zapper' ),
                    'add_new_item'  => __( 'Add New Tag', 'feed-zapper' ),
                    'new_item_name' => __( 'New Tag', 'feed-zapper' ),
                ),
                'show_ui'               => true,
                'show_tagcloud'         => true,
                'hierarchical'          => false,
                'show_admin_column'     => true,
                'show_in_nav_menus'     => false,
                'show_table_filter'     => true,  // framework specific key
                'show_in_sidebar_menus' => true,  // framework specific key
                'submenu_order'         => 40,  // the Setting page is 50
                'capabilities'          => FeedZapper_Feed_Utility::getTaxonomyCapabilities(),
            )
        );

        // Internal - to associate multiple authors 
        $this->addTaxonomy( 
            FeedZapper_Registry::$aTaxonomies[ 'feed_owner' ], // taxonomy slug
            array(            // argument - for the argument array keys, refer to : http://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments
                'labels'                => array(
                    'name'            => __( 'Feed Owners', 'feed-zapper' ),
                ),
                'show_ui'               => $_bDebugMode,
                'show_tagcloud'         => false,
                'hierarchical'          => false,
                'show_admin_column'     => $_bDebugMode,
                'show_in_nav_menus'     => $_bDebugMode,
                'show_table_filter'     => $_bDebugMode,     // framework specific key
                'show_in_sidebar_menus' => $_bDebugMode,     // framework specific key

                'capabilities'          => FeedZapper_Feed_Utility::getTaxonomyCapabilities(),
            )
        );

        /**
         * This gives the ability to query feed item posts by performed feed actions.
         * @remark  not used at the moment
         */
        $this->addTaxonomy(
            FeedZapper_Registry::$aTaxonomies[ 'feed_action' ], // taxonomy slug
            array(            // argument - for the argument array keys, refer to : http://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments
                'labels'                => array(
                    'name'            => __( 'Feed Actions', 'feed-zapper' ),
                ),
                'show_ui'               => $_bDebugMode,
                'show_tagcloud'         => false,
                'hierarchical'          => false,
                'show_admin_column'     => $_bDebugMode,
                'show_in_nav_menus'     => $_bDebugMode,
                'show_table_filter'     => false,    // framework specific key
                'show_in_sidebar_menus' => $_bDebugMode,    // framework specific key

                // Giving the same capabilities with the feed tag taxonomy
                // so that plugin's dynamically assigned user role capabilities will take effect on this taxonomy as wll
                // This is mainly for creating and assigning new taxonomy terms when creating posts from feed in the background.
                'capabilities'          => FeedZapper_Feed_Utility::getTaxonomyCapabilities(),
            )
        );

        /**
         * This gives the ability to query feed item posts by URL, needed when displaying feeds in the front-end.
         * @remark  not used at the moment
         */
        $this->addTaxonomy(
            FeedZapper_Registry::$aTaxonomies[ 'feed_source' ], // taxonomy slug
            array(            // argument - for the argument array keys, refer to : http://codex.wordpress.org/Function_Reference/register_taxonomy#Arguments
                'labels'                => array(
                    'name'            => __( 'Feed Sources', 'feed-zapper' ),
                ),
                'show_ui'               => $_bDebugMode,
                'show_tagcloud'         => false,
                'hierarchical'          => false,
                'show_admin_column'     => $_bDebugMode,
                'show_in_nav_menus'     => $_bDebugMode,
                'show_table_filter'     => false,    // framework specific key
                'show_in_sidebar_menus' => $_bDebugMode,    // framework specific key

                // Giving the same capabilities with the feed tag taxonomy
                // so that plugin's dynamically assigned user role capabilities will take effect on this taxonomy as wll
                // This is mainly for creating and assigning new taxonomy terms when creating posts from feed in the background.
                'capabilities'          => FeedZapper_Feed_Utility::getTaxonomyCapabilities(),
            )
        );

        // List Table Customization
        if ( $this->oProp->bIsAdmin && 'edit.php' === $this->oProp->sPageNow ) {
            add_filter( 'get_edit_post_link', array( $this, 'replyToModifyPostTitles' ), 10, 3 );
            add_filter( 'get_the_excerpt', array( $this, 'replyToModifyPostExcerpts' ), 10, 2 );
            $_sFeedComponentDirPath = dirname( dirname( __FILE__ ) );
            $this->enqueueStyle( FeedZapper_Registry::getPluginURL( $_sFeedComponentDirPath . '/asset/css/post_type_feed_item.css', true ) );
            $this->enqueueScript(
                FeedZapper_Registry::getPluginURL( $_sFeedComponentDirPath . '/asset/js/jquery-lazy/jquery.lazy.min.js', true ),
                array(
                    'handle_id'    => 'jquery-lazy',
                    'dependencies' => 'jquery',
                    'in_footer'    => true,
                )
            );
            $this->enqueueScript(
                FeedZapper_Registry::getPluginURL( $_sFeedComponentDirPath . '/asset/js/feed-item.js', true ),
                array(
                    'handle_id'    => 'feed-item',
                    'dependencies' => 'jquery-lazy',
                    'in_footer'    => true,
                )
            );
        }
    }

        /**
         * @return      array
         */
        private function ___getLabels() {

            return $this->oProp->bIsAdmin
                ? array(
                    'name'               => __( 'Feed Zapper Posts', 'feed-zapper' ),
                    'menu_name'          => __( 'Feed Zapper Posts', 'feed-zapper' ),
                    'all_items'          => __( 'Feed Zapper Posts', 'feed-zapper' ),
                    'singular_name'      => __( 'Feed Zapper Post', 'feed-zapper' ),
                    'add_new'            => __( 'Add Post', 'feed-zapper' ),
                    'add_new_item'       => __( 'Add a New Feed Zapper Post', 'feed-zapper' ),
                    'edit'               => __( 'Edit', 'feed-zapper' ),
                    'edit_item'          => __( 'Edit Feed Zapper Post', 'feed-zapper' ),
                    'new_item'           => __( 'New Feed Zapper Post', 'feed-zapper' ),
                    'view'               => __( 'View', 'feed-zapper' ),
                    'view_item'          => __( 'View Feed Zapper Post', 'feed-zapper' ),
                    'search_items'       => __( 'Search Feed Zapper Posts', 'feed-zapper' ),
                    'not_found'          => __( 'No Feed Zapper Post found', 'feed-zapper' ),
                    'not_found_in_trash' => __( 'No Feed Zapper Post found in Trash', 'feed-zapper' ),
                    'parent'             => __( 'Parent Feed Zapper Post', 'feed-zapper' ),

                    // (framework specific)
//                    'plugin_action_link' => __( 'FeedZapper Posts', 'feed-zapper' ), // framework specific key. [3.7.3+]
                )
            : array();

        }

    /**
     * Defines the column header of the unit listing table.
     *
     * @callback     filter      columns_{post type slug}
     * @return       array
     */
    public function columns_fz_feed_item( $aHeaderColumns ) {
        $_aColumns = array();
        // Parse items to preserve the column order.
        foreach( $aHeaderColumns as $_sKey => $_sLabel ) {
            $_aColumns[ $_sKey ] = $_sLabel;
            if ( 'title' === $_sKey ) {
                $_aColumns[ 'thumbnail' ] = __( 'Thumbnail', 'feed-zapper' );
            }
        }
        return $_aColumns;
    }
    /**
     * @callback    filter  cell_{post type slug}_{column slug}
     * @return      string
     */
    public function cell_fz_feed_item_thumbnail( $sCell, $iPost ) {
        $_sURL = get_post_meta( $iPost, '_fz_post_thumbnail', true );
        return $_sURL
            ? "<img class='feed-thumbnail' data-src='" . esc_url( $_sURL ) . "' />"
            : '';
    }

    /**
     * @param $aActionLinks
     * @param $oPost
     * @callback    filter  action_links_{post type slug}
     */
    public function action_links_fz_feed_item( $aActionLinks, $oPost ) {

        unset( $aActionLinks[ 'view' ] );

        if ( 'trash' === $oPost->post_status ) {
            return $aActionLinks;
        }

        // `View` goes to the linked article
        $aActionLinks[ 'view' ] = sprintf(
            '<a href="%1$s" target="_blank">' . __( 'View' ) . '</a>',
            esc_url( get_post_meta( $oPost->ID, '_fz_post_permalink', true ) )
        );

        // Normalize the Edit action link as the link is modified
        if ( current_user_can( 'edit_post', $oPost->ID ) ) {
            $aActionLinks[ 'edit' ] = sprintf(
                '<a href="%s" aria-label="%s">%s</a>',
                get_edit_post_link( $oPost->ID, 'post-list-table-action-link' ),
                /* translators: %s: post title */
                esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $oPost->post_title ) ),
                __( 'Edit' )
            );
        }

        return $aActionLinks;
    }

    /**
     * @param $sLink
     * @param $iPostID
     * @param string $sContext
     * @callback    filter      get_edit_post_link
     * @return  string
     */
    public function replyToModifyPostTitles( $sLink, $iPostID, $sContext='display' ) {
        if ( 'display' !== $sContext ) {
            return $sLink;
        }
        if ( $this->oProp->sPostType !== get_post_type( $iPostID ) ) {
            return $sLink;
        }
        return get_permalink( $iPostID );

    }
    /**
     * @callback    filter      get_the_excerpt
     * @return  string
     */
    public function replyToModifyPostExcerpts( $sPostExcept, $oPost ) {
        return FeedZapper_PluginUtility::getTruncatedString( $sPostExcept, 200 );
    }

    /**
     * @param $sContent
     *
     * @return string
     */
    public function content( $sContent ) {

        $_sFeedPermalink = get_post_meta( $GLOBALS[ 'post' ]->ID, '_fz_post_permalink', true );
        return $sContent
            . "<p>"
                . "<a href='" . esc_url( $_sFeedPermalink ) . "' target='_blank' >"
                    . __( 'read more', 'feed-zapper' )
                . "</a>"
            . "</p>"
//            . $this->oDebug->get( func_get_args() )
            ;

    }

}