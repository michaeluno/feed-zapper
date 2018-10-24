<?php
if ( ! class_exists( 'FeedZapper_PluginUtility' ) ) {
    return;
}

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
    static public function getUserTags( $iUserID, $iCount=PHP_INT_MAX ) {

        $_sBlockTerms          = self::___getBlockTermsForSQL_IN();
        $_sOwnerTaxonomySlug   = FeedZapper_Registry::$aTaxonomies[ 'feed_owner' ];
        $_sFeedTagTaxonomySlug = FeedZapper_Registry::$aTaxonomies[ 'feed_tag' ];
        $_sPostTypeSlug        = FeedZapper_Registry::$aPostTypes[ 'item' ];
        $_aTags = $GLOBALS[ 'wpdb' ]->get_results(
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
        return $_aTags;
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
     * @param string $args
     * @param array $aTopTags
     *
     * @return array|string
     * @deprecated not used at the moment
     */
    private function ___getTagCloud( $args = '', $aTopTags=array() ) {
        $defaults = array(
            'smallest'      => 8,       'largest'       => 22,
            'unit'          => 'pt',    'number'        => 45,
            'format'        => 'flat',  'separator'     => "\n",
            'orderby'       => 'name',  'order'         => 'ASC',
            'exclude'       => '',      'include'       => '',
            'link'          => 'view',  'taxonomy'      => 'post_tag',
            'post_type'     => '',      'echo'          => true,
            'show_count'    => 0,
        );
        $args = wp_parse_args( $args, $defaults );
        $tags = get_terms(
            $args[ 'taxonomy' ],
            array_merge(
                array(
                    'term_taxonomy_id' => wp_list_pluck( $aTopTags, 'term_id' ),
                ),
                $args,
                array(
                    'orderby'   => 'count',
                    'order'     => 'DESC',
                )
            )
        ); // Always query top tags
        if ( empty( $tags ) || is_wp_error( $tags ) ) {
            return '';
        }
        foreach ( $tags as $_iIndex => $tag ) {
            $_sLink = 'edit' === $args['link']
                ? get_edit_term_link( $tag->term_id, $tag->taxonomy, $args['post_type'] )
                : get_term_link( intval($tag->term_id), $tag->taxonomy );
            if ( is_wp_error( $_sLink ) ) {
                continue;
            }
            $tags[ $_iIndex ]->link = $_sLink;
            $tags[ $_iIndex ]->id   = $tag->term_id;
        }
        return wp_generate_tag_cloud( $tags, $args ); // Here's where those top tags get sorted according to $args

    }

}
class FeedZapper_Template_Carousel_ResourceLoader extends FeedZapper_PluginUtility {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'replyToEnqueueStyles' ) );
        add_action( 'wp_enqueue_scripts', array($this, 'replyToEnqueueScripts') );
    }

    public function replyToEnqueueStyles() {
        $this->___loadSlickStyles();
        $this->___loadContextMenuStyles();
        $this->___loadTemplateStyles();
    }
        private function ___loadContextMenuStyles() {
            $_sMin  = $this->isDebugMode() ? '' : '.min';
            $_sPath = dirname( __FILE__ ) . "/context-menu/jquery.contextMenu{$_sMin}.css";
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_register_style( "jquery-context-menu", $_sURL );
            wp_enqueue_style( "jquery-context-menu" );
        }
        private function ___loadTemplateStyles() {
            wp_enqueue_style( 'dashicons' );    // Dashicons in front-end
        }
        private function ___loadSlickStyles() {
            $_sPath = dirname( __FILE__ ) . "/slick/slick.css"; // @todo if non debug load .min
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_register_style( "slick-style", $_sURL );
            wp_enqueue_style( "slick-style" );
            $_sPath = dirname( __FILE__ ) . "/slick/slick-theme.css"; // @todo if non debug load .min
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
            $_sPath = dirname( __FILE__ ) . "/notify/notify{$_sMin}.js";
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
            $_sPath = dirname( __FILE__ ) . "/context-menu/jquery.contextMenu{$_sMin}.js";
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
            $_sPath = dirname( __FILE__ ) . "/slick/slick{$_sMin}.js";
            $_sURL  = $this->getSRCFromPath( $_sPath );
            wp_enqueue_script(
                'slick',
                $_sURL,     // src
                array( 'jquery' ),   // dependencies
                '1.8.0',    // version number
                true    // insert in footer
            );
        }
        private function ___loadTemplateScripts() {
            $_sMin  = $this->isDebugMode() ? '' : ''; // @todo if non debug load .min
            $_sPath = dirname( __FILE__ ) . "/script{$_sMin}.js";
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
                    'debugMode'         => $this->isDebugMode(),
                    'taxonomySlug'      => FeedZapper_Registry::$aTaxonomies[ 'feed_tag' ],
                    'taxonomySlugs'     => FeedZapper_Registry::$aTaxonomies,
                    // @deprecated 'cookieSlugs'       => FeedZapper_Registry::$aCookieSlugs,
                    'labels'            => array(
                        // load buttons
                        'loadMore' => __( 'Load More', 'feed-zapper' ),
                        'noMore'   => __( 'No More', 'feed-zapper' ),
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

new FeedZapper_Template_Carousel_ResourceLoader;