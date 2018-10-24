<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 * A dummy SimplePie transient class.
 */
class FeedZapper_SimplePie_Cache_Transient {

	public function __construct($location, $filename, $extension) {}

	/**
	 * @access public
	 */
	public function save($data) {
		return true;
	}

	/**
	 * @access public
	 */
	public function load() {
        return null;        
	}

	/**
	 * @access public
	 */
	public function mtime() {
        return 0;   // SimplePie thinks it is expired. So the plugin custom HTTP client will be used.
	}

	/**
	 * @access public
	 */
	public function touch() {
        return true;        
	}

	/**
	 * @access public
	 */
	public function unlink() {
        return true;        
	}
    
}