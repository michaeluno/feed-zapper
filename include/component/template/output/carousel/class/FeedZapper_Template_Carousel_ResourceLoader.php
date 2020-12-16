<?php

class FeedZapper_Template_Carousel_ResourceLoader extends FeedZapper_PluginUtility {

    public $sTemplateDirPath;
    
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'replyToEnqueueStyles' ) );
        add_action( 'wp_enqueue_scripts', array($this, 'replyToEnqueueScripts') );
        $this->sTemplateDirPath = FeedZapper_Template_Carousel::$sDirPath;
    }

    public function replyToEnqueueStyles() {
        $this->___loadSlickStyles();
        $this->___loadContextMenuStyles();
        $this->___loadTemplateStyles();
    }
        private function ___loadContextMenuStyles() {
            $_sMin  = $this->isDebugMode() ? '' : '.min';
            $_sPath = $this->sTemplateDirPath . "/context-menu/jquery.contextMenu{$_sMin}.css";
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_register_style( "jquery-context-menu", $_sURL );
            wp_enqueue_style( "jquery-context-menu" );
        }
        private function ___loadTemplateStyles() {
            wp_enqueue_style( 'dashicons' );    // Dashicons in front-end
        }
        private function ___loadSlickStyles() {
            $_sPath = $this->sTemplateDirPath . "/slick/slick.css"; // @todo if non debug load .min
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_register_style( "slick-style", $_sURL );
            wp_enqueue_style( "slick-style" );
            $_sPath = $this->sTemplateDirPath . "/slick/slick-theme.css"; // @todo if non debug load .min
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_register_style( "slick-theme", $_sURL );
            wp_enqueue_style( "slick-theme" );
        }

    public function replyToEnqueueScripts() {
        $this->___loadSlickScripts();
        $this->___loadLazyLoadScripts();
        $this->___loadContextMenuScripts();
        $this->___loadNotifyScripts();
        $this->___loadTemplateScripts();
    }
        private function ___loadNotifyScripts() {
            $_sMin  = $this->isDebugMode() ? '' : '.min';
            $_sPath = $this->sTemplateDirPath . "/notify/notify{$_sMin}.js";
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_enqueue_script(
                'notify',
                $_sURL,     // src
                array( 'jquery' ),   // dependencies
                '0.4.2',    // version number
                true    // insert in footer
            );
        }
        private function ___loadContextMenuScripts() {
            $_sMin  = $this->isDebugMode() ? '' : '.min';
            wp_enqueue_script( 'jquery-ui-position' );
            $_sPath = $this->sTemplateDirPath . "/context-menu/jquery.contextMenu{$_sMin}.js";
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_enqueue_script(
                'jquery-context-menu',
                $_sURL,     // src
                array( 'jquery', 'jquery-ui-position' ),   // dependencies
                '2.7.0',    // version number
                true    // insert in footer
            );
        }
        private function ___loadLazyLoadScripts() {
            // @todo move the script to the common area
            $_sPath = FeedZapper_Registry::$sDirPath . "/include/component/feed/asset/js/jquery-lazy/jquery.lazy.min.js";
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_enqueue_script(
                'jquery-lazy',
                $_sURL,     // src
                array( 'jquery' ),   // dependencies
                '',    // version number
                true    // insert in footer
            );
        }

        private function ___loadSlickScripts() {
            $_sMin  = $this->isDebugMode() ? '' : '.min';
            $_sPath = $this->sTemplateDirPath . "/slick/slick{$_sMin}.js";
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_enqueue_script(
                'slick',
                $_sURL,     // src
                array( 'jquery' ),   // dependencies
                '1.8.1',    // version number
                true    // insert in footer
            );
        }
        private function ___loadTemplateScripts() {
            $_sMin  = $this->isDebugMode() ? '' : ''; // @todo if non debug load .min
            $_sPath = $this->sTemplateDirPath . "/script{$_sMin}.js";
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_enqueue_script(
                'feed-zapper-template-carousel',
                $_sURL,
                array(
                    'jquery',
                    // 'wp-util',  // Underscore
                    'slick'
                ), // dependencies
                null,
                true
            );
            wp_localize_script(
                'feed-zapper-template-carousel',
                'fzCarousel',
                array(
                    'userID'            => get_current_user_id(),
                    'spinnerURL'        => site_url( 'wp-includes/js/tinymce/skins/lightgray/img/loader.gif' ),
                    'AJAXURL'           => admin_url( 'admin-ajax.php' ),
                    'nonce'             => wp_create_nonce('feed_zapper_carousel_template_nonce' ),
                    'debugMode'         => $this->isDebugMode(),
                    'taxonomySlug'      => FeedZapper_Registry::$aTaxonomies[ 'feed_tag' ],
                    'taxonomySlugs'     => FeedZapper_Registry::$aTaxonomies,
                    // @deprecated 'cookieSlugs'       => FeedZapper_Registry::$aCookieSlugs,
                    'labels'            => array(
                        // bottom buttons
                        'loadMore'     => __( 'Load More', 'feed-zapper' ),
                        'noMore'       => __( 'No More', 'feed-zapper' ),
                        'checkedAbove' => __( 'Checked Above', 'feed-zapper' ),
                        // mute menu
                        'one_day'    => __( 'for one day', 'feed-zapper' ),
                        'one_week'   => __( 'for one week', 'feed-zapper' ),
                        'one_month'  => __( 'for one month', 'feed-zapper' ),
                        'forever'    => __( 'forever', 'feed-zapper' ),
                        'in_title'   => __( 'in title', 'feed-zapper' ),
                        'in_content' => __( 'in content', 'feed-zapper' ),
                        'mute'       => __( 'Mute', 'feed-zapper' ),
                        'something_went_wrong' => __( 'Something went wrong while trying to mute an item.', 'feed-zapper' ),
                    ),
                )
            );
        }

}