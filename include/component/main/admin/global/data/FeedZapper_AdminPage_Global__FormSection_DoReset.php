<?php
/**
 * Feed Zapper
 *
 * [PROGRAM_URI]
 * Copyright (c) 2018 Michael Uno; Licensed under <LICENSE_TYPE>
 */

/**
 * Adds the 'Reset' form section to the 'Reset' tab.
 *
 * @since    0.0.1
 */
class FeedZapper_AdminPage_Global__FormSection_DoReset extends FeedZapper_AdminPage__FormSection_Base {

    protected function _construct( $oFactory ) {
    }

    /**
     *
     * @since   0.0.1
     */
    protected function _getArguments( $oFactory ) {
        return array(
            'section_id'    => 'do_reset',
            'tab_slug'      => $this->_sTabSlug,
            'title'         => __( 'Reset', 'feed-zapper' ),
            'save'          => false,
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
                'field_id'          => 'reset_confirmation_check',
                'title'             => __( 'Restore Defaults', 'feed-zapper' ),
                'type'              => 'checkbox',
                'label'             => __( 'I understand the options will be erased by pressing the reset button.', 'feed-zapper' ),
                'save'              => false,
                'value'             => false,
            ),
            array(
                'field_id'          => 'reset',
                'type'              => 'submit',
                'reset'             => true,
                'skip_confirmation' => true,
                // 'show_title_column' => false,
                'value'             => __( 'Reset', 'feed-zapper' ),
            )
        );

    }

    /**
     * Validates the submitted form data.
     *
     * @since    0.0.1
     */
    protected function _validate( $aInputs, $aOldInput, $oAdminPage, $aSubmitInfo ) {

        $_bVerified = true;
        $_aErrors   = array();

        // If the pressed button is not the one with the check box, do not set a field error.
        if ( 'reset' !== $aSubmitInfo[ 'field_id' ] ) {
            return $aInputs;
        }

        if ( ! $aInputs[ 'reset_confirmation_check' ] ) {

            $_bVerified = false;
            $_aErrors[ $this->_sSectionID ][ 'reset_confirmation_check' ] = __( 'Please check the check box to confirm you want to reset the settings.', 'feed-zapper' );

        }

        // An invalid value is found. Set a field error array and an admin notice and return the old values.
        if ( ! $_bVerified ) {
            $oAdminPage->setFieldErrors( $_aErrors );
            $oAdminPage->setSettingNotice( __( 'There was something wrong with your input.', 'feed-zapper' ) );
            return $aOldInput;
        }

        // At this point, the `Reset` button is pressed with a confirmation check box.
        FeedZapper_PluginUtility::rescheduleFeedChecks();

        // The `Feeds` page needs to be set again.
        add_filter(
            'pre_update_option_' . $oAdminPage->oProp->sOptionKey,
            array( $this, 'replyToSetFeedURL' ),
            10,
            3
        );

        return $aInputs;

    }

        /**
         * @param $mValue
         * @param $mOldValue
         * @param $sOptionKey
         * @callback    pre_update_option_{option key}
         */
        public function replyToSetFeedURL( $mValue, $mOldValue, $sOptionKey ) {

            $_aPage = FeedZapper_PluginUtility::getFeedPage();
            $_aPage = array(
                'value'     => $_aPage[ 'id' ],
                'encoded'   => json_encode( array( $_aPage ) ),
            );
            $mValue = is_array( $mValue )
                ? $mValue
                : array();
            $mValue[ 'feed' ][ 'page' ] = $_aPage;
            return $mValue;

        }

}