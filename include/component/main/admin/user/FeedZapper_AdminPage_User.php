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
class FeedZapper_AdminPage_User extends FeedZapper_AdminPageFramework {

    /**
     * User constructor.
     */
    public function start() {
        
        if ( ! $this->oProp->bIsAdmin ) {
            return;
        }     
        add_filter( 
            "options_" . $this->oProp->sClassName,
            array( $this, 'replyToSetOptions' )
        );
        
    }
        /**
         * Sets the default option values for the setting form.
         * @return      array       The options array.
         * @todo        Set user meta of the current logged-in user
         */
        public function replyToSetOptions( $aOptions ) {
return $aOptions;
            $_oOption    = FeedZapper_Option::getInstance();
            return $aOptions + $_oOption->get();
        }
        
    /**
     * Sets up admin pages.
     */
    public function setUp() {
        
        $this->setRootMenuPageBySlug( 'FeedZapper_AdminPage' );
                    
        // Add pages      
        new FeedZapper_AdminPage_User__Page_Setting( $this );

        $this->_doPageSettings();
        
    }

        /**
         * Page styling
         * @since    0.0.1
         * @return   void
         */
        private function _doPageSettings() {
                        
            $this->setPageTitleVisibility( false ); // disable the page title of a specific page.
            $this->setInPageTabTag( 'h2' );                
            // $this->setPluginSettingsLinkLabel( '' ); // pass an empty string to disable it.
            
            $this->enqueueStyle( FeedZapper_Registry::getPluginURL( 'asset/css/admin.css' ) );
        
        }

}