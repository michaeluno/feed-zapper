<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Provides the interface for creating a component loader class.
 *
 * @package  FeedZapper
 * @since    0.0.1
 */
abstract class FeedZapper_Loader_Base {

    protected $_sFilePath = '';

    public function __construct( $sFilePath ) {

        $this->_sFilePath = $sFilePath;

        $this->_construct();
        $this->_load();
        if ( is_admin() ) {
            $this->_loadInAdmin();
        }

    }

    protected function _construct() {}
    protected function _load() {}
    protected function _loadInAdmin() {}

}