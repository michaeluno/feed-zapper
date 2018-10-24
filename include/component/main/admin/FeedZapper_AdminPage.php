<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 * 
 */


/**
 * Deals with the plugin admin pages.
 * 
 * @since    0.0.1
 */
class FeedZapper_AdminPage extends FeedZapper_AdminPageFramework {

    /**
     * User constructor.
     */
    public function start() {
        
        if ( ! $this->oProp->bIsAdmin ) {
            return;
        }

        new FeedZapper_AdminPage_User(
            '', // do not save options - options will be saved in user meta in the `validate()` method.
            $this->sFilePath,   // caller file path
            'read'    // capability
        );

        add_filter( 
            "options_" . $this->oProp->sClassName,
            array( $this, 'replyToSetOptions' )
        );
        
    }
        /**
         * Sets the default option values for the setting form.
         * @return      array       The options array.
         */
        public function replyToSetOptions( $aOptions ) {
            $_oOption = FeedZapper_Option::getInstance();
            return $_oOption->get();
        }

    public function load() {
        new FeedZapper_Select2CustomFieldType( $this->oProp->sClassName );
    }

    /**
     * Sets up admin pages.
     */
    public function setUp() {
        
        $this->setRootMenuPage(
            __( 'Feed Zapper', 'feed-zapper' ),
            'dashicons-rss'
        );



        // Add submenu/pages
        new FeedZapper_AdminPage_Global__Page_Setting( $this );

        $this->___addSubMenus();

        $this->___doPageSettings();
        
    }

        private function ___addSubMenus() {
            // http://.../wp-admin/post-new.php?post_type=fz_feed_urls
            $this->addSubMenuLink(
                array(
                    'href'  => add_query_arg(
                        array(
                            'post_type' => FeedZapper_Registry::$aPostTypes[ 'feed' ],
                        ),
                        admin_url( 'post-new.php' )
                    ),
                    'title' => __( 'Add New Feed', 'feed-zapper' ),
                    'order' => 25,
                    'capability' => 'edit_fz_feeds',
                )
            );
            $this->addSubMenuLink(
                array(
                    'href'  => add_query_arg(
                        array(
                            'post_type' => FeedZapper_Registry::$aPostTypes[ 'zapper' ],
                        ),
                        admin_url( 'post-new.php' )
                    ),
                    'title' => __( 'Follow New Zapper', 'feed-zapper' ),
                    'order' => 35,
                    'capability' => 'edit_fz_feeds',
                )
            );

            $_oOption    = FeedZapper_Option::getInstance();
            $_iFeedPage = $_oOption->get( array( 'feed', 'page', 'value' ), 0 );
            $this->addSubMenuLink(
                array(
                    'href'  => $_iFeedPage
                        ? get_permalink( $_iFeedPage )
                        : add_query_arg(
                            array(
                                'page' => FeedZapper_Registry::$aAdminPages[ 'setting' ],
                                'tab' => 'general',
                            ),
                            admin_url( 'admin.php' )
                        ),
                    'title' => __( 'View', 'feed-zapper' ),
                    'order' => 25,
                    'capability' => 'read',
                )
            );
        }

        /**
         * Page styling
         * @since    0.0.1
         * @return   void
         */
        private function ___doPageSettings() {
                        
            $this->setPageTitleVisibility( false ); // disable the page title of a specific page.
            $this->setInPageTabTag( 'h2' );                
            // $this->setPluginSettingsLinkLabel( '' ); // pass an empty string to disable it.
            
            $this->enqueueStyle( FeedZapper_Registry::getPluginURL( 'asset/css/admin.css' ) );
        
        }

}