<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Provides feed component specific methods.
 *
 * @package      FeedZapper
 * @since    0.0.1
 */
class FeedZapper_Log_Action_Log extends FeedZapper_Event_Action_Base {

    protected $_sActionHookName     = 'feed_zapper_action_add_log';
    protected $_iCallbackParameters = 2;

    /**
     *
     * @remark          For PHP warnings, use `func_get_args()` to retrieve parameters.
     * @callback        action      feed_zapper_action_add_log
     */
    public function doAction( /* $sMessage, $sTitle */ ) {

        $_aParameters   = func_get_args() + array( '', '' );
        $_sMessage      = $_aParameters[ 0 ];
        $_sTitle        = $_aParameters[ 1 ];
        FeedZapper_Log_Utility::addLog( $_sMessage, $_sTitle );

    }

}