<?php
/**
 * Feed Zapper
 * 
 * http://en.michaeluno.jp/feed-zapper/
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 * Loads template resources such as style.css, template.php, functions.php etc.
 *  
 * @package     FeedZapper
 * @since       0.0.1
 * @filter      apply       feed_zapper_filter_template_custom_css
 *
 * @todo    Currently to load template resources, the `wp` action hook is used and this hook timing is too late
 * to add ajax action callbacks in `functions.php` Think about a better implementation.
 * -> [0.2.4] change to `wp_loaded` as `wp` is not triggered in admin while the template function.php needs to be loaded in admin.
 */
class FeedZapper_Template_ResourceLoader extends FeedZapper_Template_Utility {

    /**
     * Stores the template option object.
     * @var FeedZapper_Template_Option
     */
    public $_oTemplateOption;
  
    public function __construct() {

        if ( $this->hasBeenCalled( __METHOD__ ) ) {
            return;
        }

        $_sActionHook = defined( 'DOING_AJAX' ) && DOING_AJAX ? 'wp' : 'wp_loaded';
        if ( did_action( $_sActionHook ) ) {
            trigger_error( 'Feed Zapper: The class is called too late. Call this class before the `wp` hook.' );
        }
        add_action( $_sActionHook, array( $this, 'replyToLoad' ) );
        
    }
        public function replyToLoad() {

            $this->___enableTemplateInTheFeedPage();
            $this->___enableTemplateInFeedsPostTypePages();

            $this->_oTemplateOption = FeedZapper_Template_Option::getInstance();

            $this->___loadFunctionsOfActiveTemplates();
            $this->___loadStylesOfActiveTemplates();
            $this->___loadSettingsOfActiveTemplates();

        }

            private function ___enableTemplateInTheFeedPage() {
                add_filter( 'feed_zapper_filter_active_templates', array( $this, 'replyToAddActiveTemplatesForTheFeedPage' ), 10, 2 );
            }
                public function replyToAddActiveTemplatesForTheFeedPage( $aActiveTemplates, FeedZapper_Template_Option $oTemplateOption ) {

        // @deprecated 0.2.4 To load function.php in admin, the hook changed to `wp_loaded` from `wp` and is_page() does not work with `wp_loaded`
//                    $_oOption = FeedZapper_Option::getInstance();
//                    $_iFeedPageID = ( integer ) $_oOption->get( array( 'feed', 'page', 'value' ), 0 );
//                    if ( ! is_page( $_iFeedPageID ) ) {
//                        return $aActiveTemplates;
//                    }
                    // Carousel
                    $_aTemplate = $oTemplateOption->getTemplateArrayByDirPath(
                        // FeedZapper_Registry::$sDirPath . '/include/component/template/output/post' deprecated
                        FeedZapper_Registry::$sDirPath . '/include/component/template/output/carousel'
                    );
                    $aActiveTemplates[ $_aTemplate[ 'id' ] ] = $_aTemplate;

                    // Feeds
                    $_aTemplate = $oTemplateOption->getTemplateArrayByDirPath(
                        FeedZapper_Registry::$sDirPath . '/include/component/template/output/feed'
                    );
                    $aActiveTemplates[ $_aTemplate[ 'id' ] ] = $_aTemplate;

                    add_filter( 'feed_zapper_filter_template_path', array( $this, 'replyToSetTemplate' ), 10, 2 );
                    return $aActiveTemplates;

                }
                    public function replyToSetTemplate( $sPath, $aArguments ) {
                        return FeedZapper_Registry::$sDirPath . '/include/component/template/output/carousel/template.php';
                    }
            private function ___enableTemplateInFeedsPostTypePages() {
                add_filter( 'feed_zapper_filter_active_templates', array( $this, 'replyToAddActiveTemplatesForFeedPostType' ), 10, 2 );
            }
                public function replyToAddActiveTemplatesForFeedPostType( $aActiveTemplates, FeedZapper_Template_Option $oTemplateOption ) {
                    if ( ! is_singular( array( FeedZapper_Registry::$aPostTypes[ 'feed' ] ) ) ) {
                        return $aActiveTemplates;
                    }
                    $_aTemplate = $oTemplateOption->getTemplateArrayByDirPath(
                        FeedZapper_Registry::$sDirPath . '/include/component/template/output/feed'
                    );
                    $aActiveTemplates[ $_aTemplate[ 'id' ] ] = $_aTemplate;
                    return $aActiveTemplates;
                }

