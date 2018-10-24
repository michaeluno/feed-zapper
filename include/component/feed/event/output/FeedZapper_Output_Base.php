<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno
 *
 */

/**
 * A base for feed output classes.
 *
 * @since    0.0.1
 */
abstract class FeedZapper_Output_Base extends FeedZapper_PluginUtility {

    /**
     * e.g. feed_zapper_filter_feed_output_by_url
     * @var string
     */
    protected $_sOutputFilterHook = '';
    protected $_iFilterHookParameters = 2;

    /**
     * Serves as a substring of filter hook names.
     * @var string
     */
    protected $_sItemType = ''; // `simplepie` or `post`

    /**
     * Class specific arguments.
     * @remark  override this in each extended class.
     * @var array
     */
    protected $_aArguments = array();

    /**
     * Default argument values.
     * @var array
     */
    protected $_aDefaults = array(
        'template_id'     => '',      // (string) the template ID or path for the `template.php` file.
        'template_path'   => '',      // (string) the template path. If this is set with a valid file path, `template_id` will not be used.
        'count'           => -1,      // (integer) -1 for all
        'source_timezone' => 0,       // (integer) hour offset
        'show_errors'     => true,    // (boolean)
    );

    public function __construct() {

        // The hook below is registered only once.
        if ( $this->hasBeenCalled( get_class( $this ) ) ) {
            return;
        }

        if ( ! $this->_sOutputFilterHook ) {
            return;
        }

        add_filter(
            $this->_sOutputFilterHook,
            array( $this, 'replyToGetOutput' ),
            1,  // priority
            $this->_iFilterHookParameters   // number of parameters
        );

        $this->_construct();

    }

    /**
     * A constructor for extended classes.
     */
    protected function _construct() {}

    /**
     * @param $sOutput
     * @param array $aArguments
     * @callback    filter
     * @return string
     */
    public function replyToGetOutput( $sOutput, array $aArguments ) {

        // Format arguments
        $this->_aArguments = $this->_getArguments( $aArguments );
        return $sOutput . $this->_get( $this->_aArguments );

    }

    /**
     * Formats arguments.
     * @param array $aArguments
     * @return array
     */
    protected function _getArguments( array $aArguments ) {
        return $aArguments + $this->_aArguments + $this->_aDefaults;
    }

    /**
     * Generates the output.
     * @return  string
     */
    protected function _get( array $aArguments ) {
        return '';
    }

    /**
     * Finds the template path from the given arguments(unit options).
     *
     * The keys that can determine the template path are template, template_id, template_path.
     *
     * The template_id key is automatically assigned when creating a unit. If the template_path is explicitly set and the file exists, it will be used.
     *
     * The template key is a user friendly one and it should point to the name of the template. If multiple names exist, the first item will be used.
     *
     */
    protected function _getTemplatePath( array $aArguments ) {

        // If it is set in a request, use it.
        if ( isset( $aArguments[ 'template_path' ] ) && file_exists( $aArguments[ 'template_path' ] ) ) {
            return $aArguments[ 'template_path' ];
        }

        $_oTemplateOption = FeedZapper_Template_Option::getInstance();

        // If a template ID is given,
        if ( isset( $aArguments[ 'template_id' ] ) && $aArguments[ 'template_id' ] ) {
            foreach( $_oTemplateOption->getActiveTemplates() as $_sID => $_aTemplate ) {
                if ( $_sID == trim( $aArguments[ 'template_id' ] ) ) {
                    return ABSPATH . $_aTemplate[ 'relative_dir_path' ] . '/template.php';
                }
            }
        }

        // Not found. In that case, use the default one.
        return apply_filters(
            FeedZapper_Registry::HOOK_SLUG . '_filter_default_template_path',
            ''
        );
    }

    /**
     * @param array $aItems
     * @param array $aArguments
     *
     * @return array    An array holding formatted items that can be passed to a template
     */
    protected function _getItems( array $aItems, array $aArguments ) {
        return $this->___getItemsByType(
            $this->_sItemType,    // simplepie or post
            $aItems,
            $aArguments
        );
    }
        private function ___getItemsByType( $sType, array $aItems, array $aArguments ) {

            $_iCount = ( integer ) $aArguments[ 'count' ];
            $_iAdded = 0;
            $_aItems = array();
            foreach( $aItems as $_iIndex => $_oItem ) {

                if ( -1 !== $_iCount && $_iCount <= $_iAdded ) {
                    break;
                }

                // The routine of creating posts also uses this filter.
                // This filter should be only used to format the feed item array
                // To block/mute items, use the filter below.
                $_aItem = apply_filters(
                    "feed_zapper_filter_feed_{$sType}_item",
                    array(),    // data will be filled
                    $_oItem,
                    $aArguments
                );
                // Black listed items will be dropped through the above filter.
                if ( empty( $_aItem ) ) {
                    continue;
                }

                // Allows user-defined blacklists to take effect
                $_aItem = apply_filters(
                    "feed_zapper_filter_feed_{$sType}_item_by_user_" . get_current_user_id(),
                    $_aItem,    // data may be dropped
                    $_oItem,
                    $aArguments
                );
                // Black listed items will be dropped through the above filter.
                if ( empty( $_aItem ) ) {
                    continue;
                }

                // Storing the item with the key of ID prevents duplicates.
                $_aItems[ $_aItem[ 'id' ] ] = $_aItem;
                $_iAdded++;

            }
            return $_aItems;

        }

}