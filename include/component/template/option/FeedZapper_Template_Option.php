<?php
/**
 * Feed Zapper
 * 
 * http://en.michaeluno.jp/feed_zapper/
 * Copyright (c) 2018 Michael Uno
 * 
 */

/**
 * Provides methods for template options.
 * 
 * @since       0.0.1
 */
class FeedZapper_Template_Option extends FeedZapper_Option_Base {

    /**
     * @var FeedZapper_Template_Utility
     */
    public $oUtil;

    /**
     * Caches the active templates.
     * 
     * @since 0.0.1    
     */
    static private $_aActiveTemplates = array();
    
    /**
     * Represents the structure of the template option array.
     * @since 0.0.1
     */
    static public $aStructure_Template = array(

        // required keys to include a template when rendering
        'relative_dir_path' => null,  // (string)
        'id'                => null,  // (string)

        // set when for the default template
        'is_active'         => null,  // (boolean)
        'index'             => null,  // (integer)

        // maybe needed for determining active templates
        'name'              => null,  // (string)   will be used to list templates in options.
        'thumbnail_path'    => null,  // (string)

        // for listing table
        'description'       => null,
        'version'           => null,
        'author'            => null,
        'author_uri'        => null,

        // for future
        'custom_css'        => '',
         
    );

    /**
     * The default template path.
     * This is set in the constructor.
     * @var string 
     */
    static private $___sDefaultTemplateDirPath = '';
    
    /**
     * Stores the self instance.
     */
    static public $oSelf;
    
    /**
     * Sets up properties.
     */
    public function __construct( $sOptionKey ) {

        $_s = DIRECTORY_SEPARATOR;
        self::$___sDefaultTemplateDirPath = FeedZapper_Registry::$sDirPath
            . $_s . 'include' . $_s . 'component' . $_s
            . 'template' . $_s . 'output' . $_s . 'feed';

        $this->oUtil = new FeedZapper_Template_Utility;

        add_filter(
            FeedZapper_Registry::HOOK_SLUG . '_filter_default_template_path',
            array( $this, 'replyToReturnDefaultTemplatePath' )
        );

        // The parent constructor triggers the option formatter method.
        // In the method, some class members will be accessed so the parent construct must be called at the end.
        parent::__construct( $sOptionKey );

    }
        /**
         * @callback    feed_zapper_filter_default_template_path
         * @return      string
         */
        public function replyToReturnDefaultTemplatePath( $sPath ) {
            $_aTemplate       = $this->getTemplateArrayByDirPath(
                self::$___sDefaultTemplateDirPath,
                false       // no extra info
            );
            return empty( $_aTemplate )
                ? ''
                : ABSPATH . $_aTemplate[ 'relative_dir_path' ] . '/template.php';
        }
    
    /**
     * Returns an instance of the self.
     * 
     * @remark      To support PHP 5.2, this method needs to be defined in each extended class 
     * as in static methods, it is not possible to retrieve the extended class name in a base class in PHP 5.2.x.
     * @return      FeedZapper_Template_Option
     */
    static public function getInstance( $sOptionKey='' ) {
        
        if ( isset( self::$oSelf ) ) {
            return self::$oSelf;
        }
        $sOptionKey = $sOptionKey 
            ? $sOptionKey
            : FeedZapper_Registry::$aOptionKeys[ 'template' ];
        
        $_sClassName = __Class__;
        self::$oSelf = new $_sClassName( $sOptionKey );            
        return self::$oSelf;
        
    }
    
    /**
     * Returns the formatted options array.
     * @return  array
     */    
    protected function _getFormatted( $sOptionKey ) {
        
        $_aOptions = parent::_getFormatted( $sOptionKey );
        return $_aOptions + $this->___getDefaultTemplates();
        
    }    
        /**
         * @return      array       plugin default templates which should be activated upon installation / restoring factory default.
         */
        private function ___getDefaultTemplates() {
            return array();
            // @deprecated - default template is disabled. Add active templates dynamically in the template resource class.

            $_aDirPaths = array(
                self::$___sDefaultTemplateDirPath,
            );
            $_iIndex     = 0;
            $_aTemplates = array();
            foreach( $_aDirPaths as $_sDirPath ) {
                $_aTemplate = $this->getTemplateArrayByDirPath( $_sDirPath, false );
                if ( empty( $_aTemplate ) ) {
                    continue;
                }
                $_aTemplate[ 'is_active' ] = true;
                $_aTemplate[ 'index' ] = ++$_iIndex;
                $_aTemplates[ $_aTemplate[ 'id' ] ] = $_aTemplate;
            }
            return $_aTemplates;
         
        }
    
