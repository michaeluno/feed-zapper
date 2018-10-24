<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Creates custom database tables for the plugin.
 * 
 * @since       0.0.1
 */
class FeedZapper_DatabaseTableInstall {

    /**
     * 
     */
    public function __construct( $bInstallOrUninstall ) {

        $_sMethodName = $bInstallOrUninstall
            ? 'install'
            : 'uninstall';
            
        foreach( FeedZapper_Registry::$aDatabaseTables as $_sKey => $_aArguments ) {
            $_sClassName = "FeedZapper_DatabaseTable_{$_sKey}";
            $_oTable     = new $_sClassName;
            $_oTable->$_sMethodName();
        }
 
    }
   
}