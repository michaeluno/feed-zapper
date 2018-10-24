<?php
/**
 * Echoes or returns the output of feeds.
 *
 * ### Arguments
 * ```
 * array(
 *      'zapper_id' => 1,   // following zapper user id registered on this site
 *      'tag' => array(
 *          'WordPress', 'PHP'
 *       ),   // comma delimited taxonomy tags
 *      'template' => 'template id here',
 *      'count' => -1, // -1: all, any integer to limit the number of items to retrieve
 *
 * )
 * ```
 *
 * @since       0.0.1
 * @return      string
 */
function getFeedZapperFeed( array $aArguments, $bEcho=true ) {

    $_sOutput = apply_filters(  
        FeedZapper_Registry::HOOK_SLUG . '_filter_feed_output',
        '', // output
        $aArguments
    );
    
    if ( $bEcho ) {
        echo $_sOutput;
        return;
    }
    return $_sOutput;
        
}

/**
 * Echoes or returns the output of feeds by a specified URL.
 *
 * ### Arguments
 * ```
 * array(
 *      'zapper_id' => 1,   // following zapper user id registered on this site
 *      'tag' => array(
 *          'WordPress', 'PHP'
 *       ),   // comma delimited taxonomy tags
 *      'url'   => 'https://some.feed.com/rss2',   // feed url
 *      'template' => 'template id here',
 *      'count' => -1, // -1: all, any integer to limit the number of items to retrieve
 *
 * )
 * ```
 *
 * @since       0.0.1
 * @return      string
 */
function getFeedZapperFeedByURL( array $aArguments, $bEcho=true ) {

    $_sOutput = apply_filters(
        FeedZapper_Registry::HOOK_SLUG . '_filter_feed_output_by_url',
        '', // output
        $aArguments
    );

    if ( $bEcho ) {
        echo $_sOutput;
        return;
    }
    return $_sOutput;

}