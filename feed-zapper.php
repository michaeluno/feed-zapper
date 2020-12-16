<?php
/**
 * Plugin Name:    Feed Zapper
 * Plugin URI:     [PROGRAM_URI]
 * Description:    Helps your feed zapping and create your own zappers' network.
 * Author:         Michael Uno
 * Author URI:     http://en.michaeluno.jp
 * Version:        0.2.3
 */

/**
 * Provides the basic information about the plugin.
 * 
 * @since    0.0.1       
 */
class FeedZapper_Registry_Base {
 
    const VERSION        = '0.2.3';    // <--- DON'T FORGET TO CHANGE THIS AS WELL!!
    const NAME           = 'Feed Zapper';
    const DESCRIPTION    = 'Helps your feed zapping and create your own zappers\' network.';
    const URI            = '[PROGRAM_URI]';
    const AUTHOR         = 'Michael Uno';
    const AUTHOR_URI     = 'http://en.michaeluno.jp';
    const PLUGIN_URI     = '[PROGRAM_URI]';
    const COPYRIGHT      = 'Copyright (c) 2018, Michael Uno';
    const LICENSE        = 'GPL v2 or later';
    const CONTRIBUTORS   = '';
 
}

/**
 * Provides the common data shared among plugin files.
 * 
 * To use the class, first call the setUp() method, which sets up the necessary properties.
 * 
 * @package     FeedZapper
 * @since       0.0.1
*/
final class FeedZapper_Registry extends FeedZapper_Registry_Base {
    
    const TEXT_DOMAIN               = 'feed-zapper';
    const TEXT_DOMAIN_PATH          = '/language';
    
    /**
     * The hook slug used for the prefix of action and filter hook names.
     * 
     * @remark      The ending underscore is not necessary.
     */    
    const HOOK_SLUG                 = 'feed_zapper';    // without trailing underscore
    
    /**
     * The transient prefix. 
     * 
     * @remark      This is also accessed from uninstall.php so do not remove.
     * @remark      Up to 8 characters as transient name allows 45 characters or less ( 40 for site transients ) so that md5 (32 characters) can be added
     */    
    const TRANSIENT_PREFIX          = 'FZ';
    
    /**
     * 
     * @since       0.0.1
     */
    static public $sFilePath = __FILE__;

    /**
     *
     * @since       0.0.1
     */
    static public $sDirPath;

    /**
     * @since   0.0.5
     * @var string
     */
    static public $sTempDirPath = '';

    /**
     * @since    0.0.1
     */
    static public $aOptionKeys = array(
        'setting'           => 'fz_settings',   // administrators settings
        'template'          => 'fz_templates',   // template settings
    );

    /**
     * Used admin pages.
     * @since    0.0.1
     */
    static public $aAdminPages = array(
        // key => 'page slug'
        'setting'           => 'fz_settings',
        'user_setting'      => 'fz_user_settings',

        // not implemented yet
        'template'          => 'fz_templates',

    );

    /**
     * Represents the plugin options structure and their default values.
     * @var         array
     * @since       0.0.1
     */
    static public $aOptions = array(
        'version'   => '',  // stores the plugin version when the options are updated, which represents the option version
        'delete'    => array(
            'delete_on_uninstall' => false,
        ),
        'feed'      => array(
            'page'   => array(
                'value'     => 0,
                'encoded'   => '[{"id":"0","text":""}]',
            ),
            'update_interval' => array(
                'size'      => 1,
                'unit'      => 3600,
            ),
            'retention_period' => array(
                'size'      => 1,
                'unit'      => 604800,
            ),
        ),
        'permission' => array(
            'user_roles' => array(
                'administrator',
                'editor',
                'author',
                'contributor',
                'subscriber',
            ),
        ),

    );

    /**
     * Used post types.
     * @remark  recommended to use a singular form for the post type slug for capability mapping.
     */
    static public $aPostTypes = array(
        // (key) => post type slug
        'zapper'   => 'feed_zapper',       // (visible) stores following zapper users
        'feed'     => 'fz_feed',           // (visible) stores feed urls
        'item'     => 'fz_feed_item',      // (invisible) stores retrieved items
        'log'      => 'fz_log',            // (visible) visible only to administrators

        // @todo not implemented yet
        'touched'   => 'fz_touched',      // () stores feed items interacted by the user, such as plussed one
        'comment'   => 'fz_comment',   // stores users comments
    );

    /**
     * Used post types by meta boxes.
     */
    static public $aMetaBoxPostTypes = array();

    /**
     * Used taxonomies.
     * @remark
     */
    static public $aTaxonomies = array(
        'feed_tag'      => 'fz_feed_tag',     // tags associated with feed items (feed posts)
        'feed_channel'  => 'fz_feed_channel', // tags associated with feeds (feed urls).
        'feed_language' => 'fz_feed_lang',    // languages associated with feeds (feed urls) and feed items (feed posts).
        'feed_owner'    => 'fz_feed_owner',   // associated with feed items to identify the owners (author/user) as multiple authors can be possible for feed items
        'feed_action'   => 'fz_feed_action',  // associated with feed items to label performed actions by the user

        // not used at the moment
        'feed_source'   => 'fz_feed_source',  // associated with feed items to identify the feed source URL
        'zapper_tag'    => 'fz_zapper_tag',
    );

