<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno; Licensed under <LICENSE_TYPE>
 */

/**
 * Adds the 'Import' form section to the 'Reset' tab.
 *
 * @since    0.0.1
 */
class FeedZapper_AdminPage_Global__FormSection_Import extends FeedZapper_AdminPage__FormSection_Base {

    /**
     *
     * @since   0.0.1
     */
    protected function _getArguments( $oFactory ) {
        return array(
            'section_id'    => 'import',
            'tab_slug'      => $this->_sTabSlug,
            'title'         => __( 'Import', 'feed-zapper' ),
        );
    }

    /**
     * Get adding form fields.
     * @since    0.0.1
     * @return   array
     */
    protected function _getFields( $oFactory ) {
        return array(
            array(
                'field_id'          => 'import_options',
                'title'             => __( 'Import Options', 'feed-zapper' ),
                'type'              => 'import',
                'value'             => __( 'Upload Options', 'feed-zapper' ),
                'save'              => false,
            )
        );

    }

}