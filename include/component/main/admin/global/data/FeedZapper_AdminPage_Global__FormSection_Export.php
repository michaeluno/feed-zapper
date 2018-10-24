<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno; Licensed under <LICENSE_TYPE>
 */

/**
 * Adds the 'Export' form section to the 'Reset' tab.
 *
 * @since    0.0.1
 */
class FeedZapper_AdminPage_Global__FormSection_Export extends FeedZapper_AdminPage__FormSection_Base {

    /**
     *
     * @since   0.0.1
     */
    protected function _getArguments( $oFactory ) {
        return array(
            'section_id'    => 'export',
            'tab_slug'      => $this->_sTabSlug,
            'title'         => __( 'Export', 'feed-zapper' ),
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
                'field_id'          => 'export_options',
                'title'             => __( 'Export Options', 'feed-zapper' ),
                'type'              => 'export',
                'value'             => __( 'Download', 'feed-zapper' ),
                'save'              => false,
            )
        );

    }

}