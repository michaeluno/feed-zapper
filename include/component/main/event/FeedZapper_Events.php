<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 * Plugin event handler.
 * 
 * @package      FeedZapper
 * @since    0.0.1
 */
class FeedZapper_Events {

    /**
     * Triggers event actions.
     */
    public function __construct() {

        // Output
        new FeedZapper_Output_FeedPage;

        // Background events
        new FeedZapper_Event_Action_HTTPCacheRenewal;

        // Custom Cron Interval
        new FeedZapper_WPCronCustomInterval;

    }
    
}