<?php
$_sLastItem = '';
foreach( $aItems as $_iIndex => $_aItem ) :

    end($aItems );
    if ( ! $bHasMore && $_iIndex === key( $aItems ) ) {
        $_sLastItem = 'last-item';
    }

    ?>
<div class="feed-zapper-feed-item <?php echo $_sLastItem; ?>" data-time="<?php echo $_aItem[ 'date' ]; ?>" data-id="<?php echo esc_attr( $_aItem[ 'id' ] ); ?>" data-post_id="<?php echo $_aItem[ '_post_id' ]; ?>">
    <div class="feed-zapper-item-head">
        <h2 class="feed-zapper-feed-title">
            <a href="<?php echo esc_url( $_aItem[ 'permalink' ] ); ?>" target="_blank" rel="nofollow">
                <?php echo $_aItem[ 'title' ]; ?>
            </a>
        </h2>
    </div>
    <div class="feed-zapper-feed-item-images"><?php
    foreach( $_aItem[ 'images' ] as $_iIndex => $_sIMGURL ) {
        echo "<div class='feed-zapper-feed-item-image'>"
                . "<img src='" . esc_url( $_sIMGURL ) .  "' alt='" . esc_attr( basename( $_sIMGURL ) ) . "'/>"
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
        </div>
        <div class='feed-zapper-feed-description'>
            <p>
            <?php echo FeedZapper_Template_Utility::getTruncatedString( strip_tags( $_aItem[ 'description' ] ), 200 ); ?>
            </p>
        </div>
    </div>
</div><!-- .feed-zapper-feed-item -->
<?php endforeach; ?>