    /**
     * Includes activated templates' `functions.php` files.
     * @since       0.0.1
     */    
    private function ___loadFunctionsOfActiveTemplates() {
        foreach( $this->_oTemplateOption->getActiveTemplates() as $_aTemplate ) {
            $this->includeOnce(
                ABSPATH . ltrim( $_aTemplate[ 'relative_dir_path' ], './' ) . DIRECTORY_SEPARATOR . 'functions.php'
            );
        }    
    }
    
    /**
     * 
     * @since       0.0.1
     */
    private function ___loadStylesOfActiveTemplates() {
        add_action( 
            'wp_enqueue_scripts', 
            array( $this, '_replyToEnqueueActiveTemplateStyles' ) 
        );    
        // @todo Examine whether the ` wp_add_inline_style()` function can be used.
        add_action(
            'wp_enqueue_scripts',
            array( $this, '_replyToPrintActiveTemplateCustomCSSRules' )
        );
    }
        /**
         * Enqueues activated templates' CSS file.
         * 
         * @callback        action      wp_enqueue_scripts
         */
        public function _replyToEnqueueActiveTemplateStyles() {
            
            // This must be called after the option object has been established.
            foreach( $this->_oTemplateOption->getActiveTemplates() as $_aTemplate ) {
                
                $_sCSSPath = ABSPATH . ltrim( $_aTemplate[ 'relative_dir_path' ], './' ) . DIRECTORY_SEPARATOR . 'style.css';
                $_sCSSURL  = $this->getSRCFromPath( $_sCSSPath );
                wp_register_style( "feed-zapper-{$_aTemplate[ 'id' ]}", $_sCSSURL );
                wp_enqueue_style( "feed-zapper-{$_aTemplate[ 'id' ]}" );        
                
            }
            
        }   
        /**
         * Prints a style tag by joining all the custom CSS rules set in the active template options.
         * 
         * @since       0.0.1
         * @return      void
         */
        public function _replyToPrintActiveTemplateCustomCSSRules() {
            
            $_aCSSRUles = array();
            
            // Retrieve 'custom_css' option value from all the active templates.
            foreach( $this->_oTemplateOption->getActiveTemplates() as $_aTemplate ) {   
                $_aCSSRUles[] = $this->getElement(
                    $_aTemplate,
                    'custom_css',
                    ''
                );
            }             
            $_sCSSRules = apply_filters(
                'feed_zapper_filter_template_custom_css',
                trim( implode( PHP_EOL, array_filter( $_aCSSRUles ) ) )
            );
            if ( $_sCSSRules ) {
                echo "<style type='text/css' id='feed-zapper-template-custom-css'>"
                        . $_sCSSRules
                    . "</style>";
            }
            
        }
        
    /**
     * Stores loaded file paths so that PHP errors of including the same file multiple times can be avoided.
     */
    static public $_aLoadedFiles = array();
    
    /**
     * Includes activated templates' settings.php files.
     * @since       0.0.1
     */    
    private function ___loadSettingsOfActiveTemplates() {
        if ( ! is_admin() ) {
            return;
        }
        foreach( $this->_oTemplateOption->getActiveTemplates() as $_aTemplate ) {
            $this->includeOnce( ABSPATH . ltrim( $_aTemplate[ 'relative_dir_path' ], './' ) . DIRECTORY_SEPARATOR . 'settings.php' );
        }        
    }    
        

  
}