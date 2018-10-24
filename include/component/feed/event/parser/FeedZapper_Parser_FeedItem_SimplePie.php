<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Formats feed SimplePie items.
 *
 * @since    0.0.1
 */
class FeedZapper_Parser_FeedItem_SimplePie extends FeedZapper_Parser_FeedItem_Base {

    /**
     * This serves as a substring of the `feed_zapper_filter_feed_simplepie_item` filter.
     * @var string
     */
    protected $_sItemType = 'simplepie';

    /**
     * @param array $aItem
     * @param SimplePie_Item $oItem
     * @param array $aArguments
     *
     * @return array
     */
    public function replyToGetItem( array $aItem, $oItem, array $aArguments ) {

        $aArguments = $aArguments + array(
            'source_timezone' => 0,
        );
        $_iTimezoneOffset = ( integer ) $aArguments[ 'source_timezone' ];
        $_nsDescription   = $oItem->get_description();
        $_nsContent       = $oItem->get_content();
        $_oThisFeed       = $oItem->get_feed();

        $_aItem = array(
            'id'            => $this->___getID( $oItem ),   // guid
            'title'         => $oItem->get_title(),
            'date'          => $this->___getItemDate( $oItem, $_iTimezoneOffset ),
            'timestamp'     => $oItem->get_date( 'U' ),
            '_raw_date'     => $oItem->get_date( '' ),
            'authors'       => $this->___getItemAuthors( $oItem ),
            'permalink'     => $oItem->get_permalink(),
            'description'   => $_nsDescription,
            'content'       => $_nsContent,
            // Thumbnail image set as an item element. Only a single item is set.
            'thumbnail'     => '',
            'source'        => $oItem->get_base(),
            'source_feed'   => $_oThisFeed->subscribe_url(),
            'categories'    => $this->___getCategories( $oItem ),
        ) + $aItem + $this->_aItem;

        // Images in the content. Multiple images are possible.
        $_aItem[ 'images' ] = apply_filters(
            'feed_zapper_filter_images_extracted_from_html',    // filter hook name
            array(),    // image container array
            $_nsContent ? $_nsContent : $_nsDescription, // html text to parse
            $oItem
        );

        // Enclosure (additional contents)
        if ( $_oEnclosure = $oItem->get_enclosure() ) {
            $_aItem[ 'description' ] .= $_oEnclosure->get_description();
            $_sImageURL = $_oEnclosure->get_thumbnail();
            if ( filter_var( $_sImageURL, FILTER_VALIDATE_URL ) ) {
                $_aItem[ 'images' ][ $_sImageURL ] = $_sImageURL;
                $_aItem[ 'thumbnail' ] = $_sImageURL;
            }
            if ( false !== strpos( $_oEnclosure->get_medium(), 'image' ) ) {    // ->get_type() does not return a string even though an image exists.
                $_sImageURL = $_oEnclosure->get_link();
                if ( filter_var( $_sImageURL, FILTER_VALIDATE_URL ) ) {
                    $_aItem[ 'images' ][ $_sImageURL ] = $_sImageURL;
                    $_aItem[ 'thumbnail' ] = $_sImageURL;
                }
            }
        }

        if ( ! $_aItem[ 'thumbnail' ] && ! empty( $_aItem[ 'images' ] ) ) {
            $_aItem[ 'thumbnail' ] = $this->getFirstElement( $_aItem[ 'images' ] ); // use the first found image.
        }

        return $_aItem;

    }
        /**
         * @param SimplePie_Item $oItem
         * @return null|string
         */
        private function ___getID( SimplePie_Item $oItem ) {
            $_sID = $oItem->get_id();
            if ( $_sID ) {
                return $this->___getItLikeURL( $_sID );
            }
            // GUID must be within the 255 character length. Permalinks of Google News feed items often exceed 255 characters.
            $_sPermalink = $oItem->get_permalink();
            if ( strlen( $_sPermalink ) > 255 ) {
                $_sPermalink = md5( $_sPermalink );
            }
            return $this->___getItLikeURL( $_sPermalink );
        }
            /**
             * @remark  It seems GUID passed to `wp_insert_post()` gets modified to a URL.
             * So if a string that is not a form of URL is passed, the item ID value becomes different.
             * This becomes a problem when deciding to create a new post by comparing GUID to detect duplicates.
             * @param $sString
             * @return string
             */
            private function ___getItLikeURL( $sString ) {
                if ( filter_var( $sString, FILTER_VALIDATE_URL ) ) {
                    return $sString;
                }
                // tag:news.google.com,2005:cluster=52781318790010
                // wp_insert_post() makes it something like -> http(s)://siteurl/[encoded string]
                return 'http://' . md5( $sString );
            }

        /**
         * @since       0.0.1
         * @return      string  a formatted date string.
         */
        private function ___getItemDate( SimplePie_Item $oFeedItem, $iHourOffset ) {

            $_iSecondOffset   = $iHourOffset * 60 * 60;
            return date(
                $this->_sSiteTimeFormat, // date/time format
                $oFeedItem->get_date( 'U' ) + $_iSecondOffset // time-stamp
            );

        }
        /**
         * @since       0.0.1
         * @return      array
         */
        private function ___getItemAuthors( SimplePie_Item $oFeedItem ) {

            $_aAuthors = array();
            $_aAuthorObjects = $oFeedItem->get_authors();
            if ( empty( $_aAuthorObjects ) ) {
                return $_aAuthors;
            }
            foreach( $_aAuthorObjects as $_oAuthor ) {
                $_aAuthors[] = array(
                    'link'  => $_oAuthor->get_link(),
                    'email' => $_oAuthor->get_email(),
                    'name'  => $_oAuthor->get_name(),
                );
            }
            return $_aAuthors;

        }

        /**
         * @param SimplePie_Item|SimplePie_Item $oFeed
         *
         * @return array
         */
        private function ___getCategories( SimplePie_Item $oFeed ) {
            $_aCategories = array();
            $_aCategoryObjs = $oFeed->get_categories();
            if ( empty( $_aCategoryObjs ) ) {
                return $_aCategories;
            }
            /* @var $_oCategory SimplePie_Category */
            foreach( $_aCategoryObjs as $_oCategory ) {
                $_nsLabel = $_oCategory->get_label();
                if ( null === $_nsLabel ) {
                    continue;
                }
                // Sometimes terms are not delimited
                $_aSplit = preg_split( "/[,]\s*/", trim( $_nsLabel ), 0, PREG_SPLIT_NO_EMPTY );
                $_aCategories = array_merge( $_aCategories, $_aSplit );
            }

            return array_unique( $_aCategories );
        }


}