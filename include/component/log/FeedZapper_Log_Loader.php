<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Loads the log component.
 *
 * @package      FeedZapper
 * @since    0.0.1
 */
class FeedZapper_Log_Loader extends FeedZapper_Loader_Base {

    protected function _load() {
        $this->___loadPostTypes();
        $this->___listenEvents();

    }
        private function ___listenEvents() {
            new FeedZapper_Log_Action_Log;
            new FeedZapper_WPCronAction_DeleteOldLogItems;
        }
        private function ___loadPostTypes() {
            new FeedZapper_PostType_Log(
                FeedZapper_Registry::$aPostTypes[ 'log' ],
                array(),    // post type arguments - set in setUp()
                $this->_sFilePath, // caller file path
                FeedZapper_Registry::TEXT_DOMAIN
            );
        }

    protected function _loadInAdmin() {
    }

}