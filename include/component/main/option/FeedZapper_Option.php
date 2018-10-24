<?php
/**
 * Feed Zapper
 * 
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 * Handles plugin options.
 * 
 * @since    0.0.1
 */
class FeedZapper_Option extends FeedZapper_Option_Base {

    /**
     * Stores instances by option key.
     * 
     * @since    0.0.1
     */
    static public $aInstances = array(
        // key => object
    );

    /**
     * Returns the instance of the class.
     * 
     * This is to ensure only one instance exists.
     * 
     * @since      0.0.1
     */
    static public function getInstance( $sOptionKey='' ) {
        
        $sOptionKey = $sOptionKey 
            ? $sOptionKey
            : FeedZapper_Registry::$aOptionKeys[ 'setting' ];
        
        if ( isset( self::$aInstances[ $sOptionKey ] ) ) {
            return self::$aInstances[ $sOptionKey ];
        }
        $_sClassName = apply_filters( 
            FeedZapper_Registry::HOOK_SLUG . '_filter_option_class_name',
            __CLASS__ 
        );
        self::$aInstances[ $sOptionKey ] = new $_sClassName( $sOptionKey );
        return self::$aInstances[ $sOptionKey ];
        
    }

    /**
     * Some elements need recursive merge and some not.
     *
     * @param $sOptionKey
     * @return array
     */
    protected function _getFormatted( $sOptionKey ) {
        $_aDefaults = $this->getDefaults();
        $_aOptions  = $this->getAsArray(
                $this->bIsNetworkAdmin
                    ? get_site_option( $sOptionKey, array() )
                    : get_option( $sOptionKey, array() )
            ) + $_aDefaults;
        $_aOptions[ 'feed' ] = $this->uniteArrays(
            $_aOptions[ 'feed' ],
            $_aDefaults[ 'feed' ]
        );
//        $_aOptions[ 'permission' ][ 'user_roles' ] = is_array( $_aOptions[ 'permission' ][ 'user_roles' ] )
//            ? $_aOptions[ 'permission' ][ 'user_roles' ]
//            : $_aDefaults[ 'permission' ][ 'user_roles' ];

        return $_aOptions;
    }

    /**
     * @param array $aOptions
     *
     * @return array
     */
    public function getDefaults( array $aOptions=array() ) {
        return parent::getDefaults( $aOptions + FeedZapper_Registry::$aOptions );
    }

    /**
     * Checks whether the plugin debug mode is on or not.
     * @return      boolean
     */ 
    public function isDebug() {
        return defined( 'WP_DEBUG' ) && WP_DEBUG;
    }
    
}