<?php
/*
 * Available variables:
 * 
    // Template variables
    // $aArguments - arguments
    // $aItems - formatted items array
    // $aRawItems - an array holding `SimplePie_Item` objects
    // $iFoundCount - the number indicating the found items
    // $oFeedAsItem - bypassed feed object. For some reasons, $oFeed methods do not work well.
    // $this - this class object
    // $aCategories - channel categories
    // $aTags - (array) stores top 20 tags
 */

?>

<div class="feed-zapper-feed-container">
    <div class="feed-zapper-head">
    <?php if ( ! empty( $_aCategories ) ) : ?>
    <div class="feed-zapper-categories">
        <p><?php _e( 'Categories', 'feed-zapper' ); ?>: <?php echo implode( ', ', $_aCategories ); ?></p>
    </div><!-- .feed-zapper-categories ==>
    <?php endif; ?>
    </div>
    <?php include( dirname( __FILE__ ) . '/include/items.php' );
    ?>
</div><!-- .feed-zapper-feed-container -->
