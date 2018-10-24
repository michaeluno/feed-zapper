<?php
/**
 * Feed Zapper
 * 
 * http://en.michaeluno.jp/externals/
 * Copyright (c) 2018 Michael Uno
 * 
 */


/**
 * Deals with the plugin admin pages.
 * 
 * @since        0.0.1
 */
class FeedZapper_TemplateAdminPage extends FeedZapper_AdminPageFramework {

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
         */
        public function replyToSetOptions( $aOptions ) {
            $_oTemplateOption    = FeedZapper_Template_Option::getInstance();
            return $aOptions + $_oTemplateOption->aDefault; 
        }
        
    /**
     * Sets up admin pages.
     */
    public function setUp() {

       
        $this->setRootMenuPageBySlug( 'edit.php?post_type=' . FeedZapper_Registry::$aPostTypes[ 'external' ] );
        
        // Add pages      
        new FeedZapper_TemplateAdminPage_Template(
            $this,
            array(
                'page_slug' => FeedZapper_Registry::$aAdminPages[ 'template' ],
                'title'     => __( 'Templates', 'externals' ),
                'style'     => FeedZapper_Registry::getPluginURL( 'asset/css/externals_templates.css' ),
                'order'     => 70,
            )                
        );

        $this->_doPageSettings();
        
    }

        /**
         * Page styling
         * @since       1
         * @return      void
         */
        private function _doPageSettings() {
                        
            $this->setPageTitleVisibility( false ); // disable the page title of a specific page.
            $this->setInPageTabTag( 'h2' );                
            $this->setPluginSettingsLinkLabel( __( 'Templates', 'externals' ) ); // pass an empty string to disable it.
      

            $this->enqueueStyle( FeedZapper_Registry::getPluginURL( 'asset/css/admin.css' ) );

        }
    
 
        
}