<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno; Licensed under <LICENSE_TYPE>
 */

/**
 * Adds the 'Feeds' form section to the 'Cache' tab.
 *
 * @since    0.0.1
 */
class FeedZapper_AdminPage_Global__FormSection_FeedCache extends FeedZapper_AdminPage__FormSection_Base {

    /**
     *
     * @since   0.0.1
     */
    protected function _getArguments( $oFactory ) {

        return array(
            'section_id'        => 'cache',
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
                'field_id'          => 'update_interval',
                'type'              => 'size',
                'title'             => __( 'Feed Update Interval', 'amazon-auto-links' ),
                'description'       => __( 'Determines how often the plugin checks and renew subscribed feeds in the background.', 'feed-zapper' ),
//                'capability'        => 'manage_options', // inherits from the setting page
                'units'             => array(
                    60       => __( 'minute(s)', 'amazon-auto-links' ),
                    3600     => __( 'hour(s)', 'amazon-auto-links' ),
                    86400    => __( 'day(s)', 'amazon-auto-links' ),
                    604800   => __( 'week(s)', 'amazon-auto-links' ),
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
            )
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
            wp_clear_scheduled_hook( FeedZapper_Registry::$aScheduledActionHooks[ 'feed_renew' ], $_aArguments );
            FeedZapper_PluginUtility::scheduleFeedChecks();

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

}