    /**
     * Returns an array that holds arrays of activated template information.
     *
     * @since       0.0.1
     * @scope       public      It is accessed from the template loader class.
     */
    public function getActiveTemplates() {

        if ( ! empty( self::$_aActiveTemplates ) ) {
            return self::$_aActiveTemplates;    // using cache
        }
        $_aActiveTemplates = $this->___getActiveTemplatesExtracted( $this->get() );    // passing saved all templates
        $_aActiveTemplates = apply_filters( 'feed_zapper_filter_active_templates', $_aActiveTemplates, $this );
        self::$_aActiveTemplates = $_aActiveTemplates;  // caching
        return $_aActiveTemplates;
        
    }
        /**
         * Extracts active templates from given all template information arrays.
         * @since       0.0.1
         * @return      array
         */
        private function ___getActiveTemplatesExtracted( array $aTemplates ) {
            foreach( $aTemplates as $_sID => $_aTemplate ) {
                // Remove inactive templates.
                if ( ! $this->getElement( $_aTemplate, 'is_active' ) ) {
                    unset( $aTemplates[ $_sID ] );
                    continue;
                }
            }
            return $aTemplates;
        }

        /**
         * Formats the template array.
         * 
         * Takes care of formatting change through version updates.
         * 
         * @since 0.0.1              
         * @return      array       Formatted template array. If the passed value is not an array
         * or something wrong with the template array, an empty array will be returned.
         */
        private function ___getTemplateArrayFormatted( array $aTemplate ) {

            $aTemplate = $aTemplate + self::$aStructure_Template;
            $aTemplate[ 'relative_dir_path' ] = $this->oUtil->getPathSanitized( $aTemplate[ 'relative_dir_path' ] );
            
            $_sDirPath = $this->oUtil->getAbsolutePathFromRelative( $aTemplate[ 'relative_dir_path' ] );
                        
            // Check required files. Consider the possibility that the user may directly delete the template files/folders.
            $_aRequiredFiles = array(
                $_sDirPath . DIRECTORY_SEPARATOR . 'style.css',
                $_sDirPath . DIRECTORY_SEPARATOR . 'template.php',             
            );
            if ( ! $this->oUtil->doFilesExist( $_aRequiredFiles ) ) {
                return array();
            }                                    

            $aTemplate[ 'id' ]                = $this->getElement(
                $aTemplate,
                'id',
                $aTemplate[ 'relative_dir_path' ]
            );     

            // For uploaded templates
            $aTemplate[ 'name' ]              = $this->getElement(
                $aTemplate,
                'name',
                ''
            );     
            $aTemplate[ 'description' ]       = $this->getElement(
                $aTemplate,
                'description',
                ''
            );     
            $aTemplate[ 'version' ]            = $this->getElement(
                $aTemplate,
                'version',
                ''
            );     
            $aTemplate[ 'author' ]             = $this->getElement(
                $aTemplate,
                'author',
                ''
            );     
            $aTemplate[ 'author_uri' ]         = $this->getElement(
                $aTemplate,
                'author_uri',
                ''
            );     
            $aTemplate[ 'is_active' ]          = $this->getElement(
                $aTemplate,
                'is_active',
                false
            );
            return $aTemplate;
            
        }    
            
 
    /**
     * Retrieves the label(name) of the template by template id
     * 
     * @remark            Used when rendering the post type table of units.
     * @deprecated      not used at the moment
     */ 
    public function getTemplateNameByID( $sTemplateID ) {
        return $this->get(
            array( $sTemplateID, 'name' ), // dimensional keys
            '' // default
        );    
    }
 
 
    /**
     * Returns an array holding active template labels.
     * @since 0.0.1
     * @deprecated not used at the moment
     */
    public function getActiveTemplateLabels() {        
        $_aLabels = array();
        foreach( $this->getActiveTemplates() as $_aTemplate ) {
            $_aLabels[ $_aTemplate[ 'id' ] ] = $_aTemplate[ 'name' ];
        }
        return $_aLabels;
    }
    /**
     *
     * Used by form filed classes to generate selector labels.
     * @since 0.0.1
     * @return      string
     * @deprecated  not used at the moment
     */
    public function getDefaultTemplateIDByType( $sType ) {
        $_sTemplateDirPath = apply_filters(
            FeedZapper_Registry::HOOK_SLUG . '_filter_default_template_directory_path_of_' . $sType,
            self::$___sDefaultTemplateDirPath
        );
        $_aTemplate = $this->getTemplateArrayByDirPath(
            $_sTemplateDirPath,
            false       // no extra info
        );
        return isset( $_aTemplate[ 'id' ] )
            ? $_aTemplate[ 'id' ]
            : '';
    }

    /**
     * Caches the uploaded templates.
     * 
     * @since 0.0.1    
     */
    private static $_aUploadedTemplates = array();
 
