<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 */ 
class FeedZapper_SimplePie extends FeedZapper_SimplePie_Base {

    /**
     * The cache class name.
     * @var string
     */
    protected $_sCacheClass = 'FeedZapper_SimplePie_Cache';

    /**
     * The file class name.
     * @var string
     */
    protected $_sFileClass = 'FeedZapper_SimplePie_File';

    /**
     * @return bool|WP_Error
     */
    public function init() {    
        
        /**
         * We must manually overwrite $feed->sanitize because SimplePie's
         * constructor sets it before we have a chance to set the sanitization class
         */
        $this->set_sanitize_class( 'WP_SimplePie_Sanitize_KSES' );
        $this->sanitize = new WP_SimplePie_Sanitize_KSES();    
        
        /**
         * Store caches in transients.
         */
        $this->set_cache_class( $this->_sCacheClass ); // 'WP_Feed_Cache'
        
        /**
         * Feed Zapper uses an own HTTP method with a caching mechanism.
         */
        $this->set_file_class( $this->_sFileClass ); //  'WP_SimplePie_File'

        add_filter( FeedZapper_Registry::HOOK_SLUG . '_filter_simiplepie_cache_duration', array( $this, 'replyToSetCacheDuration' ) );

        $_bResult = parent::init();
        
        $this->set_output_encoding( get_option( 'blog_charset' ) );        
        $this->handle_content_type();
        
//        if ( $this->error() ) {
//            return new WP_Error( 'simplepie-error', $this->error() );
//        }
        return $_bResult;
        
    }

    /**
     * @callback    filter      feed_zapper_filter_simiplepie_http_arguments
     * @param $aArguments
     */
    public function replyToSetCacheDuration( $iCacheDuration ) {
        return $this->cache_duration;
    }

}