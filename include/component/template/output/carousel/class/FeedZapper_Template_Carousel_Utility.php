<?php

class FeedZapper_Template_Carousel_Utility extends FeedZapper_PluginUtility {

    /**
     * Ensures only one time to create a nonce for this particular template.
     * @return bool|string
     */
    static public function getTemplateNonce() {
        static $_sNonce;
        if ( $_sNonce ) {
            return $_sNonce;
        }
        $_sNonce = wp_create_nonce('feed_zapper_carousel_template_nonce' );
        return $_sNonce;
    }

    /**
    * @param $iUserID
    * @param $iCount
    *
    * @return mixed
    * @remark the count represents the total associated posts (not sorted by a user)
    * so the number is not accurate and does not mean the total number of current user's subscribing posts
    */
    static public function getUserChannels( $iUserID, $iCount=PHP_INT_MAX ) {

        $_sTransientKey = 'fz_tags_' . $iUserID;
        $_sOptionName   = "_transient_{$_sTransientKey}";

        $_aTags         = self::getAsArray( get_option( $_sOptionName, array() ) );
        if ( ! empty( $_aTags ) ) {
            if ( self::isTransientAsOptionExpired( $_sTransientKey ) ) {
                wp_schedule_single_event( time() - 1, 'feed_zapper_action_save_user_channels', array( $iUserID ) );
            }
            return array_slice( $_aTags, 0, $iCount );
        }
        $_aTags         = self::getUserChannelsFromDatabase( $iUserID );
        self::setTransientAsOption( $_sTransientKey, $_aTags, 86400 );
        return array_slice( $_aTags, 0, $iCount );

    }

    /**
     * @param  integer $iUserID
     * @return array
     */
    static public function getUserChannelsFromDatabase( $iUserID ) {
        $iCount = 1000;
        $_sBlockTerms          = self::___getBlockTermsForSQL_IN();
        $_sOwnerTaxonomySlug   = FeedZapper_Registry::$aTaxonomies[ 'feed_owner' ];
        $_sFeedTagTaxonomySlug = FeedZapper_Registry::$aTaxonomies[ 'feed_tag' ];
        $_sPostTypeSlug        = FeedZapper_Registry::$aPostTypes[ 'item' ];
        return $GLOBALS[ 'wpdb' ]->get_results(
            $GLOBALS[ 'wpdb' ]->prepare(
                "
                SELECT terms2.term_id, terms2.name, tax2.count
                FROM {$GLOBALS[ 'wpdb' ]->posts} as posts
                LEFT JOIN {$GLOBALS[ 'wpdb' ]->term_relationships} as relationships  ON (posts.ID = relationships.object_id) 
                LEFT JOIN {$GLOBALS[ 'wpdb' ]->term_taxonomy} 	   as tax 			 ON relationships.term_taxonomy_id = tax.term_taxonomy_id
                LEFT JOIN {$GLOBALS[ 'wpdb' ]->terms} 			   as terms 		 ON tax.term_id = terms.term_id
                LEFT JOIN {$GLOBALS[ 'wpdb' ]->term_relationships} as relationships2 ON posts.ID = relationships2.object_ID
                LEFT JOIN {$GLOBALS[ 'wpdb' ]->term_taxonomy} 	   as tax2 			 ON relationships2.term_taxonomy_id = tax2.term_taxonomy_id
                LEFT JOIN {$GLOBALS[ 'wpdb' ]->terms} 			   as terms2 		 ON tax2.term_id = terms2.term_id                
                WHERE 1=1 
                AND ( 
                    tax.taxonomy = '{$_sOwnerTaxonomySlug}'
                    AND terms.name = '{$iUserID}'
                ) 
                AND ( 
                    tax2.taxonomy = '{$_sFeedTagTaxonomySlug}'
                    AND terms2.name NOT IN ({$_sBlockTerms})
                ) 
                AND (
                    posts.post_status = 'publish' 
                    AND posts.post_type = '{$_sPostTypeSlug}'
                )  
                GROUP BY terms2.term_id
                ORDER BY tax2.count DESC
                LIMIT %d; 
                ",
                array(
                    $iCount
                )
            ),
            'ARRAY_A'
        );
    }
        /**
         * Using wpdb->prepare() here to sanitize strings passed as terms.
         * If it applied to a joined string listing terms, single colons are escaped unnecessarily by the method. e.g. 'News' -> '\'News\''
         * @return string SQL compatible list of items that
         */
        static private function ___getBlockTermsForSQL_IN() {
            $_aBlockTerms = array( 'Google News RSS feed URL deprecation' );
            $_aBlockTerms = apply_filters( 'feed_zapper_filter_block_terms_by_user_' . get_current_user_id(), $_aBlockTerms );
            // Sanitize terms
            $_aTerms = array();
            foreach( $_aBlockTerms as $_sBlockTerm ) {
                $_aTerms[] = $GLOBALS[ 'wpdb' ]->prepare( '%s', array( $_sBlockTerm ) );
            }
            return implode( ",", $_aTerms );
        }

    /**
     * @param array $aTerms
     * @return string
     */
    static public function getWordCloud( array $aTerms ) {

        $_sOutput   = '';
        $_iSetMax   = 5;     // Maximum scale
        $_dSetMin   = 0.6;   // Minimum scale
        $_iDefault  = 1;
        $_iMinInAll = max( min( $aTerms ), 1 ); // Frequency lower-bound
        $_iMaxInAll = max( $aTerms ); // Frequency upper-bound


        $_iIndex    = 0;
        foreach( $aTerms as $_sTerm => $_iCount ) {
            $_dScale   = self::___getWordScale( $_iCount, $_iMinInAll, $_iMaxInAll, $_dSetMin, $_iSetMax, $_iDefault );
            $_sStyle   = $_dScale !== $_iDefault ? "style='font-size: {$_dScale}em;'" : '';
            $_sOutput .= "<a href='#' {$_sStyle} class='feed-channel' data-count={$_iCount} data-slide-index={$_iIndex}>"
                    . $_sTerm
                . "</a>";
            $_iIndex++;
        }
        return "<div class='feed-zapper-all-feed-channels'>" // wp-tag-cloud
                . $_sOutput
            . "</div>";

    }
        static private function ___getWordScale( $_iCount, $_iMinInAll, $_iMaxInAll, $_dSetMin, $_iSetMax, $iDefault ) {
            // For the system items, scale it to the default.
            if ( 0 === $_iCount ) {
                return $iDefault;
            }
            return $_iCount > $_iMinInAll
                ? max( round( ( $_iSetMax * ( $_iCount - $_iMinInAll ) ) / ( $_iMaxInAll - $_iMinInAll ), 3 ), $_dSetMin )
                : $_dSetMin;
        }

}