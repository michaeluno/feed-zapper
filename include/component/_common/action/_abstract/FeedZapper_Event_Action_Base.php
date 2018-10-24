<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 * Provides base methods for plugin WP Cron event actions.

 * @since        0.0.1
 */
abstract class FeedZapper_Event_Action_Base extends FeedZapper_PluginUtility {

    protected $_sActionHookName     = '';
    protected $_iCallbackParameters = 1;
    protected $_iHookPriority       = 10;

    /**
     * Sets up hooks.
     * @since       0.0.1
     */
    public function __construct() {

        add_action(
            $this->_sActionHookName,
            array( $this, 'doAction' ),
            $this->_iHookPriority, // priority
            $this->_iCallbackParameters
        );

        $this->_construct();

    }

    /**
     * @since       0.0.1
     */
    protected function _construct() {}

    /**
     * @remark          Override this method in an extended class.
     * @callback        action
     */
    public function doAction( /* $aArguments */ ) {
    }


}