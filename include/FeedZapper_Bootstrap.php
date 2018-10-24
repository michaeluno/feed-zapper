<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 * Loads the plugin.
 * 
 * @since       0.0.1
 */
final class FeedZapper_Bootstrap extends FeedZapper_AdminPageFramework_PluginBootstrap {
    
    /**
     * User constructor.
     */
    protected function construct()  {}        

        
    /**
     * Register classes to be auto-loaded.
     * 
     * @since    0.0.1
     */
    public function getClasses() {
        
        // Include the include lists. The including file reassigns the list(array) to the $_aClassFiles variable.
        $_aClassFiles   = array();
        $_bLoaded       = include( dirname( $this->sFilePath ) . '/include/class-list.php' );
        if ( ! $_bLoaded ) {
            return $_aClassFiles;
        }
        return $_aClassFiles;
                
    }

    /**
     * Sets up constants.
     */
    public function setConstants() {}
    
    /**
     * Sets up global variables.
     */
    public function setGlobals() {}
    
    /**
     * The plugin activation callback method.
     */    
    public function replyToPluginActivation() {

        $_bSufficient = $this->___checkRequirements();
        if ( ! $_bSufficient ) {
            return;
        }
        new FeedZapper_DatabaseTableInstall( true ); // install a custom database table
        FeedZapper_PluginUtility::scheduleFeedChecks();
        $this->___setFeedPage();
    }
        private function ___setFeedPage() {
            $_oOption    = FeedZapper_Option::getInstance();
            $_iSetPageID = $_oOption->get( array( 'feed', 'page', 'value' ), 0 );
            if ( FeedZapper_PluginUtility::isPostPublished( $_iSetPageID ) ) {
                return;
            }
            $_aPage = FeedZapper_PluginUtility::getFeedPage();
            $_aPage = array(
                'value'     => $_aPage[ 'id' ],
                'encoded'   => json_encode( array( $_aPage ) ),
            );
            $_oOption->update( array( 'feed', 'page' ), $_aPage );
        }

        /**
         * 
         * @since            0.0.1
         */
        private function ___checkRequirements() {

            $_oRequirementCheck = new FeedZapper_AdminPageFramework_Requirement(
                FeedZapper_Registry::$aRequirements,
                FeedZapper_Registry::NAME
            );
            
            if ( $_oRequirementCheck->check() ) {            
                $_oRequirementCheck->deactivatePlugin( 
                    $this->sFilePath, 
                    __( 'Deactivating the plugin', 'feed-zapper' ),  // additional message
                    true    // is in the activation hook. This will exit the script.
                );
                return false;
            }
            return true;
             
        }    

        
    /**
     * The plugin activation callback method.
     */    
    public function replyToPluginDeactivation() {
        
        FeedZapper_WPUtility::cleanTransients( 
            array(
                FeedZapper_Registry::TRANSIENT_PREFIX,
                'apf_',
            )
        );

        // Remove the scheduled action hook
        wp_clear_scheduled_hook( FeedZapper_Registry::$aScheduledActionHooks[ 'feed_renew' ], array() );
        
    }        
    
        
    /**
     * Load localization files.
     * 
     * @callback    action      init
     */
    public function setLocalization() {
        
        // This plugin does not have messages to be displayed in the front-end.
        if ( ! $this->bIsAdmin ) { 
            return; 
        }
        
        load_plugin_textdomain( 
            FeedZapper_Registry::TEXT_DOMAIN, 
            false, 
            dirname( plugin_basename( $this->sFilePath ) ) . '/' . FeedZapper_Registry::TEXT_DOMAIN_PATH
        );
        
    }        
    
    /**
     * Loads the plugin specific components. 
     * 
     * @remark        All the necessary classes should have been already loaded.
     */
    public function setUp() {
        
        // This constant is set when `uninstall.php` is loaded.
        if ( defined( 'DOING_PLUGIN_UNINSTALL' ) && DOING_PLUGIN_UNINSTALL ) {
            return;
        }
            
        // Include PHP files.
         $this->___include();
            
        // Option Object - must be done before the template object.
        // The initial instantiation will handle formatting options from earlier versions of the plugin.
        FeedZapper_Option::getInstance();

        $this->___loadComponents();

        // Admin pages
        if ( $this->bIsAdmin ) {            
        
            new FeedZapper_AdminPage(
                FeedZapper_Registry::$aOptionKeys[ 'setting' ], 
                $this->sFilePath,   // caller file path
                'read'    // capability
            );

        }

        // Events
        new FeedZapper_Events;
        
    }
        private function ___loadComponents() {

            new FeedZapper_Feed_Loader( $this->sFilePath );
            new FeedZapper_Zapper_Loader( $this->sFilePath );
            new FeedZapper_Log_Loader( $this->sFilePath );
            new FeedZapper_Template_Loader( $this->sFilePath );

        }

        private function ___include() {
            include( dirname( $this->sFilePath ) . '/include/function/getFeedZapperFeed.php' );
        }
}