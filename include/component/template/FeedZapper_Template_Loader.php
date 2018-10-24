<?php
/**
 * Feed Zapper
 *
 * http://en.michaeluno.jp/feed-zapper/
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Loads template components.
 *
 * @package     FeedZapper
 * @since       0.0.1
 */
class FeedZapper_Template_Loader extends FeedZapper_Loader_Base {

    protected function _load() {

        add_filter( 'feed_zapper_filter_templates_directory_path', array( $this, 'replyToGetTemplatesDirectoryPath' ) );

        new FeedZapper_Template_ResourceLoader;
        $this->___listenEvents();

    }
        private function ___listenEvents() {
        }


    protected function _loadInAdmin() {
    }


    /**
     * @param $sPath
     * @return      string
     * @callback    filter      feed_zapper_filter_templates_directory_path
     */
    public function replyToGetTemplatesDirectoryPath( $sPath ) {
        $_s = DIRECTORY_SEPARATOR;
        return dirname( __FILE__ ) . $_s . 'output';
    }
}