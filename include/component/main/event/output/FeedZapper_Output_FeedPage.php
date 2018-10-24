<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Handles feed outputs.
 *
 * @since    0.0.1
 */
class FeedZapper_Output_FeedPage {

    public function __construct() {

        // The hook is registered only once.
        static $bLoaded = false;
        if ( $bLoaded ) {
            return;
        }
        $bLoaded = true;

        add_filter(
            'the_content',
            array( $this, 'replyToGetOutput' ),
            10,  // priority
            1   // number of parameters
        );
    }

    public function replyToGetOutput( $sContent ) {

        if ( ! is_singular() ) {
            return $sContent;
        }
        if ( ! is_main_query() ) {
            return $sContent;
        }

        $_oOption = FeedZapper_Option::getInstance();
        $_iPageID = ( integer ) $_oOption->get( array( 'feed', 'page', 'value' ), 0 );

        if ( $_iPageID !== $GLOBALS[ 'post' ]->ID ) {
            return $sContent;
        }
        // Displays the user's entire populated feed outputs.
        return getFeedZapperFeed(
                array(
                    'count'      => 50,
                    'skip_query' => true,   // the Carousel template displays contents with Ajax
                ),
                false
            )
            . $sContent;
        // @todo The comment <!-- Created by Feed Zapper --> is enclosed in a <p> tag and creates an empty extra space.

    }

}