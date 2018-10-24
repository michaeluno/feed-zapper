<?php
/*
 * Available variables:
 * 
 * $aOptions - the plugin options
 * $aItems - the fetched product links
 * $aArguments - the user defined arguments such as image size and count etc.
 */
?>

<?php if ( empty( $aItems ) ) : ?>
    <div><p><?php _e( 'No item found.', 'feed-zapper' ); ?></p></div>
    <?php return true; ?>
<?php endif; ?>    
        
<div class="feed-zapper-preview-container">
<?php

?>
    <h3 id="feed-preview-title"><?php echo $oFeedAsItem->get_title(); ?></h3>
<?php foreach( $aItems as $_aItem ) : ?>
    <div class="feed-zapper-preview-item">
        <h2 class="feed-zapper-preview-title"><a href="<?php echo esc_url( $_aItem[ 'permalink' ] ); ?>" target="_blank" rel="nofollow"><?php echo $_aItem[ 'title' ]; ?></a></h2>
        <div class="feed-zapper-preview-item-images"><?php
        foreach( $_aItem[ 'images' ] as $_iIndex => $_sIMGURL ) {
            echo "<div class='feed-zapper-preview-item-image'>"
                    . "<img src='" . esc_url( $_sIMGURL ) .  "' alt='" . esc_attr( basename( $_sIMGURL ) ) . "'/>"
                . "</div>";
        } ?></div>
        <div class="feed-zapper-preview-description">
            <div class="feed-zapper-preview-meta">
                <span class="feed-zapper-preview-date"><?php echo human_time_diff( $_aItem[ 'timestamp' ], current_time( 'timestamp', true ) ) . " " . __( 'ago' ); ?></span>
            </div>
            <?php echo "<div class='feed-zapper-preview-description'>" . FeedZapper_Template_Utility::getTruncatedString( strip_tags( $_aItem[ 'description' ] ), 200 ) . "</div>"; ?>
        </div>
    </div>
<?php endforeach; ?>    
</div>