<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 *
 * ## Deletion of Untouched Old Feed Items
 * 1. Get the retention period option value.
 * 2. Find feed items older than it and does not have the `_fz_touched` meta value.
 * 3. Delete them.
 * 4. Remove unassociated terms and term relationships. -> wp_delete_post() is supposed to take care of it but relationships seem to get have orphaned items.
 *
 * @package     FeedZapper
 * @since       0.0.1

 */
class FeedZapper_Action_DeleteOldUntouchedFeedItems extends FeedZapper_Event_Action_Base {

    protected $_sActionHookName     = 'feed_zapper_action_feed_renew'; // FeedZapper_Registry::$aScheduledActionHooks[ 'feed_renew ]
    protected $_iCallbackParameters = 0;

    /**
     * @callback        action      feed_zapper_action_feed_renew
     */
    public function doAction() {

        $_oOption   = FeedZapper_Option::getInstance();
        $_iSize     = ( integer ) $_oOption->get( array( 'feed', 'retention_period', 'size' ), 1 );
        $_iUnit     = ( integer ) $_oOption->get( array( 'feed', 'retention_period', 'unit' ), 604800 );
        $_iPeriod   = $_iSize * $_iUnit; // in seconds
        $_aPostIDs  = $this->___getOldPostsToDelete( $_iPeriod );

        if ( ! empty( $_aPostIDs ) ) {
            $this->addLog(
                "<p>" . print_r( $_aPostIDs, true ) . "</p>",
                'Deleting Feed Items'
            );
        }

        foreach( $_aPostIDs as $_iPostID ) {
            wp_delete_post( $_iPostID, true );
        }

        $this->___deleteTermsWithZeroPost( FeedZapper_Registry::$aTaxonomies[ 'feed_tag' ] );
// @deprecated       $this->___deleteOrphanedTermRelationships();

        // @todo consider an option to delete feed items by duplicate titles.
        // @see https://stackoverflow.com/a/5843604
/*        delete
        from employee using employee,
            employee e1
        where employee.id > e1.id
            and employee.first_name = e1.first_name  */

    }
        /**
         * @see http://scottnele.com/648/clean-bloated-wp_term_relationships-table/
         * @deprecated
         */
        private function ___deleteOrphanedTermRelationships() {
            /*
                DELETE wp_term_relationships FROM wp_term_relationships
                LEFT JOIN wp_posts ON wp_term_relationships.object_id = wp_posts.ID
                WHERE wp_posts.ID is NULL;
             */
            $_sPosts             = $GLOBALS[ 'wpdb' ]->posts;
            $_sTermRelationships = $GLOBALS[ 'wpdb' ]->term_relationships;

            $_iRowsToDelete = $GLOBALS[ 'wpdb' ]->get_var(
                "SELECT COUNT(*) FROM {$_sTermRelationships} "
                . "LEFT JOIN {$_sPosts} "
                . "ON {$_sTermRelationships}.object_id = {$_sPosts}.ID "
                . "WHERE {$_sPosts}.ID is NULL;"
            );
FeedZapper_Debug::log( 'orphaned term relationships: ' . $_iRowsToDelete );
            if ( ! $_iRowsToDelete ) {
                return;
            }
            $this->addLog( 'Number of orphaned term relationships: ' . $_iRowsToDelete, 'Orphaned Terms' );
            $GLOBALS[ 'wpdb' ]->query(
                "DELETE {$_sTermRelationships} "
                . "FROM {$_sTermRelationships} "
                . "LEFT JOIN {$_sPosts} "
                . "ON {$_sTermRelationships}.object_id = {$_sPosts}.ID "
                . "WHERE {$_sPosts}.ID is NULL;"
            );
            $GLOBALS[ 'wpdb' ]->query( "OPTIMIZE {$_sTermRelationships};" );
        }
        /**
         * @see http://sabrinazeidan.com/bulk-delete-tags-taxonomy-terms/
         */
        private function ___deleteTermsWithZeroPost( $sTaxonomySlug ) {

            $_aTerms = get_terms(
                array(
                    'taxonomy'      => $sTaxonomySlug,
                    'hide_empty'    => false,
                )
            );
            foreach ( $_aTerms as $_oTerm ) {
                if ( 1 > $_oTerm->count ) {
                    wp_delete_term( $_oTerm->term_id, $sTaxonomySlug );
                }
            }

        }
    /**
         * @param   integer $iExpiry
         * @return  array   an array holding post IDs to delete.
         */
        private function ___getOldPostsToDelete( $iExpiry ) {

            $_aArguments = array(
                'post_type'         => array(
                    FeedZapper_Registry::$aPostTypes[ 'item' ],
                ),
                'posts_per_page'    => -1,    // -1 for all
                'orderby'           => 'date ID',        // another option: 'ID',
                'order'             => 'ASC', // DESC: the newest comes first, 'ASC' : the oldest comes first
                'fields'            => 'ids',    // return only post IDs by default.
                'date_query'        => array(
                    'before'    => "-{$iExpiry} seconds", // strtotime() compatible string
                ),
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_fz_touched',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key'     => '_fz_touched',
                        'value'   => false,
                        'type'    => 'BOOLEAN',
                    ),
                ),
                'tax_query' => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => FeedZapper_Registry::$aTaxonomies[ 'feed_action' ],
                        'field'    => 'name',
                        'terms'    => 'read_later_by_', // this query clause will be modified with a filter added below
                        'operator' => 'NOT IN',
                    )
                ),
            );
            add_filter( 'terms_clauses', array( $this, 'replyToEditTermClauses' ), 10, 3 );
            $_oResults = new WP_Query( $_aArguments );

//FeedZapper_Debug::log( 'query results' );
//FeedZapper_Debug::log( $_oResults->posts );
            return $_oResults->posts;

        }
    /**
     * @callback    filter      terms_clauses
     * @see     https://wordpress.stackexchange.com/a/123306
     * @param array $aTermClauses     Terms query SQL clauses.
     * @param array $aTaxonomies      An array of taxonomies.
     * @param array $aArguments       An array of terms query arguments.
     */
    public function replyToEditTermClauses( $aTermClauses, $aTaxonomies, $aArguments ) {
        remove_filter('terms_clauses',array( $this, 'replyToEditTermClauses' ), 10 );
        if ( FeedZapper_Registry::$aTaxonomies[ 'feed_action' ] !== $this->getElement( $aTaxonomies, 0 ) ) {
            return $aTermClauses;
        }
        /*
        $aTermClauses = array(
            [fields] => (string, length: 9) t.*, tt.*
            [join] => (string, length: 62)  INNER JOIN wp49_term_taxonomy AS tt ON t.term_id = tt.term_id
            [where] => (string, length: 66) tt.taxonomy IN ('fz_feed_action') AND t.name IN ('read_later_by_')
            [distinct] => (string, length: 0)
            [orderby] => (string, length: 0)
            [order] => (string, length: 3) ASC
            [limits] => (string, length: 0)
        )
        */
        $_sSearch = "t.name IN ('read_later_by_')";
        $aTermClauses[ 'where' ] = str_replace( $_sSearch, "t.name LIKE 'read_later_by_'", $aTermClauses[ 'where' ] );
//FeedZapper_Debug::log( 'term clauses' );
//FeedZapper_Debug::log( $aTermClauses );
        return $aTermClauses;
    }

}