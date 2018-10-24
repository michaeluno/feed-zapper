<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Schedules WP Cron intervals/
 *
 * @package      FeedZapper
 * @since       0.0.1
 */
class FeedZapper_WPCronCustomInterval {

    /**
     * Ensures the class is called only once.
     * @var bool
     */
    static private $___bCalled = false;

    protected $_sIntervalSlug;

    public function __construct() {

        if ( self::$___bCalled ) {
            return;
        }

        $this->_sIntervalSlug = FeedZapper_Registry::$aWPCronIntervals[ 'feed_renew' ];

        add_filter( 'cron_schedules', array( $this, 'replyToAddCustomInterval' ) );

        self::$___bCalled = true;

    }

        /**
         * @param $aSchedules
         *
         * @return array
         */
        public function replyToAddCustomInterval( $aSchedules ){

            if( isset( $aSchedules[ $this->_sIntervalSlug ] ) ) {
                return $aSchedules;
            }

            $_iFeedCheckInterval = $this->___getFeedCheckInterval();
            $aSchedules[ $this->_sIntervalSlug ] = array(
                'interval' => $_iFeedCheckInterval,
                'display'  => sprintf(
                    __( 'Once every %1$s seconds' , 'feed-zapper' ),
                    $_iFeedCheckInterval
                )
            );
            return $aSchedules;

        }
            private function ___getFeedCheckInterval() {
                $_oOption = FeedZapper_Option::getInstance();
                $_iSize   = $_oOption->get( array( 'feed', 'update_interval', 'size' ), 1 );
                $_iUnit   = $_oOption->get( array( 'feed', 'update_interval', 'unit' ), 3600 );
                return ( integer ) $_iSize * $_iUnit;
            }

}