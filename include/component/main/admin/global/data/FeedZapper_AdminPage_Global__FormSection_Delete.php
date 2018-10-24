<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno; Licensed under <LICENSE_TYPE>
 */

/**
 * Adds the 'Delete' form section to the 'General' tab.
 *
 * @since    0.0.1
 */
class FeedZapper_AdminPage_Global__FormSection_Delete extends FeedZapper_AdminPage__FormSection_Base {

    /**
     *
     * @since   0.0.1
     */
    protected function _getArguments( $oFactory ) {
        return array(
            'section_id'    => 'delete',
            'tab_slug'      => $this->_sTabSlug,
            'title'         => __( 'Delete', 'feed-zapper' ),
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
                'field_id'          => 'delete_on_uninstall',
                'type'              => 'checkbox',
//                'show_title_column' => false,
                'label'             => __( 'Delete plugin data upon plugin uninstall.', 'feed-zapper' ),
            )
        );

    }

}