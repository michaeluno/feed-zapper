<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Provides an abstract base for adding pages.
 * 
 * @since       0.0.1
 */
abstract class FeedZapper_AdminPage__Page_Base extends FeedZapper_AdminPage__Element_Base {
        
    protected $_sPageSlug = '';
    
    /**
     * Sets up hooks and properties.
     */
    public function __construct( $oFactory ) {
        
        $this->_oFactory     = $oFactory;
        $this->_aArguments   = $this->_getArguments( $oFactory );
        $this->_sPageSlug    = $this->getElement( $this->_aArguments, 'page_slug' );
        
        $this->_construct( $oFactory );
        
        $this->___addPage( $this->_sPageSlug, $this->_aArguments );
        
    }
    
        private function ___addPage( $sPageSlug, $aArguments ) {
            
            if ( ! $sPageSlug ) {
                return;
            }
            
            $this->_oFactory->addSubMenuItems( $aArguments );
            add_action( "load_{$sPageSlug}", array( $this, 'replyToLoad' ) );
            add_action( "do_{$sPageSlug}", array( $this, 'replyToDo' ) );

            add_filter(
                'validation_' . $sPageSlug,
                array( $this, 'replyToValidate' ),
                10,
                4
            );

        }

    public function replyToDo( $oFactory ) {
        $this->_do( $oFactory );
    }
    
    protected function _do( $oFactory ) {}

    public function replyToValidate( $aInputs, $aOldInputs, $oAdmin, $aSubmitInfo ) {
        return $this->_validate( $aInputs, $aOldInputs, $oAdmin, $aSubmitInfo );
    }
    protected function _validate( $aInputs, $aOldInputs, $oAdmin, $aSubmitInfo ) {
        return $aInputs;
    }
    
}