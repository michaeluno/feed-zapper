<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno; Licensed under <LICENSE_TYPE>
 */

/**
 * Adds the 'Permissions' form section to the 'General' tab.
 *
 * @since    0.0.1
 */
class FeedZapper_AdminPage_Global__FormSection_Permissions extends FeedZapper_AdminPage__FormSection_Base {

    /**
     *
     * @since   0.0.1
     */
    protected function _getArguments( $oFactory ) {

        return array(
            'section_id'        => 'permission',
            'tab_slug'          => $this->_sTabSlug,
            'title'             => __( 'Permissions', 'feed-zapper' ),
        );

    }

    /**
     * Get adding form fields.
     * @since    0.0.1
     * @return   array
     */
    protected function _getFields( $oFactory ) {
        $_aUserRoleLabels = $this->___getUserRoleLabels();
        return array(
            array(
                'field_id'          => 'user_roles',
                'title'             => __( 'User Roles', 'feed-zapper' ),
                'description'       => __( 'Select user roles that can add feeds and follow zappers.', 'feed-zapper' ),
                'type'              => 'select',
                'is_multiple'       => true,
                'label'             => $_aUserRoleLabels,
                'attributes'    =>  array(
                    'select'    =>  array(
                        'size'  => count( $_aUserRoleLabels ),
                    ),
                ),
            ),

        );

    }

        private function ___getUserRoleLabels() {

            $_aRoleNames = $GLOBALS[ 'wp_roles' ]->get_names();
       return $_aRoleNames;

        }

    protected function _validate( $aInputs, $aOldInputs, $oFactory, $aSubmitInfo ) {

        $_bVerified = true;
        $_aErrors   = array();

        // An invalid value is found. Set a field error array and an admin notice and return the old values.
        if ( ! $_bVerified ) {
            $oAdminPage->setFieldErrors( $_aErrors );
            $oAdminPage->setSettingNotice( __( 'There was something wrong with your input.', 'feed-zapper' ) );
            return $aOldInputs;
        }
        return $aInputs;

    }




}