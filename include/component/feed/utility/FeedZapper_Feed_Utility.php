<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Provides feed component specific methods.
 *
 * @package      FeedZapper
 * @since    0.0.1
 */
class FeedZapper_Feed_Utility extends FeedZapper_PluginUtility {

    /**
     * Defines the base capabilities used by plugin custom taxonomies.
     *
     * This is for giving the same capabilities to all the created plugin custom taxonomies
     * so that plugin's dynamically assigned user role capabilities will take effect on those taxonomies as well.
     * This is mainly for creating and assigning new taxonomy terms when creating posts from feed in the background.
     *
     * @return  array
     */
    static public function getTaxonomyCapabilities() {
        return array(
            'manage_terms' => 'manage_fz_feed_tags',
            'edit_terms'   => 'manage_fz_feed_tags',
            'delete_terms' => 'manage_fz_feed_tags',
            'assign_terms' => 'edit_fz_feeds',
        );
    }

    /**
     * @return array
     */
    static public function getSpecialCapabilities() {
        return array(

            'edit_post'  => true,   // Allow bulk-actions with the post listing table.
            'edit_posts' => true,   // Allow accessing post listing page. Without this, WordPress adds an item to the global `$_wp_menu_nopriv` array and the user with low privileges becomes unable to access.

            // Without this, a new post cannot be created for low privilege users.
            // When an object ID is passed in the second parameter of `current_user_can()`, all the mapped meta capabilities are checked its existence
            // and if even mere single mapped capabilities misses, the method returns `false` which results in insufficient capabilities.
            'edit_published_posts' => true,

            // to allow trashing items
            'delete_published_posts' => true,

        );
    }

    /**
     * @return array
     */
    static public function getCustomCapabilitiesForFeedCustomPostType() {
        return array(
            'create_fz_feeds'            => true,
            'edit_fz_feed'               => true,
            'edit_fz_feeds'              => true,
            'edit_published_fz_feeds'    => true,
            'edit_other_fz_feeds'        => true,
            'publish_fz_feeds'           => true,
            'read_fz_feed'               => true,
            'read_private_fz_feeds'      => true,
            'delete_fz_feed'             => true,
            'delete_fz_feeds'            => true,
            'delete_published_fz_feeds'  => true,
            // taxonomies
            'manage_fz_feed_tags'        => true,
        );
    }


}