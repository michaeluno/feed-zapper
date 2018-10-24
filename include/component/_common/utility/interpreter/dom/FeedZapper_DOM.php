<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * Provides Dom related functions.
 * 
 * @package     FeedZapper
 * @since       0.0.1
 * @version     1.0.1       Added the `$bHTMLEntitiesConversion` parameter to some methods.
 * Removed $bUseFileGetContents parameter in a method.
 */
final class FeedZapper_DOM extends FeedZapper_Interpreter_Utility {

    public $sCharEncoding = '';
    public $sHTMLCachePrefix = '';
    public $bIsMBStringInstalled = false;

    /**
     * Sets up properties.
     */
    public function __construct() {
        
        $this->sCharEncoding    = get_bloginfo( 'charset' ); 
        // $this->oEncrypt         = new FeedZapper_Encrypt; @deprecated
        $this->sHTMLCachePrefix = FeedZapper_Registry::TRANSIENT_PREFIX . "_HTML_";
            
        $this->bIsMBStringInstalled = function_exists( 'mb_language' );

    }
    
    /**
     * Creates a DOM object from a given HTML string.
     * 
     * @return      object      DOM object
     */
    public function loadDOMFromHTMLElement( $sHTMLElements, $sMBLang='uni', $sSourceCharSet='', $bHTMLEntitiesConversion=false ) {
                
        return $this->loadDOMFromHTML( 
            // Enclosing in a div tag prevents from inserting the comment <!-- xml version .... --> when using saveXML() later.
            '<div>' 
                . $sHTMLElements 
            . '</div>', 
            $sMBLang,
            $sSourceCharSet,
            $bHTMLEntitiesConversion
        );
        
    }    
    /**
     * Creates a DOM object from a given url.
     * @return      object      DOM object
     */
    public function loadDOMFromURL( $sURL, $sMBLang='uni', $sSourceCharSet='' ) {
        return $this->loadDOMFromHTML( 
            $this->getHTML( $sURL ),
            $sMBLang,
            $sSourceCharSet
        );
    }    
    /**
     * 
     * @param       string          $sHTML     
     * @param       string          $sMBLang     
     * @param       string  $sSourceCharSet     If true, it auto-detects the character set. If a string is given, 
     * the HTML string will be converted to the given character set. If false, the HTML string is treated as it is.
     */
    public function loadDOMFromHTML( $sHTML, $sMBLang='uni', $sSourceCharSet='', $bHTMLEntitiesConversion=false ) {
        
        // without this, the characters get broken    
        if ( ! empty( $sMBLang ) && $this->bIsMBStringInstalled ) {
            mb_language( $sMBLang ); 
        }
       
        if ( false !== $sSourceCharSet ) {
            $sHTML       = $this->convertCharacterEncoding( 
                $sHTML, // subject
                $this->sCharEncoding, // to
                $sSourceCharSet, // from
                $bHTMLEntitiesConversion   // false for no html entities conversion
            );           
        }

        // @todo    Examine whether the below line takes effect or not.
        // mb_internal_encoding( $this->sCharEncoding );                     
        
        $oDOM                     = new DOMDocument( 
            '1.0', 
            $this->sCharEncoding
        );
        $oDOM->recover            = true;    // @see http://stackoverflow.com/a/7386650, http://stackoverflow.com/a/9281963
        // $oDOM->strictErrorChecking = false; // @todo examine whether this is necessary or not.
        $oDOM->preserveWhiteSpace = false;
        $oDOM->formatOutput       = true;
        @$oDOM->loadHTML( 
            function_exists( 'mb_convert_encoding' )
                ? mb_convert_encoding( $sHTML, 'HTML-ENTITIES', $this->sCharEncoding )
                : $sHTML
        );    
        return $oDOM;
        
    }
    
    /**
     * 
     * @return      string
     */
    public function getInnerHTML( $oNode ) {
        $sInnerHTML  = ""; 
        if ( ! $oNode ) {
            return $sInnerHTML;
        }
        $oChildNodes = $oNode->childNodes; 
        foreach ( $oChildNodes as $oChildNode ) { 
            $oTempDom    = new DOMDocument( '1.0', $this->sCharEncoding );
            
            $_oImportedNode = $oTempDom->importNode( 
                $oChildNode, 
                true 
            );
            if ( $_oImportedNode ) {
                $oTempDom->appendChild( 
                    $_oImportedNode    
                ); 
            }

            // Sometimes <html><body> tags get inserted.
            $sInnerHTML .= $this->___getAutoInjectedWrapperTagsRemoved( @$_oTempDom->saveHTML() );

        } 
        return $sInnerHTML;     
        
    }
        /**
         * Removes wrapped `<html>` and `<body>`tags from a given string.
         *
         * Sometimes $oDOM->saveHTML() returns a string with <html><body> wrapped. Use this method to remove those.
         *
         * @return      string
         */
        private function ___getAutoInjectedWrapperTagsRemoved( $sHTML ) {

            $sHTML = trim( $sHTML );

            if ( $this->bLoadHTMLFix ) {
                return $sHTML;
            }

            return preg_replace(
                '~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i',
                '',
                $sHTML
            );

        }
    /**
     * Fetches HTML body with the specified URL with caching functionality.
     * 
     * @return      string
     */
    public function getHTML( $sURL ) {
        $_oHTML = new FeedZapper_HTTPClient( $sURL );
        return $_oHTML->get();
    }

    /**
     * Modifies the attributes of the given node elements by specifying a tag name.
     * 
     * Example:
     * `
     * $oDom->setAttributesByTagName( $oNode, 'a', array( 'target' => '_blank', 'rel' => 'nofollow' ) );
     * `
     */
    public function setAttributesByTagName( DOMDocument $oNode, $sTagName, $aAttributes=array() ) {
        
        foreach( $oNode->getElementsByTagName( $sTagName ) as $_oSelectedNode ) {
            foreach( $this->getAsArray( $aAttributes ) as $_sAttribute => $_sProperty ) {
                if ( in_array( $_sAttribute, array( 'src', 'href' ) ) ) {
                    $_sProperty = esc_url( $_sProperty );
                }
                @$_oSelectedNode->setAttribute( 
                    $_sAttribute, 
                    esc_attr( $_sProperty )
                );
            }
        }
            
    }

    /**
     * Removes nodes by tag and class selector. 
     * 
     * Example:
     * `
     * $this->oDOM->removeNodeByTagAndClass( $nodeDiv, 'span', 'riRssTitle' );
     * `
     */
    public function removeNodeByTagAndClass( DOMDocument $oNode, $sTagName, $sClassName, $iIndex='' ) {
        
        $oNodes = $oNode->getElementsByTagName( $sTagName );
        
        // If the index is specified,
        if ( 0 === $iIndex || is_integer( $iIndex ) ) {
            $oTagNode = $oNodes->item( $iIndex );
            if ( $oTagNode ) {
                if ( stripos( $oTagNode->getAttribute( 'class' ), $sClassName ) !== false ) {
                    $oTagNode->parentNode->removeChild( $oTagNode );
                }
            }
        }
        
        // Otherwise, remove all - Dom is a live object so iterate backwards
        for ( $i = $oNodes->length - 1; $i >= 0; $i-- ) {
            $oTagNode = $oNodes->item( $i );
            if ( stripos( $oTagNode->getAttribute( 'class' ), $sClassName ) !== false ) {
                $oTagNode->parentNode->removeChild( $oTagNode );
            }
        }
        
    }                
    
}