    static public $aPostMetas = array(

        // `fz_feed` post type
        '_fz_feed_url'              => '',  // (string) the feed source URL
        '__fz_cache_duration'       => '',  // (array) the feed cache duration with size and unit. This is for the form field (UI).
        '_fz_cache_duration'        => '',  // (integer) the feed cache duration
        '_fz_feed_expiration_time'  => 0,   // (integer) non GMT-calculated timestamp of expiration time

        // `fz_feed_item` post type
        '_fz_touched'               => false,    // (boolean) whether or not any action is performed on the item. Untouched items should not have this meta key as auto-deletion routine pulls out posts with this key existence.
        '_fz_feed_thumbnail'        => '',       // (string) feed item thumbnail URL
        '_fz_post_permalink'        => '',       // (string) the page URL of the feed post
        '_fz_post_images'           => array(),  // (array) image URLs
        '_fz_post_source'           => '',       // (string)  feed source site URL
    );

    static public $aUserMetas = array(
        // meta key => ...whatever values for notes
        '_fz_mute_items' => array(), // stores mute items
// @deprecated       '_fz_mute_items_permanent' => array(),
// @deprecated       '_fz_mute_items_timed'     => array(),
    );

    /**
     * Used shortcode slugs
     */
    static public $aShortcodes = array();

    /**
     * Stores custom database table names.
     * @remark      The below is the structure
     * array(
     *      'slug (part of database wrapper class file name)' => array(
     *          'version'   => '0.1',
     *          'name'      => 'table_name',    // serves as the table name suffix
     *      ),
     *      ...
     * )
     * @since       0.0.1
     */
    static public $aDatabaseTables = array(
        'fz_request_cache'    => array(
            'name'              => 'fz_request_cache',  // serves as the table name suffix
            'version'           => '1.0.0',
            'across_network'    => true,
            'class_name'        => 'FeedZapper_DatabaseTable_fz_request_cache',
        )
    );

    /**
     * Stores action hook names registered with WP Cron.
     * @var array
     */
    static public $aScheduledActionHooks = array(
        // key (whatever) => value: the name of the action hook
        'feed_renew' => 'feed_zapper_action_feed_renew',
    );

    /**
     * Stores custom keys for the WP Cron intervals.
     * @var array
     */
    static public $aWPCronIntervals = array(
        'feed_renew' => 'FZ_FeedCheck',
    );

    static public $aCookieSlugs = array(
        // (any) => cookie slug (cookie slug where in $_COOKIE[ slug ])
        // @deprecated 'visited'       => 'feed_zapper_visited',
    );
    
    /**
     * Sets up class properties.
     * @return      void
     */
    static function setUp() {
        self::$sDirPath  = dirname( self::$sFilePath );  
    }    
    
    /**
     * @return      string
     */
    public static function getPluginURL( $sPath='', $bAbsolute=false ) {
        $_sRelativePath = $bAbsolute
            ? str_replace('\\', '/', str_replace( self::$sDirPath, '', $sPath ) )
            : $sPath;
        if ( isset( self::$_sPluginURLCache ) ) {
            return self::$_sPluginURLCache . $_sRelativePath;
        }
        self::$_sPluginURLCache = trailingslashit( plugins_url( '', self::$sFilePath ) );
        return self::$_sPluginURLCache . $_sRelativePath;
    }
        /**
         * @since    0.0.1
         */
        static private $_sPluginURLCache;

    /**
     * Requirements.
     * @since    0.0.1
     */    
    static public $aRequirements = array(
        'php' => array(
            'version'   => '5.2.4',
            'error'     => 'The plugin requires the PHP version %1$s or higher.',
        ),
        'wordpress'         => array(
            // @remark requires v4.8 for the `post_date_column_status` filter.
            // @remark requires v4.7 for the `handle_bulk_actions-screenid` filter.
            'version'   => '4.8',
            'error'     => 'The plugin requires the WordPress version %1$s or higher.',
        ),
        // 'mysql'             => array(
            // 'version'   => '5.0.3', // uses VARCHAR(2083) 
            // 'error'     => 'The plugin requires the MySQL version %1$s or higher.',
        // ),
        'functions'     => '', // disabled
        // array(
            // e.g. 'mblang' => 'The plugin requires the mbstring extension.',
        // ),
        // 'classes'       => array(
            // 'DOMDocument' => 'The plugin requires the DOMXML extension.',
        // ),
        'constants'     => '', // disabled
        // array(
            // e.g. 'THEADDONFILE' => 'The plugin requires the ... addon to be installed.',
            // e.g. 'APSPATH' => 'The script cannot be loaded directly.',
        // ),
        'files'         => '', // disabled
        // array(
            // e.g. 'home/my_user_name/my_dir/scripts/my_scripts.php' => 'The required script could not be found.',
        // ),
    );

}
FeedZapper_Registry::setUp();

// Do not load if accessed directly. Not exiting here because other scripts will load this main file such as uninstall.php and inclusion list generator
// and if it exists their scripts will not complete.
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

include( dirname( __FILE__ ).'/include/library/apf/admin-page-framework.php' );
include( dirname(__FILE__) . '/include/FeedZapper_Bootstrap.php');
new FeedZapper_Bootstrap(
    FeedZapper_Registry::$sFilePath,
    FeedZapper_Registry::HOOK_SLUG    // hook prefix    
);