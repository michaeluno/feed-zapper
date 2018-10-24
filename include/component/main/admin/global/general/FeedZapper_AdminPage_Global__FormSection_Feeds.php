<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno; Licensed under <LICENSE_TYPE>
 */

/**
 * Adds the 'Feeds' form section to the 'General' tab.
 *
 * @since    0.0.1
 */
class FeedZapper_AdminPage_Global__FormSection_Feeds extends FeedZapper_AdminPage__FormSection_Base {

    /**
     *
     * @since   0.0.1
     */
    protected function _getArguments( $oFactory ) {

        return array(
            'section_id'        => 'feed',
            'tab_slug'          => $this->_sTabSlug,
            'title'             => __( 'Feeds', 'feed-zapper' ),
        );

    }

    /**
     * Get adding form fields.
     * @since    0.0.1
     * @return   array
     */
    protected function _getFields( $oFactory ) {
        return array(
            array(
                'field_id'          => 'page',
                'type'              => 'select2',
                'title'             => __( 'Feed Page', 'feed-zapper' ),
                'description'       => __( 'Specify the page that your subscribing feeds will be displayed.', 'feed-zapper' ),
                'callback'        => array(
                    // If the `search` callback is set, the field will be AJAX based.
                    'search'    => __CLASS__ . '::getPages',
                ),
                'options'         => array(
                    'minimumInputLength' => 2,
                    'width' => '80%',
                ),
            ),
            array(
                'field_id'          => 'update_interval',
                'type'              => 'size',
                'title'             => __( 'Feed Update Interval', 'feed-zapper' ),
                'description'       => __( 'Determines how often the plugin checks and renew subscribed feeds in the background.', 'feed-zapper' ),
                'units'             => array(
                    60       => __( 'minute(s)', 'feed-zapper' ),
                    3600     => __( 'hour(s)', 'feed-zapper' ),
                    86400    => __( 'day(s)', 'feed-zapper' ),
                    604800   => __( 'week(s)', 'feed-zapper' ),
                ),
                'attributes'        => array(
                    'size'      => array(
                        'step' => 0.1
                    ),
                ),
//                'default'           => array(
//                    'size'      => 1,
//                    'unit'      => 3600
//                ),
                'after_fieldset'    => $this->___getCronWarning(),
            ),
            array(
                'field_id'          => 'retention_period',
                'type'              => 'size',
                'title'             => __( 'Retention Period', 'feed-zapper' ),
                'description'       => __( 'The period of time to keep untouched feed items.', 'feed-zapper' )  
                    . __( 'Feed items older than this time span will be automatically deleted.', 'feed-zapper' ),
                'units'             => array(
                    3600     => __( 'hour(s)', 'feed-zapper' ),
                    86400    => __( 'day(s)', 'feed-zapper' ),
                    604800   => __( 'week(s)', 'feed-zapper' ),
                ),
            ),
        );

    }
        private function ___getCronWarning() {
            if ( $this->___isWPCronEnabled() ) {
                return '';
            }
            return '<p>'
                . '<span class="error-message">* ' . __( 'Warning', 'feed-zapper' ) . ':</span> '
                . sprintf(
                    __( '<a href="%1$s">WP Cron</a> is disabled on your site. It is strongly recommended that you set up a real cron job on your server. e.g.<code>%2$s</code>', 'feed-zapper' ),
                    esc_url( 'https://developer.wordpress.org/plugins/cron/#what-is-wp-cron' ),
                    '*/30 * * * * curl -vs -o /dev/null ' . site_url( 'wp-cron.php' ) . ' > /dev/null 2>&1'
                )
                . '</p>';
        }
            private function ___isWPCronEnabled() {

                if ( ! defined( 'DISABLE_WP_CRON' ) ) {
                    return true;
                }
                return DISABLE_WP_CRON
                    ? false
                    : true;
            }

    protected function _validate( $aInputs, $aOldInputs, $oFactory, $aSubmitInfo ) {
        $_bVerified = true;
        $_aErrors   = array();

        // An invalid value is found. Set a field error array and an admin notice and return the old values.
        if ( ! $_bVerified ) {
            $oAdminPage->setFieldErrors( $_aErrors );
            $oAdminPage->setSettingNotice( __( 'There was something wrong with your input.', 'feed-zapper' ) );
            return $aOldInputs;
        }

        $this->___updateWPCronEvent( $aInputs[ 'update_interval' ], $aOldInputs[ 'update_interval' ] );

        return $aInputs;

    }

        private function ___updateWPCronEvent( $aNewInterval, $aOldInterval ) {

            $_aArguments = array(); // the argument must be an empty array. This is used when unscheduling the event.

            // If for some reasons (such as by thrid-parties) the event is not scheduled, schedule it.
            if ( ! wp_next_scheduled( FeedZapper_Registry::$aScheduledActionHooks[ 'feed_renew' ], $_aArguments ) ) {
                FeedZapper_PluginUtility::scheduleFeedChecks();
                return;
            }

            // If the interval option is the same, do nothing
            if ( $this->___isFeedUpdateIntervalSame( $aNewInterval, $aOldInterval ) ) {
                return;
            }

            // At this point, the interval option is changed. Schedule the event with the new interval.
            FeedZapper_PluginUtility::rescheduleFeedChecks();

        }

        private function ___isFeedUpdateIntervalSame( $aNewInterval, $aOldInterval ) {
            if ( $aNewInterval[ 'size' ] != $aOldInterval[ 'size' ] ) {
                return false;
            }
            if ( $aNewInterval[ 'unit' ] != $aOldInterval[ 'unit' ] ) {
                return false;
            }
            return true;
        }


    static public function getPages( $aQueries, $aFieldset ) {

        $_aArgs         = array(
            'post_type'         => 'page',
            'paged'             => $aQueries[ 'page' ],
            's'                 => $aQueries[ 'q' ],
            'posts_per_page'    => 30,
            'nopaging'          => false,
        );
        $_oResults      = new WP_Query( $_aArgs );
        $_aPostTitles   = array();
        foreach( $_oResults->posts as $_iIndex => $_oPost ) {
            $_aPostTitles[] = array(    // must be numeric
                'id'    => $_oPost->ID,
                'text'  => $_oPost->post_title,
            );
        }
        return array(
            'results'       => $_aPostTitles,
            'pagination'    => array(
                'more'  => intval( $_oResults->max_num_pages ) !== intval( $_oResults->get( 'paged' ) ),
            ),
        );

    }

}