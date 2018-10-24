<?php

/**
 * Class FeedZapper_SimplePie_File
 * @version 1.0.1   deprecated the `cache_duration` custom HTTP arguments and added the {...}_filter_simiplepie_cache_duration filter hook.
 */
class FeedZapper_SimplePie_File extends WP_SimplePie_File {

    var $url;
    var $useragent = 'Feed Zapper';
    var $success = true;
    var $headers = array();
    var $body;
    var $status_code;
    var $redirects = 0;
    var $error;
    var $method = SIMPLEPIE_FILE_SOURCE_REMOTE;
    var $timeout = 5;

    /**
     * The request type passed to the HTTP client for caching.
     * @var string
     */
    protected $_sRequestType = 'simplepie';

    /**
     * Just represents the default arguments.
     * The above listed properties take precedence.
     * @var array
     */
    protected $aArgs = array(
        'timeout'     => 5,
        'redirection' => 5,
        'httpversion' => '1.0',
        'user-agent'  => 'Feed Zapper',
        'blocking'    => true,
        'headers'     => array(
            'Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1'
        ),
        'cookies'     => array(),
        'body'        => null,
        'compress'    => false,
        'decompress'  => true,
        'sslverify'   => false,
        'stream'      => false,
        'filename'    => null
    ); 
    
    public function __construct( $sURL, $iTimeout=10, $iRedirects=5, $aHeaders=null, $sUserAgent=null, $bForceFsockOpen=false ) {

        $this->timeout   = $iTimeout;
        $this->redirects = $iRedirects;
        $this->headers   = $aHeaders;
        $this->useragent = $sUserAgent ? $sUserAgent : $this->useragent;
        $this->url       = $sURL;

        // If the scheme is not http or https.
        if ( ! preg_match( '/^http(s)?:\/\//i', $sURL ) ) {
            $this->error = '';
            $this->success = false;            
            return;
        }
            
        // Arguments
        $_aHTTPArguments     = apply_filters(
            FeedZapper_Registry::HOOK_SLUG . '_filter_simiplepie_http_arguments',
            $this->_getDefaultHTTPArguments()
        );
        // Custom arguments
        $_aHTTPArguments     = array(
            'raw' => true,  // returns the raw response data so the header, body, status code can be extracted
        ) + $_aHTTPArguments;

        // Request
        $_oHTTP = new FeedZapper_HTTPClient(
            $sURL,  // urls
            apply_filters(
                FeedZapper_Registry::HOOK_SLUG . '_filter_simiplepie_cache_duration',
                3600
            ),
            $_aHTTPArguments,
            apply_filters(
                FeedZapper_Registry::HOOK_SLUG . '_filter_simiplepie_cache_request_type',
                $this->_sRequestType
            )
        );
        $_aoResponse = $_oHTTP->get();

        if ( is_wp_error( $_aoResponse ) ) {
            $this->error   = 'WP HTTP Error: ' . $_aoResponse->get_error_message();
            $this->success = false;
            return;
        } 
            
        $this->headers     = wp_remote_retrieve_headers( $_aoResponse );
        $this->body        = wp_remote_retrieve_body( $_aoResponse );
        $this->status_code = wp_remote_retrieve_response_code( $_aoResponse );

    }
    
        /**
         * @return      array
         */
        protected function _getDefaultHTTPArguments() {
            
            $aArgs     = array(
                'timeout'       => $this->timeout,
                'redirection'   => $this->redirects,
                'sslverify'     => false, // this is missing in WP_SimplePie_File
            ) + $this->aArgs;
            if ( ! empty( $this->headers ) ) {
                $aArgs[ 'headers' ] = $this->headers;
            }
            if ( SIMPLEPIE_USERAGENT != $this->useragent ) {
                $aArgs[ 'user-agent' ] = $this->useragent;
            }
            return $aArgs;
            
        }    
    
}