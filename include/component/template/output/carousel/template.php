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
$_sNonce = $_oUtil->getTemplateNonce();
$_aTags  = $_oUtil->getUserTags( get_current_user_id(), 20 );
array_unshift(
    $_aTags,
    array(
        'name'    => __( 'All', 'feed-zapper' ),
        'term_id' => 0,
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


<div class="feed-zapper-all-feeds-slider-nav">
    <?php
    $_iCount = 1;
    foreach( $_aTags as $_iIndex => $_aTag ) {
        $_sHidden = 1 < $_iCount ? 'hidden' : '';
        $_sHidden = '';
        echo "<div class='{$_sHidden}'>"
                . "<h5 class='feeds-title'>" . $_aTag[ 'name' ] . "</h5>"
            . "</div>";
        $_iCount++;
    }
    ?>
</div><!-- .feed-zapper-all-feeds-slider-nav -->

<div class="feed-action align-right"><span class="dashicons dashicons-admin-generic"></span></div>

<input type="hidden" class="nonce" data-nonce="<?php echo $_sNonce;?>" />
<div class="feed-zapper-all-feeds">
    <?php
    $_iCount = 1;
    foreach( $_aTags as $_iIndex => $_aTag ) {
        $_sHidden = 1 < $_iCount ? 'hidden' : '';
        $_sHidden = '';
        echo "<div class='feeds {$_sHidden}'>"
                . "<div class='feeds-head'>"
                    . "<h2 class='feeds-title' data-term_id='{$_aTag[ 'term_id' ]}'>" . $_aTag[ 'name' ] . "</h2>"
                . "</div>"
                . "<div class='feeds-body'>"
                    // body content (feed item output) will be placed here
                    . '<div class="feed-zapper-feed-container">'
                    . '</div><!-- .feed-zapper-feed-container -->'
                . "</div><!-- feeds-body -->"
            . "</div>";
        $_iCount++;
    }
    ?>
</div><!-- .feed-zapper-feeds -->