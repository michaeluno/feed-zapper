<?php
/**
 * Cleans up the plugin options.
 *    
 * @package      FeedZapper
 * @copyright    Copyright (c) 2018, <Michael Uno>
 * @author       Michael Uno
 * @authorurl    http://en.michaeluno.jp
 * @since        0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/* 
 * Plugin specific constant. 
 * We are going to load the main file to get the registry class. And in the main file, 
 * if this constant is set, it will return after declaring the registry class.
 **/
if ( ! defined( 'DOING_PLUGIN_UNINSTALL' ) ) {
    define( 'DOING_PLUGIN_UNINSTALL', true  );
}

/**
 * Set the main plugin file name here.
 */
$_sMainPluginFileName  = 'feed-zapper.php';
if ( file_exists( dirname( __FILE__ ). '/' . $_sMainPluginFileName ) ) {
   include( $_sMainPluginFileName );
}

if ( ! class_exists( 'FeedZapper_Registry' ) ) {
    return;
}

// 1. Delete transients
$_aPrefixes = array(
    FeedZapper_Registry::TRANSIENT_PREFIX, // the plugin transients
    'apf_',      // the admin page framework transients
);
foreach( $_aPrefixes as $_sPrefix ) {
    if ( ! $_sPrefix ) { 
        continue; 
    }
    $GLOBALS[ 'wpdb' ]->query( "DELETE FROM `" . $GLOBALS[ 'table_prefix' ] . "options` WHERE `option_name` LIKE ( '_transient_%{$_sPrefix}%' )" );
    $GLOBALS[ 'wpdb' ]->query( "DELETE FROM `" . $GLOBALS[ 'table_prefix' ] . "options` WHERE `option_name` LIKE ( '_transient_timeout_%{$_sPrefix}%' )" );
}

// 2. Delete plugin data
$_oOption  = FeedZapper_Option::getInstance();
if ( ! $_oOption->get( array( 'delete', 'delete_on_uninstall' ) ) ) {
    return true;
}

// Options stored in the `options` table.
array_walk_recursive( 
    FeedZapper_Registry::$aOptionKeys, // subject array
    'delete_option'   // function name
);

// Delete custom tables
foreach( FeedZapper_Registry::$aDatabaseTables as $_aTable ) {
    if ( ! class_exists( $_aTable[ 'class_name' ] ) ) {
        continue;
    }
    $_oTable  = new $_aTable[ 'class_name' ];
    if ( ! method_exists( $_oTable, 'uninstall' ) ) {
        continue;
    }
    $_oTable->uninstall();
}

// Remove user meta keys used by the plugin
foreach( FeedZapper_Registry::$aUserMetas as $_sMetaKey => $_v ) {
    delete_metadata(
        'user',    // the user meta type
        0,  // does not matter here as deleting them all
        $_sMetaKey,
        '', // does not matter as deleting
        true // whether to delete all
    );
}

// Delete posts of the used custom post types
foreach( FeedZapper_Registry::$aPostTypes as $_sKey => $_sPostTypeSlug ) {
    _deleteFeedZapperPosts( $_sPostTypeSlug );
}

/**
 * @since 3.6.6
 */
function _deleteFeedZapperPosts( $sPostTypeSlug ) {
    $_oResults   = new WP_Query(
        array(
            'post_type'      => $sPostTypeSlug,
            'posts_per_page' => -1,     // `-1` for all
            'fields'         => 'ids',  // return only post IDs by default.
        )
    );
    foreach( $_oResults->posts as $_iPostID ) {
        wp_delete_post( $_iPostID, true );
    }
}


// Delete terms of custom taxonomies
/**
 * @see https://wordpress.stackexchange.com/a/119353
 */
function deleteFeedZapperCustomTerms( $taxonomy ){
    global $wpdb;

    $query = 'SELECT t.name, t.term_id
            FROM ' . $wpdb->terms . ' AS t
            INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
            ON t.term_id = tt.term_id
            WHERE tt.taxonomy = "' . $taxonomy . '"';

    $terms = $wpdb->get_results($query);

    foreach ($terms as $term) {
        wp_delete_term( $term->term_id, $taxonomy );
    }
}
foreach( FeedZapper_Registry::$aTaxonomies as $_sTaxonomySlug ) {
    deleteFeedZapperCustomTerms( $_sTaxonomySlug );
}

/**
 * @see http://scottnelle.com/648/clean-bloated-wp_term_relationships-table/
 */
function cleanFeedZapperOrphanedTermRelationships() {
    global $wpdb;

    $query = 'DELETE ' . $wpdb->term_relationships . ' FROM ' . $wpdb->term_relationships . '
        LEFT JOIN ' . $wpdb->posts . ' ON ' . $wpdb->term_relationships . '.object_id = ' . $wpdb->posts . '.ID
        WHERE ' . $wpdb->posts . '.ID is NULL;';
    $wpdb->get_results($query);

    $query = "OPTIMIZE TABLE `{$wpdb->term_relationships}`;";
    $wpdb->get_results($query);

}
cleanFeedZapperOrphanedTermRelationships();
