<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Feed Zapper Feed Custom Post TYpe
 *
 * Stores feeds subscribed by users.
 *
 * @remark  Uses a shared taxonomy with the `fz_feed_item` post type.
 */
class FeedZapper_PostType_Feed extends FeedZapper_AdminPageFramework_PostType {

    protected $_oOption;
    /**
     * Stores the user roles that can interact with this post type.
     * @var array
     */
    protected $_aSetAllowedRoles = array();

    public function start() {
        // In front & back end
        // this is because, when logged-in, admin bar shows the New menu item and it needs proper capabilities.
        $this->_oOption = FeedZapper_Option::getInstance();
        $this->_aSetAllowedRoles = $this->_oOption->get( array( 'permission', 'user_roles' ), array() );
        add_filter( 'user_has_cap', array( $this, 'replyToAddCapabilities' ), 10, 4 );

    }


    /**
      * Use this method to set up the post type.
      *
      * Alternately, you may use the set_up_{instantiated class name} method, which also is called at the end of the constructor.
      */
    public function setUp() {

        $_bDebugMode = $this->oUtil->isDebugMode();

        $this->setArguments(
            // argument - for the array structure, see http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
            array(
                'labels'            => $this->___getLabels(),
                'taxonomies'        => array( '' ), // added below
                'hierarchical'      => false,

                // Visibility
                'public'                => true,
                'publicly_queryable'    => true,
                'has_archive'           => true,
                'exclude_from_search'   => true,
                'can_export'            => true,

                // UI
                'supports'          => array( 'title' ), // the title input field will be removed for the Add New page
                'menu_position'     => 120,
                'show_admin_column' => true, // [3.5+ core] this is for custom taxonomies to automatically add the column in the listing table.
                'menu_icon'         => $this->oProp->bIsAdmin
                    ? (
                        version_compare( $GLOBALS[ 'wp_version' ], '3.8', '>=' )
                            ? 'dashicons-rss'
                            : plugins_url( 'asset/image/wp-logo_16x16.png', FeedZapper_Registry::$sFilePath )
                    )
                    : null, // do not call the function in the front-end.

                // (framework specific) this sets the screen icon for the post type for WordPress v3.7.1 or below.
                // a file path can be passed instead of a url, plugins_url( 'asset/image/wp-logo_32x32.png', APFDEMO_FILE )
                'screen_icon' => $this->oProp->bIsAdmin
                    ? (
                        version_compare( $GLOBALS[ 'wp_version' ], '3.8', '>=' )
                            ? 'dashicons-rss'
                            : plugins_url( 'asset/image/wp-logo_32x32.png', FeedZapper_Registry::$sFilePath )
                    )
                    : null, // do not call the function in the front-end.

                // (framework specific) [3.5.10+] default: true
                'show_submenu_add_new'  => true,

                // (framework specific) [3.7.4+]
                'submenu_order_manage' => 20,   // default 5
                'submenu_order_addnew' => 21,   // default 10

                'show_in_menu'            => 'FeedZapper_AdminPage',    // the plugin root admin page

                // Capabilities
                'capability_type'       => array( 'fz_feed', 'fz_feeds', ), // 1st: singular, 2nd: plural
//                'capabilities' => array(
//                    'create_posts'  => 'create_fz_feeds',
//                    'edit_post' => 'edit_fz_feed',
//                    'edit_posts' => 'edit_fz_feeds',
//                    'edit_others_posts' => 'edit_other_fz_feeds',
//                    'publish_posts' => 'publish_fz_feeds',
//                    'read_post' => 'read_fz_feed',
//                    'read_private_posts' => 'read_private_fz_feeds',
//                    'delete_post' => 'delete_fz_feed',
//                    'delete_posts' => 'delete_fz_feeds',   // not sure if this is needed
//                ),
                'map_meta_cap' => true,  // must be true to enable custom capabilities

            )
        );

        new FeedZapper_PostType_PostAction_Renew( $this->oProp->sPostType );
        new FeedZapper_PostType_PostAction_Delete( $this->oProp->sPostType );

        if ( $this->oProp->bIsAdmin ) {
            $_sFeedComponentDirPath = dirname( dirname( __FILE__ ) );
            $this->enqueueStyle( FeedZapper_Registry::getPluginURL( $_sFeedComponentDirPath . '/asset/css/post_type_feed.css', true ) );
            $this->enqueueScript( FeedZapper_Registry::getPluginURL( $_sFeedComponentDirPath . '/asset/js/done-typing/doneTyping.js', true ) );
            $this->enqueueScript(
                FeedZapper_Registry::getPluginURL( $_sFeedComponentDirPath . '/asset/js/feed-preview/feed-preview.js', true ),
                array(
                    'handle_id'    => 'feedPreview',
                    'dependencies' => 'jquery',
                    'translation'  => array(
                        'spinner_url'       => admin_url( 'images/wpspin_light.gif' ),
                        'AJAXURL'           => admin_url( 'admin-ajax.php' ),
                        'debugMode'         => defined( 'WP_DEBUG' ) && WP_DEBUG,
                        'mode'              => 'post.php' === $this->oProp->sPageNow ? 'edit' : 'add',
                    ),
                )
            );
            $this->enqueueStyle( FeedZapper_Registry::getPluginURL( 'include/component/template/output/preview/style.css' ) );   // feed preview on the add new page
// @deprecated            add_action( 'admin_menu', array( $this, 'replyToEditSubmenu' ) );
//            add_action( 'admin_init', array( $this, 'replyToAddCustomCapabilities' ) );

            add_action( 'current_screen', array( $this, 'replyToSetPostTypeAreaSpecificHooks' ) );

        }
        
        $this->addTaxonomy(
            FeedZapper_Registry::$aTaxonomies[ 'feed_channel' ],
            array(
                'labels'                => array(
                    'name'          => __( 'Feed Channel', 'feed-zapper' ),
                    'add_new_item'  => __( 'Add New Channel', 'feed-zapper' ),
                    'new_item_name' => __( 'New Channel', 'feed-zapper' ),
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
        $this->addTaxonomy(
            FeedZapper_Registry::$aTaxonomies[ 'feed_language' ],
            array(
                'labels'                => array(
                    'name'          => __( 'Feed Language', 'feed-zapper' ),
                    'add_new_item'  => __( 'Add New Language', 'feed-zapper' ),
                    'new_item_name' => __( 'New Language', 'feed-zapper' ),
                ),
                'show_ui'               => $_bDebugMode,
                'show_tagcloud'         => true,
                'hierarchical'          => false,
                'show_admin_column'     => $_bDebugMode,
                'show_in_nav_menus'     => $_bDebugMode,
                'show_table_filter'     => false,  // framework specific key
                'show_in_sidebar_menus' => $_bDebugMode,  // framework specific key
//                'submenu_order'         => 40,  // the Setting page is 50
                'capabilities'          => FeedZapper_Feed_Utility::getTaxonomyCapabilities(),

            ),
            array( FeedZapper_Registry::$aPostTypes[ 'item' ] )    // additional object types
        );
        
        $this->___modifyDatabaseQuery();

    }
        /**
         * Only show the current user's items.
         */
        private function ___modifyDatabaseQuery() {
            if ( ! $this->isInThePage() ) {
                return;
            }
            if ( 'edit.php' !== $this->oProp->sPageNow ) {
                return;
            }
            if ( $this->oProp->sPostType !== $_GET[ 'post_type' ] ) {
                return;
            }
            add_filter(
                'request',
                array( $this, 'replyToAddAuthorParameterToDBQuery' )
            );


        }
            /**
             * Forces the author to the current user.
             * It is only possible to view own items. Other users cannot view yours and vice versa.
             * @param $aQueryVars
             * @return mixed
             * @callback    filter      request
             */
            public function replyToAddAuthorParameterToDBQuery( $aQueryVars ) {

                $_iLoggedInUserID = get_current_user_id();
                if ( $_iLoggedInUserID ) {
                    $aQueryVars[ 'author' ] = $_iLoggedInUserID;
                }
                return $aQueryVars;

            }

        /**
         * Sets up hooks that is needed in this custom post type areas in the admin.
         *
         * Includes the following admin pages.
         *
         * - post.php?post_type=fz_feed
         * - post-new.php?post_type=fz_feed
         * - edit.php?post_type=fz_feed
         *
         * @callback    action      current_screen
         */
        public function replyToSetPostTypeAreaSpecificHooks() {

            if ( ! $this->isInThePage() ) {
                return;
            }

            add_action( 'admin_enqueue_scripts', array( $this, 'replyToDisableAutoSave' ) );
            add_action( 'do_meta_boxes', array( $this, 'replyToHidePublishMetaBox' ) );

            add_filter( 'post_date_column_status', '__return_empty_string' );

            $_oScreen = get_current_screen();
            add_filter(
                "views_{$_oScreen->id}",
                array( $this, 'replyToEditPostCountLinks' )
            );
            add_filter( 'disable_months_dropdown', array( $this, 'replyToDisableMonthDropDown' ), 10, 2 );

            $this->___removeTitleInputField();

        }
            private function ___removeTitleInputField() {
                if ( 'post-new.php' !== $this->oProp->sPageNow ) {
                    return;
                }
                remove_post_type_support( $this->oProp->sPostType, 'title' );
            }

            public function replyToDisableMonthDropDown( $bDisable, $sPostType ) {
                if ( $this->oProp->sPostType !== $sPostType ) {
                    return $bDisable;
                }
                return true;
            }

            /**
             * Removes the link at the top of the post listing screen.
             *
             * All(8) | Published(8) -> none
             *
             * @param $aLinks
             * @return array
             * @callback    filter      views_{screen->id}
             */
            public function replyToEditPostCountLinks( $aPostCountLinks ) {
                return array();
            }

        /**
         * @callback    action      admin_enqueue_scripts
         */
        public function replyToDisableAutoSave() {
            if ( get_post_type() !== $this->oProp->sPostType ) {
                return;
            }
            wp_dequeue_script( 'autosave' );
        }

        /**
         * @param $aAllCapabilities
         * @param $aMetaCapabilities
         * @param $aArguments
         * @param $oUser
         *
         * @return array
         * @callback    filter      user_has_cap
         * @remark      in wp-cron.php the user id becomes 0. So _isUserAllowed() returns false in that case.
         */
        public function replyToAddCapabilities( $aAllCapabilities, $aMetaCapabilities, $aArguments, $oUser ) {

//FeedZapper_Debug::log( 'cap filter called: ' . $oUser->ID );
            if ( ! $this->_isUserAllowed( $oUser ) ) {
                return $aAllCapabilities;
            }
//FeedZapper_Debug::log( 'cap filter adding capabilities' );
            $_oUtil = new FeedZapper_Feed_Utility;
            $aAllCapabilities = $_oUtil->getCustomCapabilitiesForFeedCustomPostType() + $aAllCapabilities;

            if ( $this->isInThePage() || in_array( $this->oProp->sPageNow, array( 'admin-ajax.php', 'wp-cron.php' ) ) ) {
                // Somehow WordPress still requires unmapped capabilities although `map_meta_cap` is set to `true`.
                $aAllCapabilities = $_oUtil->getSpecialCapabilities() + $aAllCapabilities;

            }
            return $aAllCapabilities;

        }
            /**
             * @param WP_User $oUser
             * @return boolean
             */
            protected function _isUserAllowed( WP_User $oUser ) {
                $_aUserAllowedRoles = array_intersect( $oUser->roles, $this->_aSetAllowedRoles );
                return ( boolean ) count( $_aUserAllowedRoles );
            }

        /**
         * @callback    action      admin_menu
         * @deprecated  by dynamically adding capabilities, this become unnecessary
         */
        public function replyToEditSubmenu() {
            if ( ! $this->isInThePage() ) {
                return;
            }
            /**
             * For some reasons, WordPress prevents users with insufficient capabilities for normal posts from accessing the `Add New` post-new.php page
             * even though custom capabilities are set for the custom post type. This happens only when the parent menu page slug is set to the `show_in_menu` argument.
             * If the `show_in_menu` argument is true, such users can access the page.
             *
             * So here, remove the restriction done with the sub-menu no-privilege global array.
             */
            unset( $GLOBALS[ '_wp_submenu_nopriv' ][ 'edit.php' ][ 'post-new.php' ] );
        }

        /**
         * @deprecated  `add_cap()` saves the value in the database.
         * @callback    admin_init
         */
        public function replyToAddCustomCapabilities() {
            $_oOption = FeedZapper_Option::getInstance();
            $_aAllowedRoles = $_oOption->get( array( 'permission', 'user_roles' ), array() );
            foreach( $_aAllowedRoles as $_sUserRoleKey ) {
                $_oRole = get_role( $_sUserRoleKey );
                if ( ! is_object( $_oRole ) ) {
                    continue;
                }

//                $_oRole->add_cap( 'create_fz_feeds' );
//                $_oRole->add_cap( 'edit_fz_feed' );
//                $_oRole->add_cap( 'edit_fz_feeds' );
//                $_oRole->add_cap( 'edit_other_fz_feeds' );
//                $_oRole->add_cap( 'publish_fz_feeds' );
//                $_oRole->add_cap( 'read_fz_feed' );
//                $_oRole->add_cap( 'read_private_fz_feeds' );
//                $_oRole->add_cap( 'delete_fz_feed' );
//                $_oRole->add_cap( 'delete_fz_feeds' );
//                $_oRole->add_cap( 'delete_published_fz_feeds' );

//
//                $_oRole->add_cap( 'delete_others_fz_feeds' );
//                $_oRole->add_cap( 'delete_published_posts' );
//                $_oRole->add_cap( 'delete_posts' );
//
//                $_oRole->add_cap( 'edit_post' );
//                $_oRole->add_cap( 'edit_others_posts' );
//                $_oRole->add_cap( 'edit_posts' );


                // for custom taxonomies
//                $_oRole->add_cap( 'manage_fz_feed_tags' );
//FeedZapper_Debug::log( ( array ) $_oRole );
            }
        }

        public function replyToHidePublishMetaBox() {
            remove_meta_box( 'submitdiv', $this->oProp->sPostType, 'side' );
        }

         /**
          * @return      array
          */
         private function ___getLabels() {

             return array(
                     'name'               => __( 'Subscribing Feeds', 'feed-zapper' ),
                     'menu_name'          => __( 'Feeds', 'feed-zapper' ),
                     'all_items'          => __( 'Feeds', 'feed-zapper' ),
                     'singular_name'      => __( 'Feed', 'feed-zapper' ),
                     'add_new'            => __( 'Add New', 'feed-zapper' ),
                     'add_new_item'       => __( 'Add New Feed', 'feed-zapper' ),
                     'edit'               => __( 'Edit', 'feed-zapper' ),
                     'edit_item'          => __( 'Edit Feed', 'feed-zapper' ),
                     'new_item'           => __( 'New Feed', 'feed-zapper' ),
                     'view'               => __( 'View', 'feed-zapper' ),
                     'view_item'          => __( 'View Feed', 'feed-zapper' ),
                     'search_items'       => __( 'Search Feeds', 'feed-zapper' ),
                     'not_found'          => __( 'No Feed found', 'feed-zapper' ),
                     'not_found_in_trash' => __( 'No Feed found in Trash', 'feed-zapper' ),
                     'parent'             => __( 'Parent Feed', 'feed-zapper' ),

                     // (framework specific)
                     'plugin_action_link' => __( 'Feeds', 'feed-zapper' ), // framework specific key. [3.7.3+]
                 );

         }

    /**
     * Defines the column header of the unit listing table.
     *
     * @callback     filter      columns_{post type slug}
     * @return       array
     */
    public function columns_fz_feed( $aHeaderColumns ) {
        $aHeaderColumns[ 'last_updated' ] = __( 'Last Updated', 'feed-zapper' );
        unset( $aHeaderColumns[ 'date' ] );
        return $aHeaderColumns;
    }

    /**
     * @callback    filter  cell_{post type slug}_{column slug}
     * @param string $sCell
     * @param $oPost
     */
    public function cell_fz_feed_last_updated( $sCell, $iPost ) {
        $_sModifiedTimeGMT = get_post_field( 'post_modified_gmt', $iPost, 'raw' );
        $_iModifiedTimeGMT = strtotime( $_sModifiedTimeGMT );
        $_sTimeDiff        = human_time_diff( $_iModifiedTimeGMT, current_time( 'timestamp', true  ) ) . " " . __( 'ago' );
        return '<p class="">'
            . '<em>'. $_sTimeDiff .'</em>'
            . '</p>';
    }

    public function content( $sContents ) {

        $_oUtil = new FeedZapper_WPUtility;
        $_sURL  = $_oUtil->getPostMeta( $GLOBALS[ 'post' ]->ID, '_fz_feed_url' );
        $_sOutput = getFeedZapperFeedByURL(
                array(
                    'url'   => $_sURL,
                    'cache_duration' =>  $_oUtil->getPostMeta( $GLOBALS[ 'post' ]->ID, '_fz_cache_duration' ),
                ),
                false
            );
        if ( $_oUtil->isDebugMode() ) {
            $_sOutput .= "<h3>" . __( 'Debug Information', 'feed-zapper' ) . "</h3>"
                . "<h4>" . __( 'Post Meta', 'feed-zapper' ) . "</h4>"
                . $this->oDebug->get( FeedZapper_WPUtility::getPostMeta( $GLOBALS[ 'post' ]->ID ) );
        }
        return $sContents . $_sOutput;

    }

}