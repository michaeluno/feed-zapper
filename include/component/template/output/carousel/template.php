<?php
/*
 * Available variables:
 * 
 * $aOptions - the plugin options
 * $aItems - the fetched product links
 * $aArguments - the user defined arguments such as image size and count etc.
 */
//echo $GLOBALS[ 'wpdb' ]->posts;
//echo '<br />';
//echo $GLOBALS[ 'wpdb' ]->term_relationships;
//return;

$_oUtil  = new FeedZapper_Template_Carousel_Utility;
$_aTags  = $_oUtil->getUserChannels( get_current_user_id(), 40 );

//$_aPostCounts = wp_list_pluck( $_aTags, 'count' );
array_unshift(
    $_aTags,
    array(
        'name'    => __( 'All', 'feed-zapper' ),
        'term_id' => 0,
//        'count'   => array_sum( $_aPostCounts ),  // @deprecated as unbalance in the word cloud
        'count'   => 0,
    )
);
array_push(
    $_aTags,
    array(
        'name'    => __( 'Read Later', 'feed-zapper' ),
        'term_id' => -1,
        'count'   => 0,
    )
);

?>


<?php
$_aWordCloudTags = array();
foreach( $_aTags as $_iIndex => $_aTag ) {
    $_aWordCloudTags[ $_aTag[ 'name' ] ] = $_aTag[ 'count' ];
}
echo $_oUtil->getWordCloud( $_aWordCloudTags );
?>


<?php
/* @deprecated 0.2.3
<div class="feed-zapper-all-feeds-slider-nav">
    <?php
    $_iCount = 1;
    foreach( $_aTags as $_iIndex => $_aTag ) {
        $_sHidden = 1 < $_iCount ? 'hidden' : '';
        $_sHidden = '';
        echo "<div class='feed-title-container {$_sHidden}'>"
                . "<h5 class='feed-title'>" . $_aTag[ 'name' ] . "</h5>"
            . "</div>";
        $_iCount++;
    }
    ?>
</div><!-- .feed-zapper-all-feeds-slider-nav -->
*/
?>

<div class="feed-action align-right"><span class="dashicons dashicons-admin-generic"></span></div>
<div class="feed-zapper-all-feeds">
    <?php
    $_iCount = 1;
    foreach( $_aTags as $_iIndex => $_aTag ) {
        $_sHidden = 1 < $_iCount ? 'hidden' : '';
        $_sHidden = '';
        echo "<div class='feeds {$_sHidden}'>"
                . "<div class='feed-head'>"
                    . "<div></div>"
                    . "<div>"
                        . "<h2 class='feed-title' data-term_id='{$_aTag[ 'term_id' ]}'>" . $_aTag[ 'name' ] . "</h2>"
                    . "</div>"
                    . "<div>"
                        . "<div class='feed-item-action-check-latest feed-item-action' title='" . esc_attr( __( 'Check Latest', 'feed-zapper' ) ) . "'>"
                            . "<span class='dashicons dashicons-update-alt'></span>"
                        . "</div>"
                    . "</div>"
                . "</div>"
                . "<div class='feed-body'>"
                    // body content (feed item output) will be placed here
                    . '<div class="feed-zapper-feed-container">'
                    . '</div><!-- .feed-zapper-feed-container -->'
                . "</div><!-- feed-body -->"
            . "</div>";
        $_iCount++;
    }
    ?>
</div><!-- .feed-zapper-feeds -->