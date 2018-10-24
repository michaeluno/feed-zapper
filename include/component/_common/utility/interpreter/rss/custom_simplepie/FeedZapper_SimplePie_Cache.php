<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 *
 */
class FeedZapper_SimplePie_Cache extends SimplePie_Cache {
    
    /**
     * Create a new SimplePie_Cache object
     *
     * @static
     * @access public
     */
    function create( $location, $sFileName, $extension ) {        
        return new FeedZapper_SimplePie_Cache_Transient(
            $location, 
            $sFileName,
            $extension
        );
    } 
    
}