<?php
if ( $_bDebugMode ) {

//    echo "<div class='debug-info'>";
//    if ( $iTimeDBQuery ) {
//        echo "<p>DB Query Time: {$iTimeDBQuery}</p>";
//    }
//    if ( $iTimeItemParsing ) {
//        echo "<p>Item Parsing Time: {$iTimeItemParsing}</p>";
//    }
//    echo "</div>";

}


$_sLastItem     = '';
foreach( $aItems as $_iIndex => $_aItem ) :

    end($aItems );
    if ( ! $bHasMore && $_iIndex === key( $aItems ) ) {
        $_sLastItem = 'last-item';
    }

    $_sPermalinkHost = parse_url( $_aItem[ 'permalink' ], PHP_URL_HOST);
    ?>

    <div class="feed-zapper-feed-item <?php echo $_sLastItem; ?>" data-time="<?php echo $_aItem[ 'date' ]; ?>" data-id="<?php echo esc_attr( $_aItem[ 'id' ] ); ?>" data-post_id="<?php echo $_aItem[ '_post_id' ]; ?>" data-host="<?php echo esc_attr( $_sPermalinkHost ); ?>">
        <div class="feed-zapper-item-head">
            <div class="feed-item-action-dismiss feed-item-action float-right"><span class="dashicons dashicons-dismiss"></span></div>
            <h2 class="feed-zapper-feed-title">
                <a href="<?php echo esc_url( $_aItem[ 'permalink' ] ); ?>" target="_blank" rel="nofollow">
                    <?php echo $_aItem[ 'title' ]; ?>
                </a>
            </h2>
        </div>
        <div class="feed-zapper-feed-item-images"><?php
        foreach( $_aItem[ 'images' ] as $_iIndex => $_sIMGURL ) {
            echo "<div class='feed-zapper-feed-item-image'>"
                    . "<img data-src='" . esc_url( $_sIMGURL ) .  "' alt='" . esc_attr( 'Loading...', 'feed-zapper' ) . "'/>"
                . "</div>";
        } ?></div>
        <div class="feed-zapper-feed-body">
            <div class="feed-zapper-feed-meta">
                <span class="feed-zapper-feed-date"><?php echo human_time_diff( $_aItem[ 'timestamp' ], current_time( 'timestamp', true ) ) . " " . __( 'ago' ); ?></span>
                <span class="feed-zapper-feed-author">
                <?php
                $_aAuthors = array();
                foreach( $_aItem[ 'authors' ] as $_aAuthor ) {
                    if ( ! $_aAuthor[ 'name' ] ) {
                        continue;
                    }
                    $_aAuthors[] = "<a href='" . esc_url( $_aAuthor[ 'link' ] ) . "' data-email='" . esc_attr( $_aAuthor[ 'email' ] ) . "'>" . $_aAuthor[ 'name' ] . "</a>";
                }
                $_sAuthors = trim( implode( ', ', $_aAuthors ), ', ' );
                echo $_sAuthors;
                ?>
                </span>
                <span class="feed-zapper-feed-source">from <a href="<?php echo esc_attr( $_aItem[ 'source' ] ); ?>" target="_blank"><?php echo parse_url( $_aItem[ 'source' ], PHP_URL_HOST ); ?></a></span>
                <span class="feed-zapper-feed-categories"><?php echo trim( implode( ', ', $_aItem[ 'categories' ] ), ',' ); ?></span>
                <div class="feed-item-actions float-right align-right">
                    <div class="feed-item-action-read-later feed-item-action" title="<?php echo esc_attr( __( 'Read later', 'feed-zapper' ) ); ?>"><span class="dashicons dashicons-paperclip"></span></div>
                    <div class="feed-item-action-mute feed-item-action" title="<?php echo esc_attr( __( 'Mute', 'feed-zapper' ) ); ?>"><span class="dashicons dashicons-filter"></span></div>
                    <div class="feed-item-action-menu feed-item-action" title="<?php echo esc_attr( __( 'Menu', 'feed-zapper' ) ); ?>"><span class="dashicons dashicons-menu"></span></div>
                </div>
            </div>
            <div class='feed-zapper-feed-description'>
                <p>
                <?php echo FeedZapper_Template_Utility::getTruncatedString( strip_tags( $_aItem[ 'description' ] ), 200 ); ?>
                </p>
            </div>
        </div>
    </div><!-- .feed-zapper-feed-item -->
<?php endforeach; ?>