    /**
     * Retrieve templates and returns the template information as array.
     * 
     * This method is called for the template listing table to list available templates. So this method generates the template information dynamically.
     * This method does not deal with saved options.
     * 
     * @return      array
     */
    public function getUploadedTemplates() {
            
        if ( ! empty( self::$_aUploadedTemplates ) ) {
            return self::$_aUploadedTemplates;
        }
            
        // Construct a template array.
        $_aTemplates = array();
        $_iIndex     = 0;        
        foreach( $this->___getTemplateDirs() as $_sDirPath ) {
            
            $_aTemplate = $this->getTemplateArrayByDirPath( $_sDirPath );
            if ( empty( $_aTemplate ) ) {
                continue;
            }
            
            // Uploaded templates are supposed to be only called in the admin template listing page.
            // So by default, these are not active.
            $_aTemplate[ 'is_active' ] = false;
            
            $_aTemplate[ 'index' ] = ++$_iIndex;
            $_aTemplates[ $_aTemplate[ 'id' ] ] = $_aTemplate;
            
        }
        
        self::$_aUploadedTemplates = $_aTemplates;
        return $_aTemplates;
        
    }

    /**
     * Returns the template array by the given directory path.
     * @since 0.0.1
     * @retrn array The template information array. If formatting went wrong, an empty array will be returned.
     * @scope   public  the class object is passed in a filter so that callback functions can use this method to add custom active templates dynamically.
     */
    public function getTemplateArrayByDirPath( $sDirPath, $bExtraInfo=false ) {

        $_sRelativePath = $this->oUtil->getPathSanitized(
            untrailingslashit( $this->getRelativePath( ABSPATH, $sDirPath ) )
        );
        $_aData         = array(
            'relative_dir_path'     => $_sRelativePath,
            'id'                    => $_sRelativePath,
        );

        if ( ! $bExtraInfo ) {
            return $_aData;
        }
        $_aData[ 'thumbnail_path' ] = $this->___getScreenshotPath( $sDirPath );
        return $this->___getTemplateArrayFormatted(
            $this->___getTemplateData( $sDirPath . DIRECTORY_SEPARATOR . 'style.css' )
            + $_aData
        );


    }
            /**
             * @return  string|null
             */
            private function ___getScreenshotPath( $sDirPath ) {
                foreach( array( 'jpg', 'jpeg', 'png', 'gif' ) as $sExt ) {
                    if ( file_exists( $sDirPath . DIRECTORY_SEPARATOR . 'screenshot.' . $sExt ) ) { 
                        return $sDirPath . DIRECTORY_SEPARATOR . 'screenshot.' . $sExt;
                    }
                }
                return null;
            }           
    
        /**
         * Stores the read template directory paths.
         * @since 0.0.1    
         */
        static private $_aTemplateDirs = array();
        
        /**
         * Returns an array holding the template directories.
         * 
         * @since 0.0.1
         * @return      array       Contains list of template directory paths.
         */
        private function ___getTemplateDirs() {
                
            if ( ! empty( self::$_aTemplateDirs ) ) {
                return self::$_aTemplateDirs;
            }
            foreach( $this->___getTemplateContainerDirs() as $_sTemplateDirPath ) {
                    
                if ( ! @file_exists( $_sTemplateDirPath  ) ) { 
                    continue; 
                }
                $_aFoundDirs = glob( $_sTemplateDirPath . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR );
                if ( is_array( $_aFoundDirs ) ) {    // glob can return false
                    self::$_aTemplateDirs = array_merge( 
                        $_aFoundDirs, 
                        self::$_aTemplateDirs 
                    );
                }
                                
            }
            self::$_aTemplateDirs = array_unique( self::$_aTemplateDirs );
            self::$_aTemplateDirs = ( array ) apply_filters( 'feed_zapper_filter_template_directories', self::$_aTemplateDirs );
            self::$_aTemplateDirs = array_filter( self::$_aTemplateDirs );    // drops elements of empty values.
            self::$_aTemplateDirs = array_unique( self::$_aTemplateDirs );
            return self::$_aTemplateDirs;
        
        }    
            /**
             * Returns the template container directories.
             * @since 0.0.1
             */
            private function ___getTemplateContainerDirs() {
                
                $_aTemplateContainerDirs    = array();
                $_aTemplateContainerDirs[]  = FeedZapper_Registry::$sDirPath . DIRECTORY_SEPARATOR . 'template';
                $_aTemplateContainerDirs[]  = get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'feed_zapper';
                $_aTemplateContainerDirs    = apply_filters( 'feed_zapper_filter_template_container_directories', $_aTemplateContainerDirs );
                $_aTemplateContainerDirs    = array_filter( $_aTemplateContainerDirs );    // drop elements of empty values.
                return array_unique( $_aTemplateContainerDirs );
                
            }       
    
 
    /**
     * A helper function for the getUploadedTemplates() method.
     * 
     * Used when rendering the template listing table.
     * An alternative to get_plugin_data() as some users change the location of the wp-admin directory.
     * 
     * @return      array       an array of template detail information from the given file path.
     * */
    private function ___getTemplateData( $sCSSPath )    {
        return file_exists( $sCSSPath )
            ? get_file_data( 
                $sCSSPath, 
                array(
                    'name'           => 'Template Name',
                    'template_uri'   => 'Template URI',
                    'version'        => 'Version',
                    'description'    => 'Description',
                    'author'         => 'Author',
                    'author_uri'     => 'Author URI',
                ),
                '' // context - do not set any
            )
            : array();
    }                     
        
}