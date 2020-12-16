<?php
if ( ! class_exists( 'FeedZapper_PluginUtility' ) ) {
    return;
}

class FeedZapper_Template_Carousel {
    static public $sDirPath;
    static public function setUp() {
        self::$sDirPath = dirname( __FILE__ );
    }
}
FeedZapper_Template_Carousel::setUp();


include_once( dirname( __FILE__ ) . '/class/FeedZapper_Template_Carousel_Utility.php' );
include_once( dirname( __FILE__ ) . '/class/FeedZapper_Template_Carousel_ResourceLoader.php' );
include_once( dirname( __FILE__ ) . '/class/FeedZapper_Template_Carousel_Actions.php' );

new FeedZapper_Template_Carousel_ResourceLoader;
new FeedZapper_Template_Carousel_Actions;
