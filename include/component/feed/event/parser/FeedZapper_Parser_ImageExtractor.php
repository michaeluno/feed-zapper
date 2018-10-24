<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Extracts images.
 *
 * Usage
 * ```
 * $_aImages = apply_filters( 'feed_zapper_filter_images_extracted_from_html', array(), $sHTMLContent );
 * ```
 *
 * @since    0.0.1
 */
class FeedZapper_Parser_ImageExtractor extends FeedZapper_PluginUtility {

    /**
     * @var FeedZapper_DOM
     */
    private $___oDOM;

    public function __construct() {

        // The hook below is registered only once.
        if ( $this->hasBeenCalled( __METHOD__ ) ) {
            return;
        }
        $this->___oDOM = new FeedZapper_DOM;
        add_filter( 'feed_zapper_filter_images_extracted_from_html', array( $this, 'replyToGetImagesExtracted' ), 10, 3 );

    }

    /**
     * @return      array       Holding found images.
     */
    public function replyToGetImagesExtracted( array $aImages, $sHTMLContent, SimplePie_Item $oItem ) {
//FeedZapper_Debug::$iLegibleStringCharacterLimit = 2000;
//FeedZapper_Debug::log( $sHTMLContent );
        $_oDoc    = $this->___oDOM->loadDOMFromHTMLElement( $sHTMLContent );
        $_oIMGs   = $_oDoc->getElementsByTagName( 'img' );
        foreach( $_oIMGs as $_oIMG ) {
            $_sURL      = $_oIMG->getAttribute( 'src' );
            if ( ! $_sURL ) {
                continue;
            }
            if ( false === filter_var( $_sURL, FILTER_VALIDATE_URL ) ) {
                $_sURL = $this->___getSupportedImageURL( $_sURL, $oItem );
                if ( ! $_sURL ) {
                    continue;
                }
            }
            $aImages[ $_sURL ] = $_sURL;
        }
        return $aImages;

    }
        /**
         * Some feed sources have own ways to insert images.
         * For example Google News feeds give encrypted strings into the img src attribute values.
         * Check such methods and if detected, format the URL.
         * @return  string  Empty if it is just invalid. Otherwise, a formatted URL.
         */
        private function ___getSupportedImageURL( $sInvalidURL, SimplePie_Item $oItem ) {

            // For base 64 encoded images
            if ( 'data:image' === substr( $sInvalidURL, 0, 10 ) ) {
                return $sInvalidURL;
            }
            // @todo this should be done by an external filter
            $_sFeedURL = $oItem->get_feed()->subscribe_url();
            if ( 'news.google.com' === parse_url( $_sFeedURL, PHP_URL_HOST ) ) {
                return '//t2.gstatic.com/images?q=tbn:' . $sInvalidURL;
            }
            return '';
        }

}